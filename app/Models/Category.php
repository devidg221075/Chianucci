<?php

namespace App\Models;

use App\Models\Concerns\TranslatesWithFallback;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use TranslatesWithFallback;

    protected $fillable = ['slug', 'image_path', 'sort'];

    public function translations(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
