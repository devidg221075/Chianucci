<?php

namespace App\Importer;

use DOMDocument;
use DOMXPath;

/** Legge una scheda prodotto del vecchio sito WordPress. Il prezzo non viene mai letto. */
class CatalogParser
{
    private const LABELS = [
        'article' => ['ARTICOLO', 'ARTICLE'],
        'weight_g' => ['PESO (GR)', 'WIDTH (GR)', 'WEIGHT (GR)'],
        'length_cm' => ['DIMENSIONI (CM)', 'DIMENSIONS (CM)'],
        'link_mm' => ['MISURE MAGLIE (MM)', 'MESH SIZES (MM)'],
        'metal' => ['TITOLO', 'TITLE'],
    ];

    /** @return array{name:?string,code:?string,slug:?string,weight_g:?float,length_cm:?float,link_mm:?float,titles:array,category_slug:?string,category_name:?string,images:array} */
    public function parseProduct(string $html, string $locale): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        $xp = new DOMXPath($dom);

        $name = $this->text($xp, "//h1[contains(@class,'product_title')]");
        $values = $this->shortDescription($xp);

        $canonical = $xp->query("//link[@rel='canonical']/@href")->item(0)?->nodeValue ?? '';
        preg_match('#/prodotto/([^/]+)/?#', $canonical, $slug);

        $categoryLink = $xp->query("//*[contains(@class,'posted_in')]//a")->item(0);
        preg_match('#categoria-prodotto/([^/]+)/?#', $categoryLink?->getAttribute('href') ?? '', $cat);

        $titles = [];
        $base = $values['metal'] ?? null;
        if ($base !== null && preg_match('/argento|silver/i', $base)) {
            $titles[] = 'AG';
        }
        foreach ($xp->query("//*[contains(@class,'ywapo_option_label')]") as $label) {
            if (preg_match('/\b(AU|AG)\s*(\d{3})\b/i', $label->textContent, $m)) {
                $titles[] = strtoupper($m[1]).' '.$m[2];
            }
        }

        $images = [];
        foreach ($xp->query("//meta[@property='og:image']/@content | //*[@data-large_image]/@data-large_image") as $attr) {
            $images[] = $attr->nodeValue;
        }

        return [
            'name' => $name,
            'code' => $values['article'] ?? $name,
            'slug' => $slug[1] ?? null,
            'weight_g' => $this->number($values['weight_g'] ?? null),
            'length_cm' => $this->number($values['length_cm'] ?? null),
            'link_mm' => $this->number($values['link_mm'] ?? null),
            'titles' => array_values(array_unique($titles)),
            'category_slug' => $cat[1] ?? null,
            'category_name' => $categoryLink ? trim($categoryLink->textContent) : null,
            'images' => array_values(array_unique(array_filter($images))),
        ];
    }

    /** @return array<string,string> */
    private function shortDescription(DOMXPath $xp): array
    {
        $node = $xp->query("//*[contains(@class,'woocommerce-product-details__short-description')]")->item(0);
        if (! $node) {
            return [];
        }

        $inner = '';
        foreach ($node->childNodes as $child) {
            $inner .= $node->ownerDocument->saveHTML($child);
        }

        $found = [];
        foreach (preg_split('#<br\s*/?>#i', $inner) as $line) {
            if (! preg_match('#<strong>(.*?)</strong>\s*:\s*(.*)#is', $line, $m)) {
                continue;
            }
            $label = mb_strtoupper(trim(strip_tags($m[1])));
            foreach (self::LABELS as $field => $names) {
                if (in_array($label, $names, true)) {
                    $found[$field] = trim(html_entity_decode(strip_tags($m[2])));
                }
            }
        }

        return $found;
    }

    private function text(DOMXPath $xp, string $query): ?string
    {
        $value = $xp->query($query)->item(0)?->textContent;

        return $value === null ? null : trim($value);
    }

    private function number(?string $value): ?float
    {
        if ($value === null || ! preg_match('/-?\d+(?:[.,]\d+)?/', $value, $m)) {
            return null;
        }

        return (float) str_replace(',', '.', $m[0]);
    }
}
