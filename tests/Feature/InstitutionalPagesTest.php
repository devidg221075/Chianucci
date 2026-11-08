<?php

namespace Tests\Feature;

use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstitutionalPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    public function test_all_institutional_pages_exist_in_both_languages_and_return_200(): void
    {
        foreach (config('site.pages') as $key => $slugs) {
            foreach (['it', 'en'] as $locale) {
                $path = $slugs[$locale] === 'home' ? "/$locale/" : "/$locale/{$slugs[$locale]}";
                $this->get($path)->assertOk();
            }
        }
    }

    public function test_every_page_has_a_title_a_meta_description_and_content(): void
    {
        foreach (\App\Models\Page::all() as $page) {
            $this->assertNotSame('', trim($page->title), "{$page->locale}/{$page->slug} titolo");
            $this->assertNotEmpty($page->meta_description, "{$page->locale}/{$page->slug} descrizione");
            $this->assertGreaterThan(40, strlen(strip_tags($page->html)), "{$page->locale}/{$page->slug} contenuto");
        }
    }

    public function test_area_clienti_page_explains_access_and_links_to_contacts(): void
    {
        $this->get('/it/area-clienti')->assertOk()
            ->assertSee('registrati e approvati')
            ->assertSee('href="'.url('/it/contatti').'"', false);
        $this->get('/en/customer-area')->assertOk()
            ->assertSee('href="'.url('/en/contacts').'"', false);
    }

    public function test_contact_page_and_footer_show_company_data_without_opening_hours(): void
    {
        foreach (['/it/contatti', '/en/contacts'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();

            $this->assertStringContainsString('Via Baracca', $html);
            $this->assertStringContainsString('01744400514', $html);
            $this->assertStringContainsString('+39 0575 324.101', $html);
            $this->assertStringNotContainsStringIgnoringCase('orari', $html);
            $this->assertStringNotContainsStringIgnoringCase('opening hours', $html);
            $this->assertDoesNotMatchRegularExpression('/\bLun\b|\bMon\b/', $html);
        }
    }

    public function test_pages_use_the_corrected_years_of_activity(): void
    {
        $this->assertStringNotContainsString("quarant'anni", \App\Models\Page::pluck('html')->implode(' '));
    }

    public function test_page_html_contains_no_script_or_inline_event_handlers(): void
    {
        foreach (\App\Models\Page::all() as $page) {
            $this->assertStringNotContainsString('<script', $page->html);
            $this->assertDoesNotMatchRegularExpression('/\son\w+=/i', $page->html);
        }
    }
}
