<?php

namespace App\Importer;

use App\Models\Category;
use App\Models\ImportRun;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductTranslation;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CatalogImporter
{
    /** @var array<string,string> url => errore */
    public array $failures = [];

    /** @var string[] prodotti importati con campi mancanti */
    public array $incomplete = [];

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
            foreach ($urls[$locale] as $url) {
                $this->importUrl($url, $locale);
            }
        }
    }

    private function importUrl(string $url, string $locale): void
    {
        if ($this->resume && ImportRun::where('url', $url)->where('status', 'ok')->exists()) {
            return;
        }

        try {
            $parsed = $this->parser->parseProduct($this->client->get($url), $locale);
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

        if ($locale === 'it') {
            $category = $this->category($p, 'it');
            $product = Product::updateOrCreate(['code' => $p['code']], [
                'category_id' => $category->id,
                'weight_g' => $p['weight_g'], 'length_cm' => $p['length_cm'], 'link_mm' => $p['link_mm'],
                'titles' => $p['titles'],
            ]);
            $this->images($product, $p);
            foreach (['weight_g', 'length_cm', 'link_mm'] as $field) {
                if ($p[$field] === null) {
                    $this->incomplete[$p['code']] = $field;
                }
            }
        } else {
            $product = ProductTranslation::where('locale', 'it')->where('slug', $p['slug'])->first()?->product
                ?? throw new \RuntimeException("Nessun prodotto italiano con indirizzo {$p['slug']}");
            $this->category($p, 'en', $product->category);
        }

        ProductTranslation::updateOrCreate(
            ['product_id' => $product->id, 'locale' => $locale],
            ['name' => $p['name'], 'slug' => $p['slug']]
        );
    }

    private function category(array $p, string $locale, ?Category $existing = null): Category
    {
        $category = $existing ?? Category::firstOrCreate(['slug' => $p['category_slug']], ['sort' => Category::count() + 1]);

        if ($p['category_slug'] && $p['category_name']) {
            $category->translations()->updateOrCreate(
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
