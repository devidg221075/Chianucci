<?php

namespace App\Importer;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\ImportRun;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductTranslation;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CatalogImporter
{
    private const BATCH = 8;

    /** @var array<string,string> url => errore */
    public array $failures = [];

    /** @var string[] prodotti importati con campi mancanti */
    public array $incomplete = [];

    /** @var array<string,string> indirizzo => codice articolo già usato da un altro prodotto */
    public array $duplicates = [];

    /** @var string[] prodotti che sul sito vecchio esistono solo in inglese: creati con lo stesso testo in italiano */
    public array $englishOnly = [];

    /** @var string[] prodotti senza versione inglese sul sito vecchio: nel sito nuovo mostrano il testo italiano */
    public array $withoutEnglish = [];

    public function __construct(private SiteClient $client, private CatalogParser $parser, private bool $resume = false) {}

    /** @return array<string,array<string,string[]>> lingua => slug => url (solo le schede prodotto) */
    public function productUrls(): array
    {
        $xml = $this->client->get(rtrim($this->client->base, '/').'/product-sitemap.xml');
        preg_match_all('#<loc>\s*([^<]+?)\s*</loc>#', $xml, $m);

        $urls = ['it' => [], 'en' => []];
        foreach ($m[1] as $url) {
            if (preg_match('#/(it|en)/prodotto/([^/]+)/?$#', $url, $p)) {
                $urls[$p[1]][$p[2]] = $url;
            }
        }

        return $urls;
    }

    public function run(): void
    {
        $urls = $this->productUrls();

        foreach (['it', 'en'] as $locale) {
            foreach (array_chunk($urls[$locale], self::BATCH, true) as $batch) {
                $todo = array_filter($batch, fn ($url) => ! $this->alreadyDone($url));
                $pages = $todo === [] ? [] : $this->client->getMany($todo);
                foreach ($todo as $url) {
                    $this->importUrl($url, $locale, $pages[$url]);
                }
            }
        }

        $this->completeMissingEnglish($urls);
        $this->assignCategoryImages();
    }

    /**
     * Schede inglesi che la sitemap del sito vecchio non elenca: si trovano dal selettore lingua della scheda italiana.
     *
     * @param  array<string,array<string,string>>  $urls
     */
    private function completeMissingEnglish(array $urls): void
    {
        $missing = ProductTranslation::where('locale', 'it')
            ->whereDoesntHave('product.translations', fn ($t) => $t->where('locale', 'en'))
            ->pluck('slug');

        foreach ($missing as $slug) {
            $italianUrl = $urls['it'][$slug] ?? null;
            if ($italianUrl === null) {
                continue;
            }
            try {
                $counterpart = $this->parser->parseProduct($this->client->get($italianUrl), 'it')['counterpart_slug'];
                if ($counterpart === null || isset($urls['en'][$counterpart])) {
                    continue; // nessuna versione inglese, oppure già tentata nel giro normale
                }
                $englishUrl = preg_replace('#/it/prodotto/[^/]+/?$#', "/en/prodotto/$counterpart/", $italianUrl);
                if (! $this->alreadyDone($englishUrl)) {
                    $known = ProductTranslation::where('locale', 'it')->where('slug', $slug)->firstOrFail()->product;
                    $this->importUrl($englishUrl, 'en', $this->client->get($englishUrl), $known);
                }
            } catch (Throwable $e) {
                if ($e instanceof RequestException && $e->response->status() === 404) {
                    $this->withoutEnglish[] = $slug; // il sito vecchio rimanda a una pagina inglese che non esiste

                    continue;
                }
                $this->failures[$italianUrl] = 'Versione inglese non trovata: '.mb_substr($e->getMessage(), 0, 200);
            }
        }
    }

    /** Foto della categoria per il mega menu: la prima immagine del suo primo prodotto, se non è già stata scelta. */
    private function assignCategoryImages(): void
    {
        foreach (Category::whereNull('image_path')->get() as $category) {
            $path = ProductImage::whereIn('product_id', $category->products()->select('id'))
                ->orderBy('product_id')->orderBy('sort')->value('path');
            if ($path) {
                $category->update(['image_path' => $path]);
            }
        }
    }

    private function alreadyDone(string $url): bool
    {
        return $this->resume && ImportRun::where('url', $url)->where('status', 'ok')->exists();
    }

    private function importUrl(string $url, string $locale, string|Throwable $page, ?Product $known = null): void
    {
        try {
            if ($page instanceof Throwable) {
                throw $page;
            }
            $parsed = $this->parser->parseProduct($page, $locale);
            $this->store($parsed, $locale, $known);
            ImportRun::updateOrCreate(['url' => $url], ['status' => 'ok', 'error' => null]);
        } catch (Throwable $e) {
            $this->failures[$url] = $e->getMessage();
            ImportRun::updateOrCreate(['url' => $url], ['status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 500)]);
        }
    }

    /** @param ?Product $known prodotto già individuato dalla scheda italiana (versione inglese fuori sitemap) */
    private function store(array $p, string $locale, ?Product $known = null): void
    {
        if (! $p['slug'] || ! $p['name']) {
            throw new \RuntimeException('Scheda senza nome o indirizzo');
        }

        // Tutto o niente: un prodotto non resta mai senza traduzione se un'immagine non si scarica.
        DB::transaction(function () use ($p, $locale, $known) {
            if ($locale === 'it') {
                $product = $this->italianProduct($p);
                $this->images($product, $p);
                $this->category($p, 'it', $product->category);
            } else {
                $italian = ProductTranslation::where('locale', 'it')->where('slug', $p['slug'])->first()
                    ?? (($p['counterpart_slug'] ?? null) ? ProductTranslation::where('locale', 'it')->where('slug', $p['counterpart_slug'])->first() : null);

                if (! $italian && ! $known && ($p['counterpart_slug'] ?? null)) {
                    throw new \RuntimeException("Nessun prodotto italiano con indirizzo {$p['counterpart_slug']}");
                }

                if ($known || $italian) {
                    $product = $known ?? $italian->product;
                } elseif (! ($p['complete'] ?? false)) {
                    // Pagina arrivata a metà: il rimando alla scheda italiana può essere andato perso, si riprova.
                    throw new \RuntimeException("Pagina incompleta, nessun prodotto italiano con indirizzo {$p['slug']}");
                } else {
                    // La pagina inglese non rimanda a nessuna scheda italiana: il prodotto esiste solo in inglese.
                    // Si crea comunque, con lo stesso testo anche in italiano, da correggere nel pannello.
                    $category = CategoryTranslation::where('locale', 'en')->where('slug', $p['category_slug'])->first()?->category;
                    $product = Product::where('code', $p['code'])->whereDoesntHave('translations')->first() ?? Product::create([
                        'code' => Product::where('code', $p['code'])->exists() ? "{$p['code']} [{$p['slug']}]" : $p['code'],
                        'category_id' => ($category ?? $this->category($p, 'en'))->id,
                        'weight_g' => $p['weight_g'], 'length_cm' => $p['length_cm'], 'link_mm' => $p['link_mm'],
                        'titles' => $p['titles'],
                    ]);
                    $this->images($product, $p);
                    ProductTranslation::firstOrCreate(
                        ['product_id' => $product->id, 'locale' => 'it'],
                        ['name' => $p['name'], 'slug' => $p['slug']]
                    );
                    $this->englishOnly[] = $p['slug'];
                }
                $this->category($p, 'en', $product->category);
            }

            // Mai sovrascrivere un testo già presente (può essere stato corretto a mano nel pannello).
            ProductTranslation::firstOrCreate(
                ['product_id' => $product->id, 'locale' => $locale],
                ['name' => $p['name'], 'slug' => $p['slug']]
            );
        });
    }

    /** Il prodotto si riconosce dall'indirizzo italiano; il codice articolo può ripetersi tra prodotti diversi. */
    private function italianProduct(array $p): Product
    {
        $product = ProductTranslation::where('locale', 'it')->where('slug', $p['slug'])->first()?->product;

        // Residuo di un'importazione interrotta (nessuna traduzione): si riprende quello.
        if (! $product) {
            $byCode = Product::where('code', $p['code'])->first();
            if ($byCode && ! $byCode->translations()->where('locale', 'it')->exists()) {
                $product = $byCode;
            }
        }

        if ($product) {
            // Si completano solo i campi vuoti: i valori già presenti (anche corretti a mano) non si toccano.
            foreach (['weight_g', 'length_cm', 'link_mm'] as $field) {
                if ($product->{$field} === null && $p[$field] !== null) {
                    $product->{$field} = $p[$field];
                }
            }
            if (empty($product->titles) && ! empty($p['titles'])) {
                $product->titles = $p['titles'];
            }
            $product->save();
        } else {
            $code = $p['code'];
            if (Product::where('code', $code)->exists()) {
                $this->duplicates[$p['slug']] = $code;
                $code = "$code [{$p['slug']}]";
            }
            $product = Product::create([
                'code' => $code,
                'category_id' => $this->category($p, 'it')->id,
                'weight_g' => $p['weight_g'], 'length_cm' => $p['length_cm'], 'link_mm' => $p['link_mm'],
                'titles' => $p['titles'],
            ]);
        }

        foreach (['weight_g', 'length_cm', 'link_mm'] as $field) {
            if ($product->{$field} === null) {
                $this->incomplete[$p['code'].' ('.$p['slug'].')'] = $field;
            }
        }

        return $product;
    }

    private function category(array $p, string $locale, ?Category $existing = null): Category
    {
        $category = $existing ?? Category::firstOrCreate(['slug' => $p['category_slug']], ['sort' => Category::count() + 1]);

        if ($p['category_slug'] && $p['category_name']) {
            $category->translations()->firstOrCreate(
                ['locale' => $locale],
                ['name' => $p['category_name'], 'slug' => $p['category_slug']]
            );
        }

        return $category;
    }

    private function images(Product $product, array $p): void
    {
        foreach ($p['images'] as $i => $imageUrl) {
            $path = 'products/'.basename(parse_url($imageUrl, PHP_URL_PATH));
            if (! Storage::disk('public')->exists($path)) {
                Storage::disk('public')->put($path, $this->client->get($imageUrl));
            }
            ProductImage::updateOrCreate(['product_id' => $product->id, 'path' => $path], ['sort' => $i]);
        }
    }
}
