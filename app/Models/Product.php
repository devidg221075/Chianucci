<?php

namespace App\Models;

use App\Models\Concerns\TranslatesWithFallback;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use TranslatesWithFallback;

    protected $fillable = ['code', 'category_id', 'weight_g', 'length_cm', 'link_mm', 'titles'];

    protected $casts = ['titles' => 'array'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort');
    }

    /** Prodotti visibili nella lingua: con traduzione in quella lingua o, in mancanza, in italiano. */
    public function scopeVisibleIn(Builder $query, string $locale): Builder
    {
        return $query
            ->whereHas('translations', fn ($q) => $q->whereIn('locale', array_unique([$locale, 'it'])))
            ->with('translations');
    }
}
