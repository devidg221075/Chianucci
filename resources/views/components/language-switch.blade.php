@php use App\Support\SiteUrls; @endphp
<nav aria-label="{{ __('site.language') }}" class="flex gap-2 text-sm font-semibold">
    @foreach (config('site.locales') as $code)
        @if ($code === $locale)
            <span class="text-oro" aria-current="true">{{ strtoupper($code) }}</span>
        @else
            <a href="{{ SiteUrls::switchTo($locale, $code, $slug) }}" hreflang="{{ $code }}" class="text-viola hover:text-oro">{{ strtoupper($code) }}</a>
        @endif
    @endforeach
</nav>
