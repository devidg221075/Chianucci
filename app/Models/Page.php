<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['slug', 'locale', 'title', 'html', 'css', 'meta_title', 'meta_description'];

    /** L'HTML e il CSS dell'editor libero si puliscono sempre al salvataggio. */
    protected function html(): Attribute
    {
        return Attribute::set(fn (?string $value) => HtmlSanitizer::clean($value ?? ''));
    }

    protected function css(): Attribute
    {
        return Attribute::set(fn (?string $value) => HtmlSanitizer::cleanCss($value ?? ''));
    }
}
