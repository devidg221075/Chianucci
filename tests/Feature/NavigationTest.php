<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    private function seedPages(): void
    {
        foreach (['it' => ['home', 'azienda'], 'en' => ['home', 'company']] as $locale => $slugs) {
            foreach ($slugs as $slug) {
                Page::create(['slug' => $slug, 'locale' => $locale, 'title' => ucfirst($slug), 'html' => '<p>'.$slug.'</p>']);
            }
        }
    }

    public function test_root_redirects_to_italian_home(): void
    {
        $this->get('/')->assertStatus(301)->assertRedirect('/it/');
    }

    public function test_menu_has_the_six_entries_and_area_clienti_button_in_italian(): void
    {
        $this->seedPages();

        $this->get('/it/')->assertOk()
            ->assertSeeInOrder(['Azienda', 'Manifattura', 'Catalogo', 'Qualità', 'Servizi', 'Contatti'])
            ->assertSee('Area Clienti');
    }

    public function test_menu_is_translated_in_english(): void
    {
        $this->seedPages();

        $this->get('/en/')->assertOk()
            ->assertSeeInOrder(['Company', 'Manufacturing', 'Catalogue', 'Quality', 'Services', 'Contact'])
            ->assertSee('Customer Area');
    }

    public function test_mega_menu_lists_all_23_categories_with_images(): void
    {
        $this->seedPages();
        foreach (range(1, 23) as $i) {
            $category = Category::create(['slug' => "cat-$i", 'sort' => $i, 'image_path' => "categories/cat-$i.jpg"]);
            $category->translations()->create(['locale' => 'it', 'name' => "Categoria $i", 'slug' => "cat-$i"]);
        }

        $html = $this->get('/it/')->assertOk()->getContent();

        $this->assertSame(23, substr_count($html, 'data-mega-tile'));
        $this->assertStringContainsString('categories/cat-23.jpg', $html);
        $this->assertStringContainsString('Categoria 23', $html);
    }

    public function test_language_switch_links_to_the_same_page_in_the_other_language(): void
    {
        $this->seedPages();

        $this->get('/it/azienda')->assertOk()->assertSee('href="'.url('/en/company').'"', false);
        $this->get('/en/company')->assertOk()->assertSee('href="'.url('/it/azienda').'"', false);
    }

    public function test_unknown_locale_and_unknown_page_return_404(): void
    {
        $this->seedPages();

        $this->get('/fr/')->assertNotFound();
        $this->get('/it/pagina-che-non-esiste')->assertNotFound();
    }

    public function test_english_page_without_translation_does_not_crash_the_switch(): void
    {
        $this->seedPages();
        Page::create(['slug' => 'servizi', 'locale' => 'it', 'title' => 'Servizi', 'html' => 'x']);

        $this->get('/it/servizi')->assertOk();
    }
}
