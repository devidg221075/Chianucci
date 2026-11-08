<?php

namespace Tests\Feature;

use App\Filament\Resources\Categories\Schemas\CategoryForm;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Support\HtmlSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ReviewFixesTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(string $name): string
    {
        return file_get_contents(base_path("tests/Fixtures/legacy/$name"));
    }

    public function test_dependencies_are_locked_to_versions_that_run_on_php_8_2(): void
    {
        $lock = json_decode(file_get_contents(base_path('composer.lock')), true);
        $this->assertSame('8.2.0', $lock['platform-overrides']['php'] ?? null);

        // Composer scrive nel controllo di piattaforma la versione di PHP minima richiesta da tutte le librerie.
        $check = file_get_contents(base_path('vendor/composer/platform_check.php'));
        $this->assertStringContainsString('PHP_VERSION_ID >= 80200', $check);
        $this->assertStringNotContainsString('PHP_VERSION_ID >= 80300', $check);
    }

    public function test_admin_uploads_go_to_the_public_disk_that_the_site_serves(): void
    {
        $this->assertSame('public', ProductForm::imageUpload()->getDiskName());
        $this->assertSame('public', CategoryForm::imageUpload()->getDiskName());
        $this->assertSame(public_path('storage'), config('filesystems.disks.public.root'));
    }

    public function test_htaccess_does_not_strip_trailing_slashes_before_laravel(): void
    {
        $this->assertStringNotContainsString('Redirect Trailing Slashes', file_get_contents(public_path('.htaccess')));
    }

    public function test_sanitizer_keeps_long_pages_whole(): void
    {
        $html = str_repeat('<div class="blocco"><p class="testo">riga</p></div>', 2000).'<p id="fine">FINE</p>';

        $this->assertGreaterThan(20000, strlen($html));
        $this->assertStringContainsString('id="fine">FINE</p>', HtmlSanitizer::clean($html));
    }

    public function test_products_sharing_an_article_code_are_both_imported(): void
    {
        $sitemap = '<urlset><url><loc>https://www.chianucci.it/it/prodotto/corda-100/</loc></url><url><loc>https://www.chianucci.it/it/prodotto/corda-100-battuta/</loc></url></urlset>';
        $it = $this->fixture('product-corda-100-it.html');
        Http::fake([
            '*/product-sitemap.xml' => Http::response($sitemap),
            '*/it/prodotto/corda-100/' => Http::response($it),
            '*/it/prodotto/corda-100-battuta/' => Http::response(str_replace(['corda-100', 'Corda 100</h1>'], ['corda-100-battuta', 'Corda 100 battuta</h1>'], $it)),
            '*/wp-content/uploads/*' => Http::response('IMG'),
        ]);
        Storage::fake('public');

        $this->artisan('site:import --retry-sleep=0 --only=catalog')->assertSuccessful();

        $this->assertSame(2, Product::count());
        $this->assertNotNull(\App\Models\ProductTranslation::where('slug', 'corda-100')->first());
        $this->assertNotNull(\App\Models\ProductTranslation::where('slug', 'corda-100-battuta')->first());
    }

    public function test_reimport_keeps_values_completed_by_hand(): void
    {
        $sitemap = '<urlset><url><loc>https://www.chianucci.it/it/prodotto/corda-100/</loc></url></urlset>';
        $noWeight = preg_replace('#<strong>PESO \(gr\)</strong>: [^<]*<br />#', '', $this->fixture('product-corda-100-it.html'));
        Http::fake([
            '*/product-sitemap.xml' => Http::response($sitemap),
            '*/it/prodotto/corda-100/' => Http::response($noWeight),
            '*/wp-content/uploads/*' => Http::response('IMG'),
        ]);
        Storage::fake('public');
        $this->artisan('site:import --retry-sleep=0 --only=catalog')->assertSuccessful();

        $product = Product::firstOrFail();
        $this->assertNull($product->weight_g);
        $product->update(['weight_g' => 12, 'link_mm' => 9]);
        $product->translations()->where('locale', 'it')->update(['name' => 'Nome corretto a mano']);

        $this->artisan('site:import --retry-sleep=0 --only=catalog')->assertSuccessful();

        $product->refresh();
        $this->assertEquals(12, $product->weight_g);
        $this->assertEquals(9, $product->link_mm);
        $this->assertSame('Nome corretto a mano', $product->translations()->where('locale', 'it')->value('name'));
        $this->assertSame(1, Product::count());
    }

    public function test_admin_can_add_the_missing_english_translation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $category = Category::create(['slug' => 'corda', 'sort' => 1]);
        $product = Product::create(['code' => 'C1', 'category_id' => $category->id, 'titles' => []]);
        $product->translations()->create(['locale' => 'it', 'name' => 'Corda uno', 'slug' => 'corda-uno']);

        $component = Livewire::test(EditProduct::class, ['record' => $product->getKey()]);
        $repeater = collect($component->instance()->form->getFlatComponents())->first(fn ($c) => $c instanceof \Filament\Forms\Components\Repeater && $c->getName() === 'translations');
        $this->assertTrue($repeater->isAddable(), 'si deve poter aggiungere la lingua mancante');
        $itKey = array_key_first($component->get('data.translations'));
        $component->set('data.translations.nuovo', ['locale' => 'en', 'name' => 'Rope one', 'slug' => 'rope-one', 'description' => null])
            ->call('save')->assertHasNoFormErrors();

        $this->assertSame('Rope one', $product->translations()->where('locale', 'en')->value('name'));
        $this->assertSame(2, $product->translations()->count());
        $this->assertNotNull($itKey);
    }

    public function test_failed_image_download_leaves_no_orphan_product(): void
    {
        $sitemap = '<urlset><url><loc>https://www.chianucci.it/it/prodotto/corda-100/</loc></url></urlset>';
        Http::fake([
            '*/product-sitemap.xml' => Http::response($sitemap),
            '*/it/prodotto/corda-100/' => Http::response($this->fixture('product-corda-100-it.html')),
            '*/wp-content/uploads/*' => Http::response('boom', 500),
        ]);
        Storage::fake('public');

        $this->artisan('site:import --retry-sleep=0 --only=catalog')->assertFailed();

        $this->assertSame(0, Product::doesntHave('translations')->count(), 'prodotto orfano senza traduzione');
        $this->assertSame(0, Product::count());
    }

    public function test_contact_mail_failure_does_not_give_a_500_and_keeps_the_message_in_the_log(): void
    {
        $this->seed(\Database\Seeders\PageSeeder::class);
        Mail::shouldReceive('send')->andThrow(new \RuntimeException('smtp giù'));
        \Illuminate\Support\Facades\Log::spy();

        $this->post('/it/contatti', [
            'nome' => 'Mario Rossi', 'email' => 'mario@example.com', 'messaggio' => 'Vorrei un preventivo', 'consenso' => '1', 'sito_web' => '',
        ])->assertRedirect()->assertSessionHas('error');

        \Illuminate\Support\Facades\Log::shouldHaveReceived('error')->withArgs(fn ($m, $c = []) => str_contains($m, 'contatto') && ($c['messaggio'] ?? null) === 'Vorrei un preventivo');
    }

    public function test_installer_does_not_claim_it_deleted_itself_when_it_did_not(): void
    {
        $stub = file_get_contents(resource_path('stubs/install.php.stub'));

        $this->assertStringContainsString('file_exists(__FILE__)', $stub);
        $this->assertStringContainsString('NON è stato cancellato', $stub);
    }
}
