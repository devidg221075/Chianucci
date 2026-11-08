<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer as SymfonySanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class HtmlSanitizer
{
    private static ?SymfonySanitizer $sanitizer = null;

    /** Elementi di layout usati dall'editor libero, oltre a quelli sicuri di default. */
    private const LAYOUT_ELEMENTS = ['section', 'header', 'footer', 'nav', 'article', 'aside', 'main', 'figure', 'figcaption'];

    public static function clean(string $html): string
    {
        return self::sanitizer()->sanitize($html);
    }

    public static function cleanCss(string $css): string
    {
        $css = strip_tags($css);
        $css = preg_replace('/@import[^;]*;?/i', '', $css);
        $css = preg_replace('/(javascript|vbscript)\s*:/i', '', $css);
        $css = preg_replace('/expression\s*\(|-moz-binding|behavior\s*:/i', '', $css);

        return trim($css);
    }

    private static function sanitizer(): SymfonySanitizer
    {
        if (self::$sanitizer === null) {
            $config = (new HtmlSanitizerConfig())
                ->withMaxInputLength(5_000_000) // di default il sanificatore taglia a 20.000 byte
                ->allowSafeElements()
                ->allowLinkSchemes(['https', 'http', 'mailto', 'tel'])
                ->allowRelativeLinks()
                ->allowMediaSchemes(['https', 'http'])
                ->allowRelativeMedias()
                ->allowAttribute('class', '*')
                ->allowAttribute('id', '*')
                ->allowAttribute('target', 'a')
                ->allowAttribute('rel', 'a');

            foreach (self::LAYOUT_ELEMENTS as $element) {
                $config = $config->allowElement($element, ['class', 'id']);
            }

            self::$sanitizer = new SymfonySanitizer($config);
        }

        return self::$sanitizer;
    }
}
