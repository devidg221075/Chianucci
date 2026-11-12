<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Support\Seo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    private const METALS = ['AG', 'AU 750', 'AU 585', 'AU 375'];

    public function index(Request $request, string $locale): View
    {
        $q = $this->text($request, 'q');
        $categoria = $this->text($request, 'categoria');
        $titolo = $this->text($request, 'titolo');

        $query = Product::visibleIn($locale)->with(['category.translations', 'images']);

        if ($categoria !== null) {
            $query->whereHas('category', fn ($c) => $c->where('slug', $categoria));
        }
        if ($titolo !== null && in_array($titolo, self::METALS, true)) {
            $query->whereJsonContains('titles', $titolo);
        }
        foreach (['link_mm' => 'maglia', 'length_cm' => 'lunghezza', 'weight_g' => 'peso'] as $column => $param) {
            if (($min = $this->number($request, $param.'_min')) !== null) {
                $query->where($column, '>=', $min);
            }
            if (($max = $this->number($request, $param.'_max')) !== null) {
                $query->where($column, '<=', $max);
            }
        }
        if ($q !== null) {
            $like = '%'.addcslashes($q, '%_\\').'%';
            $query->where(function ($w) use ($like, $locale) {
                $w->where('code', 'like', $like)->orWhereHas(
                    'translations',
                    fn ($t) => $t->whereIn('locale', array_unique([$locale, 'it']))->where('name', 'like', $like)
                );
            });
        }

        $seo = Seo::make(
            __('site.catalog.seo_description'),
            url("/$locale/".config("site.catalog_slug.$locale")),
            // Cambiando lingua si resta nella stessa categoria.
            collect(config('site.locales'))->mapWithKeys(fn ($l) => [
                $l => url("/$l/".config("site.catalog_slug.$l")).($categoria !== null ? '?'.http_build_query(['categoria' => $categoria]) : ''),
            ])->all(),
        );

        return view('catalog.index', [
            'seo' => $seo,
            'products' => $query->orderBy('code')->paginate(24)->withQueryString(),
            'categories' => Category::with('translations')->orderBy('sort')->get(),
            'metals' => self::METALS,
            'filters' => array_filter(
                $request->only(['q', 'categoria', 'titolo', 'maglia_min', 'maglia_max', 'lunghezza_min', 'lunghezza_max', 'peso_min', 'peso_max']),
                'is_string'
            ),
        ]);
    }

    public function show(string $locale, string $slug): View
    {
        $translation = ProductTranslation::where('slug', $slug)->where('locale', $locale)->first()
            ?? ProductTranslation::where('slug', $slug)->where('locale', 'it')->firstOrFail();

        $product = Product::with(['translations', 'images', 'category.translations'])->findOrFail($translation->product_id);

        $used = $product->translations->firstWhere('locale', $locale) ?? $product->translations->firstWhere('locale', 'it');
        $urls = $product->translations->mapWithKeys(fn ($t) => [$t->locale => url("/{$t->locale}/prodotto/{$t->slug}")])->all();
        $canonical = $urls[$used->locale];
        $seo = Seo::make(
            $used->description ?: __('site.catalog.product_description', ['name' => $used->name, 'code' => $product->code]),
            $canonical,
            $urls,
            Seo::product($product, $used->name, $used->description, $canonical),
        );

        return view('catalog.show', ['product' => $product, 'seo' => $seo]);
    }

    private function text(Request $request, string $key): ?string
    {
        $value = $request->query($key);

        if (! is_string($value) || trim($value) === '' || preg_match('/[\x00-\x1F]/', $value)) {
            return null;
        }

        return mb_substr(trim($value), 0, 100);
    }

    private function number(Request $request, string $key): ?float
    {
        $value = $request->query($key);

        return is_string($value) && is_numeric($value) ? (float) $value : null;
    }
}
