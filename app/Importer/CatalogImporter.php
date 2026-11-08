<?php

namespace App\Importer;

use App\Models\Category;
use App\Models\ImportRun;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductTranslation;
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

        $this->assignCategoryImages();
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

    private function importUrl(string $url, string $locale, string|Throwable $page): void
    {
        try {
            if ($page instanceof Throwable) {
                throw $page;
            }
            $parsed = $this->parser->parseProduct($page, $locale);
            $this->store($parsed, $locale);
            ImportRun::updateOrCreate(['url' => $url], ['status' => 'ok', 'error' => null]);
        } catch (Throwable $e) {
            $this->failures[$url] = $e->getMessage();
            ImportRun::updateOrCreate(['url' => $url], ['status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 500)]);
        }
    }

    private function store(array $p, string $locale): void
    {
        if (! $p['slug'] || ! $p['name']) {
            throw new \RuntimeException('Scheda senza nome o indirizzo');
        }

        // Tutto o niente: un prodotto non resta mai senza traduzione se un'immagine non si scarica.
        DB::transaction(function () use ($p, $locale) {
            if ($locale === 'it') {
                $product = $this->italianProduct($p);
                $this->images($product, $p);
                $this->category($p, 'it', $product->category);
            } else {
                $product = ProductTranslation::where('locale', 'it')->where('slug', $p['slug'])->first()?->product
                    ?? throw new \RuntimeException("Nessun prodotto italiano con indirizzo {$p['slug']}");
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
