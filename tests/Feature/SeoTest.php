<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    private function product(bool $english = true): Product
    {
        $category = Category::create(['slug' => 'corda', 'sort' => 1]);
        $product = Product::create(['code' => 'Corda 100', 'category_id' => $category->id, 'titles' => ['AG'], 'weight_g' => 8]);
        $product->translations()->create(['locale' => 'it', 'name' => 'Corda 100', 'slug' => 'corda-100']);
        if ($english) {
            $product->translations()->create(['locale' => 'en', 'name' => 'Rope 100', 'slug' => 'rope-100']);
        }
        $product->images()->create(['path' => 'products/corda-100-1.jpg']);

        return $product;
    }

    public function test_sitemap_lists_pages_and_products_in_both_languages(): void
    {
        $this->seed(PageSeeder::class);
        $this->product();

        $xml = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml')->getContent();

        foreach (['/it/', '/en/', '/it/azienda', '/en/company', '/it/catalogo', '/en/catalogue', '/it/prodotto/corda-100', '/en/prodotto/rope-100'] as $path) {
            $this->assertStringContainsString('<loc>'.url($path).'</loc>', $xml, $path);
        }
        $this->assertStringContainsString('hreflang="en"', $xml);
    }

    public function test_sitemap_lists_untranslated_products_only_in_italian(): void
    {
        $this->product(false);

        $xml = $this->get('/sitemap.xml')->getContent();

        $this->assertStringContainsString('/it/prodotto/corda-100', $xml);
        $this->assertStringNotContainsString('/en/prodotto/corda-100', $xml);
    }

    public function test_pages_have_title_description_hreflang_and_canonical(): void
    {
        $this->seed(PageSeeder::class);

        $html = $this->get('/it/azienda')->assertOk()->getContent();

        $this->assertStringContainsString('<title>Azienda - CHIANUCCI MAURIZIO</title>', $html);
        $this->assertMatchesRegularExpression('/<meta name="description" content="[^"]{20,}"/', $html);
        $this->assertStringContainsString('<link rel="canonical" href="'.url('/it/azienda').'">', $html);
        $this->assertStringContainsString('<link rel="alternate" hreflang="it" href="'.url('/it/azienda').'">', $html);
        $this->assertStringContainsString('<link rel="alternate" hreflang="en" href="'.url('/en/company').'">', $html);
        $this->assertStringContainsString('hreflang="x-default"', $html);
    }

    public function test_untranslated_product_in_english_points_canonical_to_the_italian_page(): void
    {
        $this->product(false);

        $html = $this->get('/en/prodotto/corda-100')->assertOk()->getContent();

        $this->assertStringContainsString('<link rel="canonical" href="'.url('/it/prodotto/corda-100').'">', $html);
        $this->assertStringNotContainsString('hreflang="en"', $html);
    }

    public function test_product_json_ld_has_no_offer_or_price(): void
    {
        $this->product();

        $html = $this->get('/it/prodotto/corda-100')->assertOk()->getContent();
        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
        $json = json_decode($m[1], true);

        $this->assertSame('Product', $json['@type']);
        $this->assertSame('Corda 100', $json['name']);
        $this->assertArrayNotHasKey('offers', $json);
        $this->assertStringNotContainsStringIgnoringCase('price', $m[1]);
    }

    public function test_home_has_organization_json_ld_with_company_data(): void
    {
        $this->seed(PageSeeder::class);

        $html = $this->get('/it/')->assertOk()->getContent();
        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
        $json = json_decode($m[1], true);

        $this->assertSame('Organization', $json['@type']);
        $this->assertSame('CHIANUCCI MAURIZIO s.r.l.', $json['name']);
        $this->assertSame('info@chianucci.it', $json['email']);
        $this->assertSame('Arezzo', $json['address']['addressLocality']);
    }

    public function test_robots_txt_points_to_the_sitemap_and_blocks_the_admin(): void
    {
        $body = $this->get('/robots.txt')->assertOk()->getContent();

        $this->assertStringContainsString('Sitemap: '.url('/sitemap.xml'), $body);
        $this->assertStringContainsString('Disallow: /admin', $body);
    }
}
