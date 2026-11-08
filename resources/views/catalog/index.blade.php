@extends('layouts.site')
@php
    use App\Support\SiteUrls;
    $locale = app()->getLocale();
    $slug = config("site.catalog_slug.$locale");
@endphp
@section('title', __('site.catalog.title').' - CHIANUCCI MAURIZIO')

@section('content')
<section class="mx-auto max-w-7xl px-6 py-10">
    <h1 class="text-3xl font-bold mb-6">{{ __('site.catalog.title') }}</h1>

    <form method="get" action="{{ url("/$locale/$slug") }}" class="grid gap-4 md:grid-cols-4 bg-bianco border border-oro/40 p-4 mb-8 text-sm">
        <label class="md:col-span-2">{{ __('site.catalog.search') }}
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="block w-full border border-oro/60 p-2 mt-1">
        </label>
        <label>{{ __('site.catalog.category') }}
            <select name="categoria" class="block w-full border border-oro/60 p-2 mt-1">
                <option value="">{{ __('site.catalog.all_categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}" @selected(($filters['categoria'] ?? null) === $category->slug)>{{ $category->translated($locale)?->name ?? $category->slug }}</option>
                @endforeach
            </select>
        </label>
        <label>{{ __('site.catalog.metal') }}
            <select name="titolo" class="block w-full border border-oro/60 p-2 mt-1">
                <option value="">{{ __('site.catalog.all_metals') }}</option>
                @foreach ($metals as $metal)
                    <option value="{{ $metal }}" @selected(($filters['titolo'] ?? null) === $metal)>{{ $metal }}</option>
                @endforeach
            </select>
        </label>
        @foreach (['maglia' => 'link', 'lunghezza' => 'length', 'peso' => 'weight'] as $param => $label)
            <fieldset class="md:col-span-1"><legend>{{ __('site.catalog.'.$label) }}</legend>
                <div class="flex gap-2 mt-1">
                    <input type="text" inputmode="decimal" name="{{ $param }}_min" placeholder="{{ __('site.catalog.min') }}" value="{{ is_string($filters[$param.'_min'] ?? null) ? $filters[$param.'_min'] : '' }}" class="w-1/2 border border-oro/60 p-2">
                    <input type="text" inputmode="decimal" name="{{ $param }}_max" placeholder="{{ __('site.catalog.max') }}" value="{{ is_string($filters[$param.'_max'] ?? null) ? $filters[$param.'_max'] : '' }}" class="w-1/2 border border-oro/60 p-2">
                </div>
            </fieldset>
        @endforeach
        <div class="flex items-end gap-3">
            <button class="btn-primario" type="submit">{{ __('site.catalog.apply') }}</button>
            <a href="{{ url("/$locale/$slug") }}" class="underline text-viola">{{ __('site.catalog.reset') }}</a>
        </div>
    </form>

    @if ($products->isEmpty())
        <p class="py-12 text-center">{{ __('site.catalog.empty') }}</p>
    @else
        <div class="grid gap-6 grid-cols-2 md:grid-cols-4">
            @foreach ($products as $product)
                @php $t = $product->translated($locale); $image = $product->images->first(); @endphp
                <a href="{{ url("/$locale/prodotto/".$t->slug) }}" class="block bg-bianco border border-oro/30 hover:border-oro">
                    @if ($image)
                        <img src="{{ asset('storage/'.$image->path) }}" alt="{{ $t->name }}" loading="lazy" class="w-full aspect-square object-cover">
                    @endif
                    <div class="p-3">
                        <h2 class="text-base font-semibold">{{ $t->name }}</h2>
                        <p class="text-xs">{{ __('site.catalog.code') }} {{ $product->code }}</p>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $products->links() }}</div>
    @endif
</section>
@endsection
