<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Redirect extends Model
{
    public const CACHE_KEY = 'redirects.map';

    protected $fillable = ['from_path', 'to_path'];

    /** Minuscolo, senza query né slash finale ("/" per la radice). */
    public static function normalize(string $path): string
    {
        $path = strtolower(parse_url($path, PHP_URL_PATH) ?: '/');
        $path = '/'.trim($path, '/');

        return $path;
    }

    /** @return array<string,string> from_path => to_path */
    public static function map(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => static::pluck('to_path', 'from_path')->all());
    }

    protected static function booted(): void
    {
        static::saving(function (Redirect $r) {
            $r->from_path = self::normalize($r->from_path);
            $query = parse_url($r->to_path, PHP_URL_QUERY);
            $r->to_path = self::normalize($r->to_path).($query ? '?'.$query : '');

            if (self::normalize($r->to_path) === $r->from_path) {
                throw new \InvalidArgumentException("Il reindirizzamento {$r->from_path} punta a se stesso");
            }
        });

        static::saved(function (Redirect $r) {
            $target = self::normalize($r->to_path);

            // Se seguendo la catena dalla destinazione si torna al punto di partenza c'è un anello:
            // vince il reindirizzamento appena salvato e si toglie quello vecchio che chiude l'anello.
            $path = $target;
            $seen = [];
            while (! isset($seen[$path]) && ($row = static::where('from_path', $path)->where('id', '!=', $r->id)->first())) {
                $seen[$path] = true;
                $path = self::normalize($row->to_path);
                if ($path === $r->from_path) {
                    $row->deleteQuietly();
                    break;
                }
            }

            // Niente catene: A->B e B->C diventano A->C e B->C.
            static::where('to_path', $r->from_path)->where('id', '!=', $r->id)->update(['to_path' => $r->to_path]);
            $next = static::where('from_path', $target)->first();
            if ($next && $next->id !== $r->id && self::normalize($next->to_path) !== $r->from_path) {
                $r->updateQuietly(['to_path' => $next->to_path]);
            }
            Cache::forget(self::CACHE_KEY);
        });

        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }
}
