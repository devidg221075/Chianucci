@extends('layouts.site')
@php
    use App\Support\SiteUrls;
    $locale = app()->getLocale();
    $t = $product->translated($locale);
    $fmt = fn ($v) => $v === null ? null : number_format((float) $v, 2, $locale === 'it' ? ',' : '.', '');
@endphp
@section('title', $t->name.' - CHIANUCCI MAURIZIO')

@section('content')
<section class="sezione"><div class="contenitore"><div class="griglia griglia-alto">
    <div class="col-meta">
        @foreach ($product->images as $image)
            <img src="{{ asset('storage/'.$image->path) }}" alt="{{ $t->name }}" class="foto-prodotto foto-prodotto-grande mb-4">
        @endforeach
    </div>
    <div class="col-meta">
        <a href="{{ SiteUrls::catalog($locale) }}" class="link-testo testo-piccolo">{{ __('site.catalog.back') }}</a>
        <h1 class="mt-2">{{ $t->name }}</h1>
        <p>{{ __('site.catalog.code') }} <strong>{{ $product->code }}</strong></p>
        @if ($t->description)<p>{{ $t->description }}</p>@endif
        <dl class="mt-6 grid grid-cols-2 gap-2 testo-piccolo">
            @if ($product->weight_g !== null)<dt>{{ __('site.catalog.weight') }}</dt><dd>{{ $fmt($product->weight_g) }}</dd>@endif
            @if ($product->length_cm !== null)<dt>{{ __('site.catalog.length') }}</dt><dd>{{ $fmt($product->length_cm) }}</dd>@endif
            @if ($product->link_mm !== null)<dt>{{ __('site.catalog.link') }}</dt><dd>{{ $fmt($product->link_mm) }}</dd>@endif
            @if (! empty($product->titles))<dt>{{ __('site.catalog.titles') }}</dt><dd>{{ implode(', ', $product->titles) }}</dd>@endif
        </dl>
        <div class="mt-8">
            <a href="{{ SiteUrls::page('area-clienti', $locale) }}" class="btn-primario">{{ __('site.catalog.request_quote') }}</a>
            <p class="mt-4 testo-piccolo">{{ __('site.catalog.quote_note') }}</p>
        </div>
    </div>
</div></div></section>
@endsection
