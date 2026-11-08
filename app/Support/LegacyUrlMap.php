<?php

namespace App\Support;

/** Traduce un vecchio indirizzo del sito WordPress nel nuovo; null se non serve alcun reindirizzamento. */
class LegacyUrlMap
{
    private const PROCESSES = [
        'it' => ['fusione' => 'fusione', 'preparazione' => 'preparazione', 'saldatura' => 'saldatura', 'pulitura-e-lucidatura' => 'pulitura-e-lucidatura', 'galvanica' => 'galvanica'],
        'en' => ['fusion' => 'fusion', 'preparation' => 'preparation', 'welding' => 'welding', 'cleaning-and-polishing' => 'cleaning-and-polishing', 'galvanic' => 'galvanic'],
    ];

    private const SINGLE = [
        'it' => [
            'azienda-chi-siamo-maurizio-chianucci' => 'azienda', 'lavorazioni' => 'manifattura', 'team-members' => 'azienda',
            'negozio' => 'catalogo', 'catalogo/catalogo-pdf' => 'catalogo', 'rimborso_reso' => 'contatti',
            'cart' => 'area-clienti', 'carrello' => 'area-clienti', 'checkout' => 'area-clienti', 'pagamento' => 'area-clienti',
            'my-account' => 'area-clienti', 'my-account-2' => 'area-clienti', 'mio-account' => 'area-clienti',
            'registration' => 'area-clienti', 'frontend-manager' => 'area-clienti', 'orders' => 'area-clienti',
        ],
        'en' => [
            'company-about-us-maurizio-chianucci' => 'company', 'processings' => 'manufacturing',
            'catalogue/pdf-catalogue' => 'catalogue',
            'cart-2' => 'customer-area', 'checkout-2' => 'customer-area', 'my-account-3' => 'customer-area',
            'user-registration' => 'customer-area', 'orders' => 'customer-area',
        ],
    ];

    public function map(string $path): ?string
    {
        $path = trim(strtolower(parse_url($path, PHP_URL_PATH) ?: ''), '/');
        if (! preg_match('#^(it|en)/(.+)$#', $path, $m)) {
            return null;
        }
        [, $locale, $rest] = $m;

        if (preg_match('#^(?:lavorazioni|processings)/([^/]+)$#', $rest, $p) && isset(self::PROCESSES[$locale][$p[1]])) {
            return "/$locale/".self::PROCESSES[$locale][$p[1]];
        }

        if (isset(self::SINGLE[$locale][$rest])) {
            $target = self::SINGLE[$locale][$rest];
            $slug = in_array($target, ['catalogo', 'catalogue'], true) ? config("site.catalog_slug.$locale") : $target;

            return "/$locale/$slug";
        }

        return null;
    }
}
