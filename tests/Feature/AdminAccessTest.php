<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Filament\Auth\Pages\Login;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role, bool $withTwoFactor): User
    {
        $user = User::factory()->create(['role' => $role]);

        if ($withTwoFactor) {
            $user->forceFill(['app_authentication_secret' => 'JBSWY3DPEHPK3PXP'])->save();
        }

        return $user;
    }

    public function test_guests_are_sent_to_the_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_clients_cannot_open_admin(): void
    {
        $client = $this->userWithRole('cliente', true);

        $this->actingAs($client)->get('/admin')->assertForbidden();
    }

    public function test_admin_without_two_factor_is_forced_to_set_it_up(): void
    {
        $admin = $this->userWithRole('admin', false);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertRedirect();
        $this->assertStringContainsString('multi-factor', $response->headers->get('Location'));
    }

    public function test_admin_with_two_factor_is_not_logged_in_until_the_code_is_given(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'password' => 'segreta-123']);
        $admin->forceFill(['app_authentication_secret' => 'JBSWY3DPEHPK3PXP'])->save();

        Livewire::test(Login::class)
            ->fillForm(['email' => $admin->email, 'password' => 'segreta-123'])
            ->call('authenticate');

        $this->assertGuest();
    }

    public function test_login_with_correct_password_works_when_no_two_factor_is_set(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'password' => 'segreta-123']);

        Livewire::test(Login::class)
            ->fillForm(['email' => $admin->email, 'password' => 'segreta-123'])
            ->call('authenticate');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_screens_never_show_raw_translation_keys(): void
    {
        app()->setLocale('it');
        $admin = $this->userWithRole('admin', true);

        $pages = [$this->get('/admin/login')->assertOk()->getContent()];
        foreach (['/admin', '/admin/products', '/admin/categories', '/admin/pages'] as $url) {
            $pages[] = $this->actingAs($admin)->get($url)->assertOk()->getContent();
        }

        foreach ($pages as $html) {
            $this->assertDoesNotMatchRegularExpression('/filament[a-z-]*::[a-z_\/.-]+/', $html);
        }
        $this->assertStringNotContainsString('filamentphp', implode('', $pages), 'Il riquadro promozionale di Filament non va mostrato al cliente');
    }

    public function test_product_form_has_no_price_field(): void
    {
        $admin = $this->userWithRole('admin', true);
        $this->actingAs($admin);

        Livewire::test(CreateProduct::class)
            ->assertFormFieldExists('code')
            ->assertFormFieldDoesNotExist('price')
            ->assertFormFieldDoesNotExist('prezzo');
    }

    public function test_admin_avatar_is_generated_locally_without_external_services(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Maria Rossi']);

        $url = app(\App\Filament\Support\InitialsAvatarProvider::class)->get($admin);

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $url);
        $this->assertStringContainsString('>M<', base64_decode(substr($url, strlen('data:image/svg+xml;base64,'))));
    }
}
