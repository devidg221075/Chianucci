<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_html_is_sanitized_on_save(): void
    {
        $page = Page::create([
            'slug' => 'azienda', 'locale' => 'it', 'title' => 'Azienda',
            'html' => '<h1>Ciao</h1><script>alert(1)</script>',
            'css' => '.a{color:red}@import url(//evil.css);',
        ]);

        $page->refresh();

        $this->assertStringContainsString('<h1>Ciao</h1>', $page->html);
        $this->assertStringNotContainsString('script', $page->html);
        $this->assertStringNotContainsString('@import', $page->css);
    }

    public function test_same_slug_can_exist_in_two_languages_but_not_twice_in_one(): void
    {
        Page::create(['slug' => 'azienda', 'locale' => 'it', 'title' => 'Azienda', 'html' => '']);
        Page::create(['slug' => 'azienda', 'locale' => 'en', 'title' => 'Company', 'html' => '']);

        $this->assertSame(2, Page::count());

        $this->expectException(\Illuminate\Database\QueryException::class);
        Page::create(['slug' => 'azienda', 'locale' => 'it', 'title' => 'Doppia', 'html' => '']);
    }

    public function test_page_form_offers_the_free_editor_in_the_admin(): void
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        \Livewire\Livewire::test(\App\Filament\Resources\Pages\Pages\CreatePage::class)
            ->assertFormFieldExists('html')
            ->assertFormFieldExists('locale')
            ->assertFormFieldExists('slug');
    }
}
