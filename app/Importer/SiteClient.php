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

    /**
     * Scarica più indirizzi in parallelo; quelli che falliscono si ripetono uno a uno con i ritentativi.
     *
     * @param  string[]  $urls
     * @return array<string,string|\Throwable> url => corpo oppure l'errore finale
     */
    public function getMany(array $urls): array
    {
        $urls = array_values($urls);
        $responses = Http::pool(fn ($pool) => array_map(fn ($u) => $pool->timeout(40)->get($u), $urls));

        $result = [];
        foreach ($urls as $i => $url) {
            $response = $responses[$i] ?? null;
            if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                $result[$url] = $response->body();

                continue;
            }
            try {
                $result[$url] = $this->get($url);
            } catch (\Throwable $e) {
                $result[$url] = $e;
            }
        }

        return $result;
    }
}
