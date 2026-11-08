@php use App\Support\SiteUrls; @endphp
<div x-data="{ open: false }" @mouseenter="open = window.innerWidth >= 768" @mouseleave="open = false" @keydown.escape="open = false">
    <a href="{{ SiteUrls::catalog($locale) }}" class="nav-voce text-sm font-semibold uppercase tracking-wide hover:text-oro"
       @click="if (window.innerWidth < 768) { $event.preventDefault(); open = !open }"
       :aria-expanded="open">{{ __('site.menu.catalogo') }}</a>
    <div x-show="open" x-cloak x-transition
         class="w-full md:absolute md:inset-x-0 md:top-full z-40 mt-2 md:mt-0 bg-bianco border border-oro/40 shadow-xl p-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @foreach ($categories as $category)
                @php $t = $category->translated($locale); @endphp
                <a data-mega-tile href="{{ SiteUrls::catalog($locale, ['categoria' => $category->slug]) }}"
                   class="flex md:flex-col items-center md:items-stretch gap-3 group">
                    @if ($category->image_path)
                        <img src="{{ asset('storage/'.$category->image_path) }}" alt="" loading="lazy"
                             class="h-12 w-12 md:h-28 md:w-full object-contain bg-avorio">
                    @endif
                    <span class="text-sm font-semibold text-viola group-hover:text-oro">{{ $t?->name ?? $category->slug }}</span>
                </a>
            @endforeach
        </div>
        <a href="{{ SiteUrls::catalog($locale) }}" class="btn-secondario mt-4 text-sm">{{ __('site.catalog_all') }}</a>
    </div>
</div>
