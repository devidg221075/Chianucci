<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Regole della tavola grafica Natural Luxury 03 / Master (8pt, griglia 12-8-4, scala dei titoli, foto).
 * Ogni regola fallisce se il sito se ne allontana.
 */
class DesignRulesTest extends TestCase
{
    private function css(): string
    {
        return file_get_contents(resource_path('css/site.css'));
    }

    /** @return array<string,string> percorso => contenuto, di tutto ciò che produce HTML o classi */
    private function sources(): array
    {
        $paths = array_merge(
            glob(resource_path('views/*.blade.php')),
            glob(resource_path('views/*/*.blade.php')),
            glob(resource_path('views/*/*/*.blade.php')),
            [base_path('database/seeders/PageSeeder.php'), resource_path('js/page-editor.js')]
        );
        $out = [];
        foreach ($paths as $p) {
            if (str_contains($p, '/views/filament/')) {
                continue; // il pannello admin usa lo stile di Filament
            }
            $out[$p] = file_get_contents($p);
        }

        return $out;
    }

    public function test_spacing_utilities_follow_the_8pt_scale(): void
    {
        // Scala della tavola: 8, 16, 24, 32, 48, 64 px. In Tailwind 1 unità = 4 px: 2, 4, 6, 8, 12, 16 (e 0).
        $allowed = ['0', '2', '4', '6', '8', '12', '16'];
        $bad = [];
        foreach ($this->sources() as $path => $code) {
            preg_match_all('/(?<![\w-])(?:[a-z]+:)*-?(?:p|px|py|pt|pb|pl|pr|m|mx|my|mt|mb|ml|mr|gap|gap-x|gap-y|space-x|space-y)-(\[[^\]]+\]|[\d.]+|px)(?![\w.])/', $code, $m);
            foreach ($m[1] as $value) {
                if (! in_array($value, $allowed, true)) {
                    $bad[] = basename($path).': '.$value;
                }
            }
        }
        $this->assertSame([], array_values(array_unique($bad)), 'Spaziature fuori scala 8pt');
    }

    public function test_grid_breakpoints_and_container_match_the_board(): void
    {
        $css = $this->css();

        $this->assertStringContainsString('--breakpoint-md: 768px', $css);
        $this->assertStringContainsString('--breakpoint-lg: 1200px', $css);
        $this->assertMatchesRegularExpression('/\.contenitore\s*\{[^}]*max-width:\s*1248px/s', $css, 'contenitore: 1200 px di griglia + 2x24 px di margine');
        foreach ([4, 8, 12] as $cols) {
            $this->assertStringContainsString("repeat($cols, minmax(0, 1fr))", $css, "griglia a $cols colonne");
        }
    }

    public function test_type_scale_uses_baseline_of_8_and_the_board_fonts(): void
    {
        $css = $this->css();

        preg_match_all('/line-height:\s*(\d+)px/', $css, $m);
        $this->assertNotEmpty($m[1]);
        foreach ($m[1] as $lh) {
            $this->assertSame(0, ((int) $lh) % 8, "line-height {$lh}px non è multiplo di 8");
        }
        foreach (['h1', 'h2', 'h3', '.occhiello', '.lead'] as $selector) {
            $this->assertStringContainsString($selector, $css, "stile per $selector");
        }
        $this->assertStringContainsString('Montserrat', $css);
        $this->assertStringContainsString('Inter', $css);
    }

    public function test_headings_and_lead_text_are_not_sized_with_ad_hoc_utilities(): void
    {
        $bad = [];
        foreach ($this->sources() as $path => $code) {
            if (preg_match_all('/(?<![\w-])(?:[a-z]+:)*text-(?:lg|xl|[2-9]xl)(?![\w-])/', $code, $m)) {
                $bad[basename($path)] = array_unique($m[0]);
            }
        }
        $this->assertSame([], $bad, 'Le dimensioni dei titoli vengono dalla scala in site.css (h1, h2, h3, .lead)');
    }

    public function test_photos_use_the_fixed_aspect_ratio_classes(): void
    {
        $bad = [];
        foreach ($this->sources() as $path => $code) {
            preg_match_all('/<img\b[^>]*object-(?:cover|contain)[^>]*>/', $code, $m);
            foreach ($m[0] as $tag) {
                if (! preg_match('/\bfoto-(card|sezione|prodotto|hero|miniatura)\b/', $tag)) {
                    $bad[] = basename($path).': '.substr($tag, 0, 90);
                }
            }
            $this->assertDoesNotMatchRegularExpression('/(?<![\w-])h-(?:40|56|80)(?![\w-])[^>]*object-/', $code, basename($path).': altezze fisse sulle foto');
        }
        $this->assertSame([], $bad, 'Foto senza proporzione della tavola');
        foreach (['.foto-card', '.foto-sezione', '.foto-prodotto', '.foto-hero'] as $class) {
            $this->assertStringContainsString($class, $this->css());
        }
    }

    public function test_buttons_follow_the_board_components(): void
    {
        $css = $this->css();

        // Primario pieno, secondario con bordo, link testuale; tutti con freccia come nella tavola.
        foreach (['.btn-primario', '.btn-secondario', '.link-testo'] as $class) {
            $this->assertStringContainsString($class, $css);
        }
        $this->assertStringContainsString("content: '→'", $css);
        $this->assertMatchesRegularExpression('/\.btn-primario[^{]*\{[^}]*height:\s*48px/s', $css, 'altezza pulsanti 48 px');
    }

    public function test_every_page_section_uses_the_shared_container_and_grid(): void
    {
        $seeder = file_get_contents(base_path('database/seeders/PageSeeder.php'));

        $this->assertDoesNotMatchRegularExpression('/max-w-(?:\d?xl|screen|prose)/', $seeder, 'larghezze fisse: si usa .contenitore');
        $this->assertStringContainsString('contenitore', $seeder);
        $this->assertStringContainsString('griglia', $seeder);
    }
}
