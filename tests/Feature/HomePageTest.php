<?php

namespace Tests\Feature;

use App\Models\Category;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    private function categories(): void
    {
        foreach (['corda' => 'Corda', 'rolo' => 'Rolò', 'grumetta' => 'Grumetta', 'forzatina' => 'Forzatina', 'rosetta' => 'Rosetta'] as $slug => $name) {
            $c = Category::create(['slug' => $slug, 'sort' => 1, 'image_path' => "products/$slug.jpg"]);
            $c->translations()->create(['locale' => 'it', 'name' => $name, 'slug' => $slug]);
        }
    }

    public function test_home_has_the_mockup_sections_in_italian(): void
    {
        $this->categories();
        $this->get('/it/')->assertOk()
            ->assertSeeInOrder([
                'Manifattura orafa dal 1973', 'L\'arte della manifattura orafa',
                'Made in Italy', 'Competenza artigianale', 'Qualità certificata', 'Partner B2B',
                'L\'atelier', 'Le lavorazioni', 'La nostra storia', 'I nostri prodotti', 'Area riservata B2B',
            ])
            ->assertSee('href="'.url('/it/catalogo').'"', false)
            ->assertSee('href="'.url('/it/area-clienti').'"', false);
    }

    public function test_home_is_translated_in_english(): void
    {
        $this->categories();
        $this->get('/en/')->assertOk()
            ->assertSeeInOrder(['Goldsmith manufacturer since 1973', 'The art of goldsmith manufacturing', 'The Chianucci atelier', 'Our processes', 'Our story', 'Our products', 'Reserved B2B area'])
            ->assertSee('href="'.url('/en/catalogue').'"', false);
    }

    public function test_every_image_on_the_home_exists_and_has_alt_text(): void
    {
        $this->categories();
        foreach (['/it/', '/en/'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            preg_match_all('#<img\b[^>]*>#i', $html, $imgs);
            $local = array_filter($imgs[0], fn ($tag) => preg_match('#src="(?:/|http://localhost/)(images|brand)/#', $tag));
            $this->assertGreaterThanOrEqual(8, count($local), "$path: poche immagini");
            foreach ($local as $tag) {
                preg_match('#src="(?:http://localhost)?(/[^"]+)"#', $tag, $m);
                $this->assertFileExists(public_path(ltrim($m[1], '/')), "File mancante: {$m[1]}");
                $this->assertMatchesRegularExpression('#\balt(=|\s|/|>)#', $tag, "alt mancante: $tag"); // alt vuoto = immagine decorativa
            }
        }
    }

    public function test_home_images_are_real_photos_not_huge(): void
    {
        foreach (glob(public_path('images/home/*.jpg')) as $file) {
            $this->assertLessThan(400 * 1024, filesize($file), basename($file).' pesa troppo per il web');
            [$w] = getimagesize($file);
            $this->assertLessThanOrEqual(1920, $w);
        }
        $this->assertNotEmpty(glob(public_path('images/home/*.jpg')));
    }

    public function test_home_shows_category_tiles_linking_to_the_filtered_catalog(): void
    {
        $this->categories();

        $html = $this->get('/it/')->assertOk()->getContent();

        $this->assertStringContainsString('data-home-category', $html);
        $this->assertStringContainsString(url('/it/catalogo?categoria=corda'), html_entity_decode($html));
        $this->assertSame(4, substr_count($html, 'data-home-category'), 'la home mostra 4 categorie');
    }

    public function test_home_has_a_single_h1_and_no_price(): void
    {
        $html = $this->get('/it/')->assertOk()->getContent();
        preg_match('#<main.*?</main>#s', $html, $main);

        $this->assertSame(1, substr_count($main[0], '<h1'));
        $this->assertStringNotContainsString('€', $main[0]);
        $this->assertStringNotContainsStringIgnoringCase('prezzo', $main[0]);
    }
}
