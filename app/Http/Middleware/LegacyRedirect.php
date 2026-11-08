<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Reindirizza (301, una sola volta) i vecchi indirizzi del sito WordPress. */
class LegacyRedirect
{
    private const SKIP = ['admin', 'livewire', 'build', 'storage', 'brand', 'up', 'sitemap.xml', 'robots.txt'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $next($request);
        }

        $first = explode('/', trim($request->getPathInfo(), '/'))[0];
        if (in_array(strtolower($first), self::SKIP, true)) {
            return $next($request);
        }

        $target = Redirect::map()[Redirect::normalize($request->getPathInfo())] ?? null;

        return $target ? redirect($target, 301) : $next($request);
    }
}
