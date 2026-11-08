@php
    use App\Support\SiteUrls;
    $locale = app()->getLocale();
    $currentSlug = $currentSlug ?? null;
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CHIANUCCI MAURIZIO')</title>
    @isset($seo)
        @if (! empty($seo['description']))<meta name="description" content="{{ $seo['description'] }}">@endif
        <link rel="canonical" href="{{ $seo['canonical'] }}">
        @foreach ($seo['alternates'] as $code => $href)
            <link rel="alternate" hreflang="{{ $code }}" href="{{ $href }}">
        @endforeach
        @if (! empty($seo['alternates']))
            <link rel="alternate" hreflang="x-default" href="{{ $seo['alternates']['it'] ?? reset($seo['alternates']) }}">
        @endif
        @if (! empty($seo['jsonld']))
            <script type="application/ld+json">{!! json_encode($seo['jsonld'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
        @endif
    @endisset
    @stack('head')
    @vite(['resources/css/site.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none !important}</style>
</head>
<body class="min-h-screen flex flex-col">
    <header class="sticky top-0 z-50 bg-bianco border-b border-oro/40" x-data="{ menu: false }">
        <div class="mx-auto max-w-7xl flex items-center justify-between gap-4 px-6 py-3">
            <a href="{{ url("/$locale/") }}"><img src="{{ asset('brand/chianucci.svg') }}" alt="CHIANUCCI MAURIZIO" class="h-12"></a>
            <button class="md:hidden text-viola" @click="menu = !menu" :aria-expanded="menu" aria-label="Menu">☰</button>
            <nav class="hidden md:flex items-center gap-6" :class="{ '!flex flex-col absolute left-0 top-full w-full bg-bianco p-6 items-start': menu }">
                @foreach (config('site.menu') as $item)
                    @if ($item === 'catalogo')
                        <x-mega-menu :locale="$locale" />
                    @else
                        <a href="{{ SiteUrls::page($item, $locale) }}" class="nav-voce text-sm font-semibold uppercase tracking-wide hover:text-oro">{{ __('site.menu.'.$item) }}</a>
                    @endif
                @endforeach
                <a href="{{ SiteUrls::page('area-clienti', $locale) }}" class="btn-primario text-sm">{{ __('site.area_clienti') }}</a>
                <x-language-switch :locale="$locale" :slug="$currentSlug" />
            </nav>
        </div>
    </header>
    <main class="flex-1">@yield('content')</main>
    <x-footer />
    <div data-cookie-banner x-data="{ show: false }" x-init="try { show = ! localStorage.getItem('cookie-notice') } catch (e) { show = true }" x-show="show" x-cloak
         class="fixed bottom-0 inset-x-0 z-50 bg-viola text-avorio text-sm p-4 flex flex-wrap items-center justify-center gap-4" role="region" aria-label="Cookie">
        <p>{{ __('site.cookie.text') }} <a class="underline" href="{{ SiteUrls::page('privacy', $locale) }}">{{ __('site.cookie.link') }}</a></p>
        <button type="button" class="btn-secondario !text-avorio !py-1" @click="try { localStorage.setItem('cookie-notice', '1') } catch (e) {} show = false">{{ __('site.cookie.ok') }}</button>
    </div>
</body>
</html>
