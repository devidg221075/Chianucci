<?php

namespace App\Importer;

use Illuminate\Support\Facades\Http;

/** Legge il sito pubblico con ritentativi: le connessioni cadono a volte. */
class SiteClient
{
    public function __construct(
        public readonly string $base = 'https://www.chianucci.it',
        private int $retries = 4,
        private int $sleepMs = 1500,
    ) {}

    public function get(string $url): string
    {
        return Http::timeout(40)->retry($this->retries, $this->sleepMs)->get($url)->throw()->body();
    }
}
