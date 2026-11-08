<?php

namespace App\Support;

use App\Models\Page;

class SiteUrls
{
    public static function page(string $key, string $locale): string
    {
        $slug = config("site.pages.$key.$locale");

        return $slug === 'home' ? url("/$locale/") : url("/$locale/$slug");
    }

    public static function catalog(string $locale, array $query = []): string
    {
        $url = url("/$locale/".config("site.catalog_slug.$locale"));

        return $query ? $url.'?'.http_build_query($query) : $url;
    }

    public static function keyForSlug(string $slug, string $locale): ?string
    {
        foreach (config('site.pages') as $key => $slugs) {
            if (($slugs[$locale] ?? null) === $slug) {
                return $key;
            }
        }

        return null;
    }

    /** Indirizzo della stessa pagina nell'altra lingua; se non esiste, la home di quella lingua. */
    public static function switchTo(string $currentLocale, string $targetLocale, ?string $currentSlug): string
    {
        $key = $currentSlug === null ? 'home' : self::keyForSlug($currentSlug, $currentLocale);

        if ($key !== null) {
            $slug = config("site.pages.$key.$targetLocale");
            if (Page::where('slug', $slug)->where('locale', $targetLocale)->exists()) {
                return self::page($key, $targetLocale);
            }
        }

        return url("/$targetLocale/");
    }
}
