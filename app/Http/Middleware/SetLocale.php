<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        if (in_array($locale, config('site.locales'), true)) {
            app()->setLocale($locale);
            URL::defaults(['locale' => $locale]);
        }

        return $next($request);
    }
}
