<?php

namespace Tests\Unit;

use App\Support\HtmlSanitizer;
use PHPUnit\Framework\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public function test_sanitizer_removes_scripts_handlers_and_iframes(): void
    {
        $dirty = '<p onclick="x()">a</p><script>alert(1)</script><iframe src="//evil"></iframe><img src=x onerror=y>';

        $clean = HtmlSanitizer::clean($dirty);

        foreach (['script', 'onclick', 'iframe', 'onerror', 'alert'] as $bad) {
            $this->assertStringNotContainsString($bad, $clean, $bad);
        }
        $this->assertStringContainsString('<p>a</p>', $clean);
    }

    public function test_sanitizer_keeps_links_images_and_classes(): void
    {
        $html = '<a href="/it/catalogo/" class="btn-primario">Vai</a><img src="/storage/a.jpg" alt="x">';

        $clean = HtmlSanitizer::clean($html);

        $this->assertStringContainsString('<a href="/it/catalogo/" class="btn-primario">Vai</a>', $clean);
        $this->assertStringContainsString('src="/storage/a.jpg"', $clean);
        $this->assertStringContainsString('alt="x"', $clean);
    }

    public function test_sanitizer_blocks_javascript_urls(): void
    {
        $clean = HtmlSanitizer::clean('<a href="javascript:alert(1)">x</a>');

        $this->assertStringNotContainsString('javascript', $clean);
    }

    public function test_sanitizer_keeps_layout_sections_and_ids(): void
    {
        $clean = HtmlSanitizer::clean('<section id="hero" class="grid"><div class="col"><h2>T</h2></div></section>');

        $this->assertStringContainsString('<section', $clean);
        $this->assertStringContainsString('class="grid"', $clean);
        $this->assertStringContainsString('<h2>T</h2>', $clean);
    }

    public function test_css_is_stripped_of_scripts_and_external_imports(): void
    {
        $clean = HtmlSanitizer::cleanCss('.a{color:red} @import url(//evil.css); .b{background:url(javascript:x)} </style><script>x</script>');

        $this->assertStringContainsString('.a{color:red}', $clean);
        $this->assertStringNotContainsString('@import', $clean);
        $this->assertStringNotContainsString('javascript', $clean);
        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('</style', $clean);
    }
}
