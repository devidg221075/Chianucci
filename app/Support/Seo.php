<?php

namespace App\Support;

use App\Models\Page;
use App\Models\Product;

/** Dati SEO di una pagina: descrizione, canonical, alternate hreflang, JSON-LD. */
class Seo
{
    /** @param array<string,string> $alternates lingua => indirizzo assoluto */
    public static function make(string $description, string $canonical, array $alternates = [], ?array $jsonld = null): array
    {
        return compact('description', 'canonical', 'alternates', 'jsonld');
    }

    /** @return array<string,string> */
    public static function pageAlternates(?string $slug, string $locale): array
    {
        $key = $slug === null ? 'home' : SiteUrls::keyForSlug($slug, $locale);
        if ($key === null) {
            return [$locale => url("/$locale/$slug")];
        }

        $alternates = [];
        foreach (config('site.locales') as $code) {
            $target = config("site.pages.$key.$code");
            if (Page::where('slug', $target)->where('locale', $code)->exists()) {
                $alternates[$code] = SiteUrls::page($key, $code);
            }
        }

        return $alternates;
    }

    public static function organization(): array
    {
        $c = config('site.company');

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $c['name'],
            'url' => url('/it/'),
            'logo' => asset('brand/chianucci.svg'),
            'email' => $c['email'],
            'telephone' => $c['phones'][0],
            'vatID' => 'IT'.$c['vat'],
            'foundingDate' => '1973',
            'address' => [
                '@type' => 'PostalAddress', 'streetAddress' => 'Via Baracca, 7', 'postalCode' => '52100',
                'addressLocality' => 'Arezzo', 'addressCountry' => 'IT',
            ],
        ];
    }

    /** Prodotto per Google: nessuna offerta e nessun prezzo. */
    public static function product(Product $product, string $name, ?string $description, string $url): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $name,
            'sku' => $product->code,
            'url' => $url,
            'brand' => ['@type' => 'Brand', 'name' => 'CHIANUCCI MAURIZIO'],
            'image' => $product->images->map(fn ($i) => asset('storage/'.$i->path))->all(),
        ];
        if ($description) {
            $data['description'] = $description;
        }

        return $data;
    }
}
