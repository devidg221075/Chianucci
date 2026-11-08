<?php

namespace App\Models\Concerns;

trait TranslatesWithFallback
{
    /** Traduzione nella lingua richiesta, con ripiego sull'italiano. */
    public function translated(string $locale): mixed
    {
        $translations = $this->translations;

        return $translations->firstWhere('locale', $locale)
            ?? $translations->firstWhere('locale', 'it');
    }
}
