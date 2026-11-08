<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CatalogModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_table_has_a_price_column(): void
    {
        foreach (Schema::getTables() as $table) {
            foreach (Schema::getColumnListing($table['name']) as $column) {
                $this->assertDoesNotMatchRegularExpression(
                    '/price|prezzo|cost|costo|euro/i',
                    $column,
                    "Colonna vietata {$table['name']}.{$column}"
                );
            }
        }
    }

    public function test_product_falls_back_to_italian_when_english_is_missing(): void
    {
        $category = Category::create(['slug' => 'corda', 'sort' => 1]);
        $product = Product::create(['code' => 'COR100', 'category_id' => $category->id, 'titles' => ['AG']]);
        $product->translations()->create(['locale' => 'it', 'name' => 'Corda 100', 'slug' => 'corda-100']);

        $found = Product::visibleIn('en')->get();

        $this->assertCount(1, $found);
        $this->assertSame('Corda 100', $found->first()->translated('en')->name);
    }

    public function test_product_with_english_translation_uses_it(): void
    {
        $category = Category::create(['slug' => 'corda', 'sort' => 1]);
        $product = Product::create(['code' => 'COR100', 'category_id' => $category->id, 'titles' => ['AG']]);
        $product->translations()->create(['locale' => 'it', 'name' => 'Corda 100', 'slug' => 'corda-100']);
        $product->translations()->create(['locale' => 'en', 'name' => 'Rope 100', 'slug' => 'rope-100']);

        $this->assertSame('Rope 100', Product::visibleIn('en')->first()->translated('en')->name);
        $this->assertSame('Corda 100', Product::visibleIn('it')->first()->translated('it')->name);
    }

    public function test_product_without_any_translation_is_not_visible(): void
    {
        $category = Category::create(['slug' => 'corda', 'sort' => 1]);
        Product::create(['code' => 'X', 'category_id' => $category->id, 'titles' => []]);

        $this->assertCount(0, Product::visibleIn('it')->get());
    }

    public function test_category_name_falls_back_to_italian(): void
    {
        $category = Category::create(['slug' => 'rolo', 'sort' => 1]);
        $category->translations()->create(['locale' => 'it', 'name' => 'Rolò', 'slug' => 'rolo']);

        $this->assertSame('Rolò', $category->translated('en')->name);
    }
}
