<?php

namespace App\Filament\Support;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/** Avatar con le iniziali, generato in locale: nessuna richiesta a servizi esterni. */
class InitialsAvatarProvider implements AvatarProvider
{
    public function get(Model|Authenticatable $record): string
    {
        $name = Filament::getNameForDefaultAvatar($record);
        $initial = mb_strtoupper(mb_substr(trim($name) ?: '?', 0, 1));
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" fill="#2A1B6D"/>'
            .'<text x="32" y="42" font-family="sans-serif" font-size="30" text-anchor="middle" fill="#FFFFFF">'.e($initial).'</text></svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
