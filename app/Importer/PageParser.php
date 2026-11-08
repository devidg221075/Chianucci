<?php

namespace App\Importer;

use DOMDocument;
use DOMXPath;

/** Estrae titoli e paragrafi di una pagina del vecchio sito, per la revisione editoriale. */
class PageParser
{
    /** @return array{title:string,blocks:array<int,array{tag:string,text:string}>} */
    public function parse(string $html): array
    {
        $html = preg_replace('#<(script|style|noscript)[^>]*>.*?</\1>#is', '', $html);
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        $xp = new DOMXPath($dom);

        $title = trim($xp->query('//title')->item(0)?->textContent ?? '');
        $title = trim(preg_replace('/\s*-\s*CHIANUCCI MAURIZIO\s*$/i', '', $title));

        $blocks = [];
        foreach ($xp->query('//h1|//h2|//h3|//p') as $node) {
            $text = trim(preg_replace('/\s+/', ' ', $node->textContent));
            if ($text !== '') {
                $blocks[] = ['tag' => $node->nodeName, 'text' => $text];
            }
        }

        return ['title' => $title, 'blocks' => $blocks];
    }
}
