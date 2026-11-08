<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Redirect;
use App\Support\LegacyUrlMap;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    private function seedSite(): void
    {
        $this->seed(PageSeeder::class);
        $cat = Category::create(['slug' => 'corda', 'sort' => 1, 'image_path' => 'categories/corda.jpg']);
        $cat->translations()->create(['locale' => 'it', 'name' => 'Corda', 'slug' => 'corda']);
        $cat->translations()->create(['locale' => 'en', 'name' => 'Rope', 'slug' => 'rope']);
        foreach (['Corda 100' => true, 'Corda 80' => false] as $name => $english) {
            $slug = strtolower(str_replace(' ', '-', $name));
            $p = Product::create(['code' => $name, 'category_id' => $cat->id, 'weight_g' => 8, 'titles' => ['AG', 'AU 750']]);
            $p->translations()->create(['locale' => 'it', 'name' => $name, 'slug' => $slug]);
            if ($english) {
                $p->translations()->create(['locale' => 'en', 'name' => $name, 'slug' => $slug]);
            }
        }
    }

    public function test_every_sitemap_url_returns_200(): void
    {
        $this->seedSite();

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();
        preg_match_all('#<loc>([^<]+)</loc>#', $xml, $m);

        $this->assertGreaterThan(30, count($m[1]));
        foreach (array_unique($m[1]) as $loc) {
            $this->get($loc)->assertOk();
        }
    }

    public function test_every_legacy_url_resolves_with_a_single_301_to_a_200(): void
    {
        $this->seedSite();
        $map = new LegacyUrlMap();
        $legacy = [
            '/it/azienda-chi-siamo-maurizio-chianucci/', '/en/company-about-us-maurizio-chianucci/', '/it/lavorazioni/', '/en/processings/',
            '/it/lavorazioni/fusione/', '/it/lavorazioni/preparazione/', '/it/lavorazioni/saldatura/', '/it/lavorazioni/pulitura-e-lucidatura/',
            '/it/lavorazioni/galvanica/', '/en/processings/fusion/', '/en/processings/preparation/', '/en/processings/welding/',
            '/en/processings/cleaning-and-polishing/', '/en/processings/galvanic/', '/it/negozio/', '/it/catalogo/catalogo-pdf/',
            '/en/catalogue/pdf-catalogue/', '/it/my-account/', '/it/carrello/', '/it/checkout/', '/en/cart-2/', '/en/user-registration/',
        ];
        foreach ($legacy as $path) {
            $to = $map->map($path);
            $this->assertNotNull($to, $path);
            Redirect::create(['from_path' => $path, 'to_path' => $to]);
        }
        Redirect::create(['from_path' => '/it/categoria-prodotto/corda/', 'to_path' => '/it/catalogo?categoria=corda']);
        $legacy[] = '/it/categoria-prodotto/corda/';

        foreach ($legacy as $path) {
            $first = $this->get($path)->assertStatus(301);
            $target = $first->headers->get('Location');
            $this->get($target)->assertOk();
        }
    }

    public function test_no_public_page_exposes_a_price(): void
    {
        $this->seedSite();

        foreach (['/it/', '/it/catalogo', '/it/prodotto/corda-100', '/en/catalogue', '/it/area-clienti'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            preg_match('#<main.*?</main>#s', $html, $main);
            $this->assertStringNotContainsString('€', $main[0], $path);
            $this->assertStringNotContainsStringIgnoringCase('prezzo', $main[0], $path);
        }
    }
}
