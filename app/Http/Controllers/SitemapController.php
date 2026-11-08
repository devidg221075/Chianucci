<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Product;
use App\Support\SiteUrls;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $entries = [];

        foreach (config('site.locales') as $locale) {
            $entries[] = [url("/$locale/".config("site.catalog_slug.$locale")), $this->catalogAlternates()];
        }

        $pages = Page::all();
        foreach ($pages as $page) {
            $key = $page->slug === 'home' ? 'home' : SiteUrls::keyForSlug($page->slug, $page->locale);
            $loc = $key ? SiteUrls::page($key, $page->locale) : url("/{$page->locale}/{$page->slug}");
            $alternates = [];
            if ($key) {
                foreach ($pages->where('slug', config("site.pages.$key.".($page->locale === 'it' ? 'en' : 'it'))) as $other) {
                    if ($other->locale !== $page->locale) {
                        $alternates[$other->locale] = SiteUrls::page($key, $other->locale);
                    }
                }
                $alternates[$page->locale] = $loc;
            }
            $entries[] = [$loc, $alternates];
        }

        foreach (Product::with('translations')->orderBy('id')->get() as $product) {
            $urls = $product->translations->mapWithKeys(fn ($t) => [$t->locale => url("/{$t->locale}/prodotto/{$t->slug}")])->all();
            foreach ($urls as $locale => $loc) {
                $entries[] = [$loc, $urls];
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";
        foreach ($entries as [$loc, $alternates]) {
            $xml .= '<url><loc>'.e($loc).'</loc>';
            if (count($alternates) > 1) {
                foreach ($alternates as $code => $href) {
                    $xml .= '<xhtml:link rel="alternate" hreflang="'.$code.'" href="'.e($href).'"/>';
                }
            }
            $xml .= "</url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function robots(): Response
    {
        return response("User-agent: *\nDisallow: /admin\nDisallow: /livewire\n\nSitemap: ".url('/sitemap.xml')."\n", 200, ['Content-Type' => 'text/plain']);
    }

    /** @return array<string,string> */
    private function catalogAlternates(): array
    {
        return collect(config('site.locales'))->mapWithKeys(fn ($l) => [$l => url("/$l/".config("site.catalog_slug.$l"))])->all();
    }
}
