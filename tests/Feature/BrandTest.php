<?php

namespace Tests\Feature;

use Tests\TestCase;

class BrandTest extends TestCase
{
    public function test_brand_tokens_are_the_logo_colors(): void
    {
        $css = file_get_contents(resource_path('css/site.css'));

        $this->assertStringContainsStringIgnoringCase('#2A1B6D', $css);
        $this->assertStringContainsStringIgnoringCase('#BD9F69', $css);
        $this->assertStringNotContainsStringIgnoringCase('#3B2385', $css);
        $this->assertStringNotContainsStringIgnoringCase('#C9A96B', $css);
    }

    public function test_web_logos_have_no_hidden_embedded_image(): void
    {
        foreach (['chianucci.svg', 'chianucci-bianco.svg'] as $file) {
            $svg = file_get_contents(public_path('brand/'.$file));

            $this->assertStringNotContainsString('<image', $svg, $file);
            $this->assertStringNotContainsString('base64', $svg, $file);
        }
    }

    public function test_original_logos_are_untouched(): void
    {
        $original = file_get_contents(base_path('design/logo/originals/CHIANUCCI.svg'));

        $this->assertStringContainsString('<image', $original);
        $this->assertSame(
            '634d94761d20ce8892c5b7bc787eca3f70aec68d631f3d5821cf2db1c81c3c8a',
            hash('sha256', $original)
        );
    }

    public function test_gold_is_never_used_as_text_color_on_light_backgrounds(): void
    {
        // #BD9F69 su avorio/bianco ha contrasto 2,3:1 (minimo WCAG AA: 4,5:1): per i testi si usa "oro-scuro".
        $files = array_merge(
            glob(resource_path('views/*/*.blade.php')),
            glob(resource_path('views/*.blade.php')),
            glob(resource_path('views/*/*/*.blade.php')),
            [base_path('database/seeders/PageSeeder.php'), resource_path('js/page-editor.js')]
        );
        $this->assertNotEmpty($files);
        foreach ($files as $file) {
            $this->assertDoesNotMatchRegularExpression('/(?<![\w:-])text-oro(?![\w-])/', file_get_contents($file), basename($file));
        }
    }

    public function test_dark_gold_text_token_meets_contrast_on_ivory(): void
    {
        $css = file_get_contents(resource_path('css/site.css'));
        preg_match('/--color-oro-scuro:\s*(#[0-9A-Fa-f]{6})/', $css, $m);
        $this->assertNotEmpty($m, 'token oro-scuro mancante');

        $lum = function (string $hex): float {
            $c = array_map(fn ($v) => ($v /= 255) <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4, sscanf($hex, '#%02x%02x%02x'));

            return 0.2126 * $c[0] + 0.7152 * $c[1] + 0.0722 * $c[2];
        };
        $ratio = (max($lum('#F7F3E8'), $lum($m[1])) + 0.05) / (min($lum('#F7F3E8'), $lum($m[1])) + 0.05);

        $this->assertGreaterThanOrEqual(4.5, $ratio);
    }
}
