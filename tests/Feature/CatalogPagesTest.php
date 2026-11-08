<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogPagesTest extends TestCase
{
    use RefreshDatabase;

    private function makeCategory(string $slug, string $name): Category
    {
        $category = Category::create(['slug' => $slug, 'sort' => 1]);
        $category->translations()->create(['locale' => 'it', 'name' => $name, 'slug' => $slug]);

        return $category;
    }

    private function makeProduct(Category $category, string $code, string $name, array $attrs = [], bool $withEnglish = false): Product
    {
        $product = Product::create(array_merge([
            'code' => $code, 'category_id' => $category->id,
            'weight_g' => 8, 'length_cm' => 19, 'link_mm' => 5.4, 'titles' => ['AG'],
        ], $attrs));
        $slug = strtolower(str_replace(' ', '-', $name));
        $product->translations()->create(['locale' => 'it', 'name' => $name, 'slug' => $slug]);
        if ($withEnglish) {
            $product->translations()->create(['locale' => 'en', 'name' => $name.' EN', 'slug' => $slug.'-en']);
        }

        return $product;
    }

    public function test_filters_combine_and_return_only_matching_products(): void
    {
        $corda = $this->makeCategory('corda', 'Corda');
        $rolo = $this->makeCategory('rolo', 'Rolò');
        $this->makeProduct($corda, 'COR100', 'Corda 100', ['weight_g' => 8, 'titles' => ['AG', 'AU 750']]);
        $this->makeProduct($corda, 'COR200', 'Corda 200', ['weight_g' => 20, 'titles' => ['AG']]);
        $this->makeProduct($rolo, 'ROL100', 'Rolo 100', ['weight_g' => 8, 'titles' => ['AG', 'AU 750']]);

        $response = $this->get('/it/catalogo?categoria=corda&titolo=AU+750&peso_max=10')->assertOk();

        $response->assertSee('Corda 100')->assertDontSee('Corda 200')->assertDontSee('Rolo 100');
    }

    public function test_empty_filter_result_shows_message_not_error(): void
    {
        $this->makeProduct($this->makeCategory('corda', 'Corda'), 'COR100', 'Corda 100');

        $this->get('/it/catalogo?peso_min=999999')->assertOk()->assertSee('Nessun prodotto');
        $this->get('/it/catalogo?maglia_min=abc&lunghezza_max=%00&peso_min[]=1&q[]=x')->assertOk();
    }

    public function test_text_search_matches_code_and_name_in_current_language(): void
    {
        $corda = $this->makeCategory('corda', 'Corda');
        $this->makeProduct($corda, 'COR100', 'Corda 100', [], true);
        $this->makeProduct($corda, 'GRU900', 'Grumetta 900');

        $this->get('/it/catalogo?q=GRU9')->assertOk()->assertSee('Grumetta 900')->assertDontSee('Corda 100');
        $this->get('/it/catalogo?q=corda')->assertOk()->assertSee('Corda 100')->assertDontSee('Grumetta 900');
        $this->get('/en/catalogue?q=EN')->assertOk()->assertSee('Corda 100 EN');
    }

    public function test_product_page_shows_attributes_titles_and_no_price(): void
    {
        $corda = $this->makeCategory('corda', 'Corda');
        $this->makeProduct($corda, 'COR100', 'Corda 100', ['titles' => ['AG', 'AU 750', 'AU 585', 'AU 375']]);

        $html = $this->get('/it/prodotto/corda-100')->assertOk()->getContent();

        foreach (['COR100', 'AU 750', 'AU 585', 'AU 375', '8,00', '19,00', '5,40'] as $expected) {
            $this->assertStringContainsString($expected, $html, $expected);
        }
        // Il piè di pagina riporta il capitale sociale ("€ 25.000,00 i.v."): si controlla il solo contenuto.
        preg_match('#<main.*?</main>#s', $html, $main);
        $this->assertNotEmpty($main);
        $this->assertStringNotContainsString('€', $main[0]);
        $this->assertStringNotContainsStringIgnoringCase('prezzo', $main[0]);
        $this->assertStringNotContainsStringIgnoringCase('price', $main[0]);
    }

    public function test_product_page_has_request_quote_button_to_customer_area(): void
    {
        $this->makeProduct($this->makeCategory('corda', 'Corda'), 'COR100', 'Corda 100');

        $this->get('/it/prodotto/corda-100')->assertOk()
            ->assertSee('Richiedi preventivo')
            ->assertSee('href="'.url('/it/area-clienti').'"', false);
    }

    public function test_english_page_for_untranslated_product_renders_italian_text(): void
    {
        $this->makeProduct($this->makeCategory('corda', 'Corda'), 'COR100', 'Corda 100');

        $this->get('/en/prodotto/corda-100')->assertOk()->assertSee('Corda 100');
        $this->get('/en/catalogue')->assertOk()->assertSee('Corda 100');
    }

    public function test_catalog_slugs_follow_the_language(): void
    {
        $this->get('/it/catalogo')->assertOk();
        $this->get('/en/catalogue')->assertOk();
        $this->get('/it/catalogue')->assertNotFound();
    }

    public function test_unknown_product_returns_404(): void
    {
        $this->get('/it/prodotto/non-esiste')->assertNotFound();
    }
}
