<?php

namespace Tests\Feature;

use App\Models\Redirect;
use App\Support\LegacyUrlMap;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_url_with_trailing_slash_uppercase_and_utm_redirects_once(): void
    {
        $this->seed(PageSeeder::class);
        Redirect::create(['from_path' => '/it/lavorazioni/fusione', 'to_path' => '/it/fusione']);

        $this->get('/IT/Lavorazioni/Fusione/?utm_source=x')->assertStatus(301)->assertRedirect('/it/fusione');
        $this->get('/it/fusione')->assertOk();
    }

    public function test_redirect_chains_are_flattened_on_save(): void
    {
        Redirect::create(['from_path' => '/it/a', 'to_path' => '/it/b']);
        Redirect::create(['from_path' => '/it/b', 'to_path' => '/it/c']);

        $this->assertSame('/it/c', Redirect::where('from_path', '/it/a')->value('to_path'));
        $this->assertSame('/it/c', Redirect::where('from_path', '/it/b')->value('to_path'));
    }

    public function test_a_redirect_to_itself_is_refused(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Redirect::create(['from_path' => '/it/a/', 'to_path' => '/it/a']);
    }

    public function test_existing_pages_are_never_hijacked_by_a_redirect_table_entry_equal_to_them(): void
    {
        $this->seed(PageSeeder::class);

        $this->get('/it/azienda')->assertOk();
        $this->get('/it/azienda/')->assertOk();
    }

    public function test_unknown_url_returns_404_not_500(): void
    {
        $this->get('/it/questa-pagina-non-esiste/')->assertNotFound();
        $this->get('/xx/yy')->assertNotFound();
    }

    public function test_redirect_keeps_query_for_category_destinations(): void
    {
        Redirect::create(['from_path' => '/it/categoria-prodotto/corda', 'to_path' => '/it/catalogo?categoria=corda']);

        $this->get('/it/categoria-prodotto/corda/')->assertStatus(301)->assertRedirect('/it/catalogo?categoria=corda');
    }

    public function test_legacy_map_translates_old_site_urls(): void
    {
        $map = new LegacyUrlMap();

        $this->assertSame('/it/azienda', $map->map('/it/azienda-chi-siamo-maurizio-chianucci/'));
        $this->assertSame('/en/company', $map->map('/en/company-about-us-maurizio-chianucci/'));
        $this->assertSame('/it/manifattura', $map->map('/it/lavorazioni/'));
        $this->assertSame('/it/fusione', $map->map('/it/lavorazioni/fusione/'));
        $this->assertSame('/en/manufacturing', $map->map('/en/processings/'));
        $this->assertSame('/en/cleaning-and-polishing', $map->map('/en/processings/cleaning-and-polishing/'));
        $this->assertSame('/it/pulitura-e-lucidatura', $map->map('/it/lavorazioni/pulitura-e-lucidatura/'));
        $this->assertSame('/it/area-clienti', $map->map('/it/my-account/'));
        $this->assertSame('/it/area-clienti', $map->map('/it/carrello/'));
        $this->assertSame('/en/customer-area', $map->map('/en/checkout-2/'));
        $this->assertSame('/it/catalogo', $map->map('/it/negozio/'));
        $this->assertSame('/it/catalogo', $map->map('/it/catalogo/catalogo-pdf/'));
        $this->assertNull($map->map('/it/prodotto/corda-100/'), 'Le schede prodotto mantengono lo stesso indirizzo');
        $this->assertNull($map->map('/it/contatti/'));
    }
}
