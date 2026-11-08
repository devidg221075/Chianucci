<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Support\Seo;
use App\Support\SiteUrls;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function show(string $locale, ?string $slug = null): View
    {
        $page = Page::where('locale', $locale)->where('slug', $slug ?? 'home')->firstOrFail();

        $canonical = $slug === null ? url("/$locale/") : url("/$locale/$slug");
        $seo = Seo::make(
            (string) $page->meta_description,
            $canonical,
            Seo::pageAlternates($slug, $locale),
            $slug === null ? Seo::organization() : null,
        );

        return view('pages.show', [
            'seo' => $seo,
            'page' => $page,
            'currentSlug' => $slug,
            'isContact' => $slug === config("site.pages.contatti.$locale"),
        ]);
    }
}
