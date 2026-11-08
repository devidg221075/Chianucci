<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogExportTest extends TestCase
{
    use RefreshDatabase;

    private string $file;

    protected function setUp(): void
    {
        parent::setUp();
        $this->file = sys_get_temp_dir().'/catalog-test-'.uniqid().'.json';
    }

    protected function tearDown(): void
    {
        @unlink($this->file);
        parent::tearDown();
    }

    private function seedCatalog(): void
    {
        $cat = Category::create(['slug' => 'corda', 'sort' => 3, 'image_path' => 'categories/corda.jpg']);
        $cat->translations()->create(['locale' => 'it', 'name' => 'Corda', 'slug' => 'corda']);
        $cat->translations()->create(['locale' => 'en', 'name' => 'Rope', 'slug' => 'rope']);
        $p = Product::create(['code' => 'Corda 100', 'category_id' => $cat->id, 'weight_g' => 8, 'length_cm' => 19, 'link_mm' => 5.4, 'titles' => ['AG', 'AU 750']]);
        $p->translations()->create(['locale' => 'it', 'name' => 'Corda 100', 'slug' => 'corda-100', 'description' => 'Catena a corda.']);
        $p->translations()->create(['locale' => 'en', 'name' => 'Corda 100', 'slug' => 'corda-100']);
        $p->images()->create(['path' => 'products/corda-100-1.jpg', 'sort' => 0]);
    }

    public function test_export_then_load_restores_the_same_catalog(): void
    {
        $this->seedCatalog();
        $this->artisan('site:export-catalog', ['path' => $this->file])->assertSuccessful();

        Product::query()->delete();
        Category::query()->delete();
        $this->assertSame(0, Product::count());

        $this->artisan('site:load-catalog', ['path' => $this->file])->assertSuccessful();

        $product = Product::where('code', 'Corda 100')->firstOrFail();
        $this->assertSame(['AG', 'AU 750'], $product->titles);
        $this->assertEquals(5.4, $product->link_mm);
        $this->assertSame('Catena a corda.', $product->translations()->where('locale', 'it')->value('description'));
        $this->assertSame('products/corda-100-1.jpg', $product->images()->first()->path);
        $this->assertSame('Rope', $product->category->translations()->where('locale', 'en')->value('name'));
        $this->assertSame(3, $product->category->sort);
    }

    public function test_load_is_idempotent(): void
    {
        $this->seedCatalog();
        $this->artisan('site:export-catalog', ['path' => $this->file])->assertSuccessful();

        $this->artisan('site:load-catalog', ['path' => $this->file])->assertSuccessful();
        $this->artisan('site:load-catalog', ['path' => $this->file])->assertSuccessful();

        $this->assertSame(1, Product::count());
        $this->assertSame(2, ProductTranslation::count());
        $this->assertSame(1, Category::count());
    }

    public function test_export_file_contains_no_price(): void
    {
        $this->seedCatalog();
        $this->artisan('site:export-catalog', ['path' => $this->file])->assertSuccessful();

        $this->assertDoesNotMatchRegularExpression('/price|prezzo|definire/i', file_get_contents($this->file));
    }

    public function test_load_fails_clearly_on_a_missing_or_broken_file(): void
    {
        $this->artisan('site:load-catalog', ['path' => '/non/esiste.json'])->assertFailed();
        file_put_contents($this->file, '{non json');
        $this->artisan('site:load-catalog', ['path' => $this->file])->assertFailed();
        $this->assertSame(0, Product::count());
    }

    public function test_redirects_travel_with_the_catalog_export(): void
    {
        $this->seedCatalog();
        \App\Models\Redirect::create(['from_path' => '/it/lavorazioni/fusione', 'to_path' => '/it/fusione']);
        $this->artisan('site:export-catalog', ['path' => $this->file])->assertSuccessful();

        \App\Models\Redirect::query()->delete();
        $this->artisan('site:load-catalog', ['path' => $this->file])->assertSuccessful();

        $this->assertSame('/it/fusione', \App\Models\Redirect::where('from_path', '/it/lavorazioni/fusione')->value('to_path'));
    }
}
