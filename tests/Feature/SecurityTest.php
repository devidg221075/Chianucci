<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PageSeeder;
use Filament\Auth\Pages\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_present_on_public_pages(): void
    {
        $this->seed(PageSeeder::class);

        $response = $this->get('/it/')->assertOk();

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString("default-src 'self'", $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString("object-src 'none'", $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString("frame-ancestors 'self'", $response->headers->get('Content-Security-Policy'));
        $this->assertNotNull($response->headers->get('Permissions-Policy'));
    }

    public function test_security_headers_also_cover_404_pages(): void
    {
        $this->get('/it/non-esiste')->assertNotFound()->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_admin_login_is_throttled_after_five_attempts(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'password' => 'segreta-123']);

        foreach (range(1, 5) as $i) {
            Livewire::test(Login::class)->fillForm(['email' => $admin->email, 'password' => 'sbagliata'])->call('authenticate');
        }

        // Dal sesto tentativo, anche la password giusta non entra.
        Livewire::test(Login::class)->fillForm(['email' => $admin->email, 'password' => 'segreta-123'])->call('authenticate');

        $this->assertGuest();
    }

    public function test_no_third_party_scripts_or_resources_load(): void
    {
        $this->seed(PageSeeder::class);
        $host = parse_url(url('/'), PHP_URL_HOST);

        $html = $this->get('/it/')->assertOk()->getContent();

        preg_match_all('#<(?:script|link|img|iframe)[^>]+(?:src|href)="(https?://[^"]+)"#i', $html, $m);
        foreach ($m[1] as $src) {
            $this->assertSame($host, parse_url($src, PHP_URL_HOST), "Risorsa esterna: $src");
        }
        $this->assertStringNotContainsString('googletagmanager', $html);
        $this->assertStringNotContainsString('google-analytics', $html);
    }

    public function test_cookie_notice_is_shown_in_both_languages_and_links_the_privacy_policy(): void
    {
        $this->seed(PageSeeder::class);

        $this->get('/it/')->assertSee('data-cookie-banner', false)->assertSee('cookie tecnici')
            ->assertSee('href="'.url('/it/privacy-policy').'"', false);
        $this->get('/en/')->assertSee('data-cookie-banner', false)->assertSee('technical cookies');
    }

    public function test_uploaded_media_cannot_run_scripts(): void
    {
        $htaccess = file_get_contents(public_path('.htaccess'));

        $this->assertMatchesRegularExpression('#RewriteRule \^storage/\.\*\\\.\(php\|phtml\|phar\)\$ - \[F,L\]#', $htaccess);
    }
}
