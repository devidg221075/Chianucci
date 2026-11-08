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
}
