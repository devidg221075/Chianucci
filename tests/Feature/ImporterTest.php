<?php

namespace Tests\Feature;

use App\Importer\CatalogParser;
use App\Importer\PageParser;
use App\Importer\SiteClient;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImporterTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(string $name): string
    {
        return file_get_contents(base_path("tests/Fixtures/legacy/$name"));
    }

    /** Fixture della Corda 100 trasformata in un secondo prodotto. */
    private function second(string $html, string $locale): string
    {
        return str_replace(['Corda 100', 'corda-100', 'Corda100 300'], ['Corda 80', 'corda-80', 'Corda 80'], $html);
    }

    private function fakeSite(array $overrides = []): void
    {
        $sitemap = '<?xml version="1.0"?><urlset>'
            .'<url><loc>https://www.chianucci.it/it/prodotto/corda-100/</loc></url>'
            .'<url><loc>https://www.chianucci.it/en/prodotto/corda-100/</loc></url>'
            .'<url><loc>https://www.chianucci.it/it/prodotto/corda-80/</loc></url>'
            .'<url><loc>https://www.chianucci.it/en/prodotto/corda-80/</loc></url>'
            .'</urlset>';

        Http::fake($overrides + [
            '*/product-sitemap.xml' => Http::response($sitemap),
            '*/it/prodotto/corda-100/' => Http::response($this->fixture('product-corda-100-it.html')),
            '*/en/prodotto/corda-100/' => Http::response($this->fixture('product-corda-100-en.html')),
            '*/it/prodotto/corda-80/' => Http::response($this->second($this->fixture('product-corda-100-it.html'), 'it')),
            '*/en/prodotto/corda-80/' => Http::response($this->second($this->fixture('product-corda-100-en.html'), 'en')),
            '*/wp-content/uploads/*' => Http::response('IMG'),
        ]);
        Storage::fake('public');
    }

    public function test_parse_product_extracts_corda_100_values(): void
    {
        $p = (new CatalogParser())->parseProduct($this->fixture('product-corda-100-it.html'), 'it');

        $this->assertSame('Corda 100', $p['name']);
        $this->assertSame('Corda 100', $p['code']);
        $this->assertSame('corda-100', $p['slug']);
        $this->assertEquals(8.00, $p['weight_g']);
        $this->assertEquals(19.00, $p['length_cm']);
        $this->assertEquals(5.40, $p['link_mm']);
        $this->assertSame(['AG', 'AU 750', 'AU 585', 'AU 375'], $p['titles']);
        $this->assertSame('corda', $p['category_slug']);
        $this->assertSame('Corda', $p['category_name']);
        $this->assertSame(['https://www.chianucci.it/wp-content/uploads/corda-100-1.jpg'], $p['images']);
    }

    public function test_parse_product_reads_the_english_labels(): void
    {
        $p = (new CatalogParser())->parseProduct($this->fixture('product-corda-100-en.html'), 'en');

        $this->assertSame('corda-100', $p['slug']);
        $this->assertEquals(8.00, $p['weight_g']);
        $this->assertEquals(19.00, $p['length_cm']);
        $this->assertEquals(5.40, $p['link_mm']);
        $this->assertSame('rope', $p['category_slug']);
        $this->assertSame('Rope', $p['category_name']);
    }

    public function test_price_is_never_parsed_or_stored(): void
    {
        $this->fakeSite();

        $parsed = (new CatalogParser())->parseProduct($this->fixture('product-corda-100-it.html'), 'it');
        $this->assertArrayNotHasKey('price', $parsed);
        $this->assertStringNotContainsStringIgnoringCase('definire', json_encode($parsed));

        $this->artisan('site:import --retry-sleep=0 --only=catalog')->assertSuccessful();

        $dump = json_encode(Product::with('translations')->get()->toArray());
        $this->assertStringNotContainsStringIgnoringCase('definire', $dump);
        $this->assertStringNotContainsStringIgnoringCase('prezzo', $dump);
    }

    public function test_import_creates_products_in_both_languages_with_images(): void
    {
        $this->fakeSite();

        $this->artisan('site:import --retry-sleep=0 --only=catalog')->assertSuccessful();

        $this->assertSame(2, Product::count());
        $product = Product::where('code', 'Corda 100')->firstOrFail();
        $this->assertSame(['it', 'en'], $product->translations()->orderByDesc('locale')->pluck('locale')->all());
        $this->assertSame('Corda', $product->category->translations()->where('locale', 'it')->value('name'));
        $this->assertSame('Rope', $product->category->translations()->where('locale', 'en')->value('name'));
        Storage::disk('public')->assertExists($product->images()->first()->path);
    }

    public function test_import_is_idempotent(): void
    {
        $this->fakeSite();

        $this->artisan('site:import --retry-sleep=0 --only=catalog')->assertSuccessful();
        $first = [Product::count(), ProductImage::count(), \App\Models\Category::count(), \App\Models\ProductTranslation::count()];
        $this->artisan('site:import --retry-sleep=0 --only=catalog')->assertSuccessful();

        $this->assertSame($first, [Product::count(), ProductImage::count(), \App\Models\Category::count(), \App\Models\ProductTranslation::count()]);
    }

    public function test_import_resumes_after_failure_without_redoing_finished_urls(): void
    {
        $this->fakeSite(['*/en/prodotto/corda-80/' => Http::response('boom', 500)]);

        $this->artisan('site:import --retry-sleep=0 --only=catalog')->assertExitCode(1);
        $this->assertSame(2, Product::count(), 'Il fallimento di un URL non deve fermare gli altri');

        // Le stub vecchie (500) non devono vincere su quelle nuove: si riparte da un client HTTP pulito.
        app()->forgetInstance(\Illuminate\Http\Client\Factory::class);
        Http::clearResolvedInstances();
        $this->fakeSite();
        Http::preventStrayRequests();
        $this->artisan('site:import --retry-sleep=0 --only=catalog --resume')->assertSuccessful();

        Http::assertSent(fn ($r) => str_contains($r->url(), '/en/prodotto/corda-80/'));
        Http::assertNotSent(fn ($r) => str_contains($r->url(), '/it/prodotto/corda-100/'));
        $this->assertSame(2, Product::where('code', 'Corda 80')->firstOrFail()->translations()->count());
    }

    public function test_client_retries_dropped_connections(): void
    {
        Http::fake(['*' => Http::sequence()
            ->pushFailedConnection()
            ->pushFailedConnection()
            ->push('<html>ok</html>')]);

        $body = (new SiteClient('https://www.chianucci.it', 4, 0))->get('https://www.chianucci.it/it/');

        $this->assertSame('<html>ok</html>', $body);
    }

    public function test_client_gives_up_after_the_last_attempt(): void
    {
        Http::fake(['*' => Http::response('boom', 500)]);

        $this->expectException(\Illuminate\Http\Client\RequestException::class);
        (new SiteClient('https://www.chianucci.it', 2, 0))->get('https://www.chianucci.it/it/');
    }

    public function test_page_parser_keeps_headings_and_paragraphs_for_review(): void
    {
        $page = (new PageParser())->parse($this->fixture('page-azienda-it.html'));

        $this->assertSame('Azienda - Chi Siamo', $page['title']);
        $texts = array_column($page['blocks'], 'text');
        $this->assertContains('Chianucci Maurizio dal 1973', $texts);
        $this->assertNotEmpty(array_filter($page['blocks'], fn ($b) => $b['tag'] === 'p'));
    }
}
