<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class PackageTest extends TestCase
{
    use RefreshDatabase;

    private string $out;

    private string $token;

    private string $marker;

    private array $entries = [];

    private ZipArchive $zip;

    protected function setUp(): void
    {
        parent::setUp();
        $this->out = sys_get_temp_dir().'/pkg-test-'.uniqid();
        File::ensureDirectoryExists(storage_path('app/public/products'));
        $this->marker = storage_path('app/public/products/pkg-test-marker.jpg');
        file_put_contents($this->marker, 'IMG');

        $cat = Category::create(['slug' => 'corda', 'sort' => 1]);
        $cat->translations()->create(['locale' => 'it', 'name' => 'Corda', 'slug' => 'corda']);
        $p = Product::create(['code' => 'Corda 100', 'category_id' => $cat->id, 'titles' => ['AG']]);
        $p->translations()->create(['locale' => 'it', 'name' => 'Corda 100', 'slug' => 'corda-100']);

        $code = Artisan::call('site:package', ['--output' => $this->out, '--vendor' => 'stub', '--skip-build' => true]);
        $output = Artisan::output();
        $this->assertSame(0, $code, $output);
        preg_match('/TOKEN: ([0-9a-f]{24})/', $output, $m);
        $this->token = $m[1] ?? '';

        $this->zip = new ZipArchive();
        $zips = glob($this->out.'/chianucci-*.zip');
        $this->assertCount(1, $zips);
        $this->assertTrue($this->zip->open($zips[0]) === true);
        for ($i = 0; $i < $this->zip->numFiles; $i++) {
            $this->entries[] = $this->zip->getNameIndex($i);
        }
    }

    protected function tearDown(): void
    {
        @unlink($this->marker);
        File::deleteDirectory($this->out);
        parent::tearDown();
    }

    public function test_package_contains_app_public_html_built_assets_and_the_catalog(): void
    {
        foreach (['chianucci_app/artisan', 'chianucci_app/vendor/autoload.php', 'chianucci_app/database/catalog.json',
            'chianucci_app/.env.example', 'public_html/index.php', 'public_html/.htaccess', 'public_html/install.php',
            'public_html/brand/chianucci.svg', 'public_html/storage/products/pkg-test-marker.jpg', 'GUIDA-PUBBLICAZIONE.md',
            'chianucci_app/bootstrap/cache/.gitignore', 'chianucci_app/storage/framework/sessions/.gitignore', 'chianucci_app/storage/logs/.gitignore'] as $expected) {
            $this->assertContains($expected, $this->entries, $expected);
        }
        $this->assertNotEmpty(array_filter($this->entries, fn ($e) => str_starts_with($e, 'public_html/build/')), 'asset compilati mancanti');
        $this->assertStringContainsString('Corda 100', $this->zip->getFromName('chianucci_app/database/catalog.json'));
    }

    public function test_package_has_no_dev_files_secrets_or_original_logos(): void
    {
        $forbidden = ['chianucci_app/tests/', 'chianucci_app/.git', 'chianucci_app/design/', 'chianucci_app/node_modules/',
            'chianucci_app/.superpowers/', 'chianucci_app/docs/', 'chianucci_app/CLAUDE.md', 'chianucci_app/.env', 'chianucci_app/storage/logs/laravel.log'];
        foreach ($this->entries as $entry) {
            foreach ($forbidden as $bad) {
                $this->assertFalse($entry === $bad || str_starts_with($entry, rtrim($bad, '/').'/'), "Non deve esserci: $entry");
            }
            $this->assertStringNotContainsString('.sqlite', $entry);
            $this->assertStringNotContainsString('CHIANUCCI.ai', $entry);
            $this->assertStringNotContainsString('public_html/hot', $entry);
        }
        // La copia web del logo non contiene l'immagine nascosta dell'originale.
        $this->assertStringNotContainsString('base64', $this->zip->getFromName('public_html/brand/chianucci.svg'));
    }

    public function test_index_php_points_to_the_app_folder_outside_the_web_root(): void
    {
        $index = $this->zip->getFromName('public_html/index.php');

        $this->assertStringContainsString("/../chianucci_app/vendor/autoload.php", $index);
        $this->assertStringContainsString("/../chianucci_app/bootstrap/app.php", $index);
    }

    public function test_install_script_stores_only_a_hash_of_the_token_and_deletes_itself(): void
    {
        $install = $this->zip->getFromName('public_html/install.php');

        $this->assertMatchesRegularExpression('/[0-9a-f]{64}/', $install);
        $this->assertStringContainsString(hash('sha256', $this->token), $install);
        $this->assertStringContainsString('unlink(__FILE__)', $install);
        $this->assertStringContainsString('hash_equals', $install);
        foreach ($this->entries as $entry) {
            $content = $this->zip->getFromName($entry);
            $this->assertStringNotContainsString($this->token, (string) $content, "Il token compare in $entry");
        }
        $this->assertNotSame('', $this->token);
    }

    public function test_production_env_example_is_safe_and_ready_for_mysql(): void
    {
        $env = $this->zip->getFromName('chianucci_app/.env.example');

        $this->assertStringContainsString('APP_ENV=production', $env);
        $this->assertStringContainsString('APP_DEBUG=false', $env);
        $this->assertStringContainsString('DB_CONNECTION=mysql', $env);
        $this->assertStringContainsString('SESSION_DRIVER=file', $env);
        $this->assertStringNotContainsString('APP_KEY=base64', $env);
    }

    public function test_publication_guide_covers_upload_rollback_and_backups(): void
    {
        $guide = $this->zip->getFromName('GUIDA-PUBBLICAZIONE.md');

        foreach (['sottodominio', 'install.php', 'tornare alla versione precedente', 'backup', 'database', 'storage', '.env'] as $needle) {
            $this->assertStringContainsStringIgnoringCase($needle, $guide, $needle);
        }
    }
}
