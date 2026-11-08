@extends('layouts.site')
@php
    use App\Support\SiteUrls;
    $locale = app()->getLocale();
    $slug = config("site.catalog_slug.$locale");
@endphp
@section('title', __('site.catalog.title').' - CHIANUCCI MAURIZIO')

@section('content')
<section class="sezione"><div class="contenitore">
    <h1>{{ __('site.catalog.title') }}</h1>

    <form method="get" action="{{ url("/$locale/$slug") }}" class="griglia griglia-alto sezione-chiara border border-oro/40 p-6 mt-6 mb-8 testo-piccolo">
        <label class="etichetta col-meta">{{ __('site.catalog.search') }}
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="campo">
        </label>
        <label class="etichetta col-quarto-testo">{{ __('site.catalog.category') }}
            <select name="categoria" class="campo">
                <option value="">{{ __('site.catalog.all_categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}" @selected(($filters['categoria'] ?? null) === $category->slug)>{{ $category->translated($locale)?->name ?? $category->slug }}</option>
                @endforeach
            </select>
        </label>
        <label class="etichetta col-quarto-testo">{{ __('site.catalog.metal') }}
            <select name="titolo" class="campo">
                <option value="">{{ __('site.catalog.all_metals') }}</option>
                @foreach ($metals as $metal)
                    <option value="{{ $metal }}" @selected(($filters['titolo'] ?? null) === $metal)>{{ $metal }}</option>
                @endforeach
            </select>
        </label>
        @foreach (['maglia' => 'link', 'lunghezza' => 'length', 'peso' => 'weight'] as $param => $label)
            <fieldset class="col-quarto-testo"><legend class="etichetta">{{ __('site.catalog.'.$label) }}</legend>
                <div class="flex gap-2 mt-2">
                    <input type="text" inputmode="decimal" name="{{ $param }}_min" placeholder="{{ __('site.catalog.min') }}" value="{{ is_string($filters[$param.'_min'] ?? null) ? $filters[$param.'_min'] : '' }}" class="campo">
                    <input type="text" inputmode="decimal" name="{{ $param }}_max" placeholder="{{ __('site.catalog.max') }}" value="{{ is_string($filters[$param.'_max'] ?? null) ? $filters[$param.'_max'] : '' }}" class="campo">
                </div>
            </fieldset>
        @endforeach
        <div class="col-quarto-testo azioni" style="margin-top:0;align-items:center">
            <button class="btn-primario" type="submit">{{ __('site.catalog.apply') }}</button>
            <a href="{{ url("/$locale/$slug") }}" class="link-testo">{{ __('site.catalog.reset') }}</a>
        </div>
    </form>

    @if ($products->isEmpty())
        <p class="lead text-center">{{ __('site.catalog.empty') }}</p>
    @else
        <div class="griglia griglia-alto">
            @foreach ($products as $product)
                @php $t = $product->translated($locale); $image = $product->images->first(); @endphp
                <a href="{{ url("/$locale/prodotto/".$t->slug) }}" class="scheda col-quarto">
                    @if ($image)
                        <img src="{{ asset('storage/'.$image->path) }}" alt="{{ $t->name }}" loading="lazy" class="foto-prodotto">
                    @endif
                    <span class="scheda-testo">{{ $t->name }}<span class="block testo-piccolo font-normal">{{ __('site.catalog.code') }} {{ $product->code }}</span></span>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $products->links() }}</div>
    @endif
</div></section>
@endsection
