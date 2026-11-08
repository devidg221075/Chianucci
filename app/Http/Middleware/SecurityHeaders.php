<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Il sito pubblico non carica nulla da terzi. 'unsafe-eval' serve ad Alpine.js (menu); 'unsafe-inline' negli stili
     * serve al CSS prodotto dall'editor delle pagine. Il pannello /admin (Filament/Livewire) non riceve la CSP.
     */
    private const CSP = "default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-eval'; font-src 'self' data:; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'";

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        $first = explode('/', trim($request->getPathInfo(), '/'))[0];
        if (! in_array($first, ['admin', 'livewire'], true)) {
            $response->headers->set('Content-Security-Policy', self::CSP);
        }

        return $response;
    }
}
