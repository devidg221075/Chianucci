<?php

namespace App\Console\Commands;

use App\Importer\CatalogImporter;
use App\Importer\CatalogParser;
use App\Importer\PageParser;
use App\Importer\SiteClient;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Product;
use App\Models\Redirect;
use App\Support\LegacyUrlMap;
use App\Models\ProductTranslation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportSite extends Command
{
    protected $signature = 'site:import {--only=catalog : catalog|pages|redirects} {--resume : salta gli indirizzi già importati} {--base=https://www.chianucci.it} {--retry-sleep=1500 : attesa in ms tra i tentativi}';

    protected $description = 'Importa catalogo e testi dal sito pubblico attuale';

    public function handle(): int
    {
        $client = new SiteClient($this->option('base'), 4, (int) $this->option('retry-sleep'));

        return match ($this->option('only')) {
            'catalog' => $this->catalog($client),
            'pages' => $this->pages($client),
            'redirects' => $this->redirects($client),
            default => $this->fail('Usa --only=catalog oppure --only=pages'),
        };
    }

    private function catalog(SiteClient $client): int
    {
        $importer = new CatalogImporter($client, new CatalogParser(), (bool) $this->option('resume'));
        $importer->run();

        $this->info('Prodotti: '.Product::count().' | categorie: '.Category::count());
        foreach (['it', 'en'] as $locale) {
            $this->line(strtoupper($locale).': '.ProductTranslation::where('locale', $locale)->count().' schede');
        }
        foreach (Category::with('translations')->orderBy('sort')->get() as $category) {
            $this->line(sprintf('  %-22s %d prodotti', $category->translated('it')?->name ?? $category->slug, $category->products()->count()));
        }
        foreach ($importer->incomplete as $code => $field) {
            $this->warn("Campo mancante in $code: $field");
        }
        foreach ($importer->duplicates as $slug => $code) {
            $this->warn("Codice articolo ripetuto: $slug usa lo stesso codice di un altro prodotto ($code)");
        }
        foreach ($importer->failures as $url => $error) {
            $this->error("$url: $error");
        }

        return $importer->failures === [] ? self::SUCCESS : self::FAILURE;
    }

    /** Mappa dei vecchi indirizzi: dalla sitemap delle pagine vecchie e dalle categorie importate. */
    private function redirects(SiteClient $client): int
    {
        $map = new LegacyUrlMap();
        $xml = $client->get(rtrim($client->base, '/').'/page-sitemap.xml');
        preg_match_all('#<loc>\s*([^<]+?)\s*</loc>#', $xml, $m);

        $pairs = [];
        foreach ($m[1] as $url) {
            $path = parse_url($url, PHP_URL_PATH);
            if (($to = $map->map($path)) !== null) {
                $pairs[Redirect::normalize($path)] = $to;
            }
        }
        foreach (CategoryTranslation::with('category')->get() as $t) {
            $slug = config("site.catalog_slug.{$t->locale}");
            $pairs[Redirect::normalize("/{$t->locale}/categoria-prodotto/{$t->slug}")] = "/{$t->locale}/$slug?categoria={$t->category->slug}";
        }

        foreach ($pairs as $from => $to) {
            Redirect::updateOrCreate(['from_path' => $from], ['to_path' => $to]);
        }
        $this->info(count($pairs).' reindirizzamenti 301 registrati');

        return self::SUCCESS;
    }

    /** Salva i testi delle pagine vecchie per la revisione; non tocca le pagine del nuovo sito. */
    private function pages(SiteClient $client): int
    {
        $parser = new PageParser();
        $targets = [
            'it' => ['azienda-chi-siamo-maurizio-chianucci', 'lavorazioni', 'lavorazioni/fusione', 'lavorazioni/preparazione', 'lavorazioni/saldatura', 'lavorazioni/pulitura-e-lucidatura', 'lavorazioni/galvanica', 'contatti'],
            'en' => ['company-about-us-maurizio-chianucci', 'processings', 'processings/fusion', 'processings/preparation', 'processings/welding', 'processings/cleaning-and-polishing', 'processings/galvanic', 'contacts'],
        ];
        $failed = 0;
        foreach ($targets as $locale => $slugs) {
            foreach ($slugs as $slug) {
                try {
                    $page = $parser->parse($client->get(rtrim($client->base, '/')."/$locale/$slug/"));
                    $lines = [$page['title'], str_repeat('=', 20)];
                    foreach ($page['blocks'] as $b) {
                        $lines[] = "[{$b['tag']}] {$b['text']}";
                    }
                    Storage::disk('local')->put('legacy-pages/'.$locale.'-'.str_replace('/', '_', $slug).'.txt', implode("\n", $lines));
                    $this->line("$locale/$slug: ".count($page['blocks']).' blocchi');
                } catch (\Throwable $e) {
                    $failed++;
                    $this->error("$locale/$slug: ".$e->getMessage());
                }
            }
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
