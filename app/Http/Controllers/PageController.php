<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function show(string $locale, ?string $slug = null): View
    {
        $page = Page::where('locale', $locale)->where('slug', $slug ?? 'home')->firstOrFail();

        return view('pages.show', [
            'page' => $page,
            'currentSlug' => $slug,
            'isContact' => $slug === config("site.pages.contatti.$locale"),
        ]);
    }
}
