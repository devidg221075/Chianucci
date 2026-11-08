@php use App\Support\SiteUrls; @endphp
<div x-data="{ open: false }" @mouseenter="open = window.innerWidth >= 768" @mouseleave="open = false" @keydown.escape="open = false">
    <a href="{{ SiteUrls::catalog($locale) }}" class="nav-voce hover:text-oro-scuro"
       @click="if (window.innerWidth < 768) { $event.preventDefault(); open = !open }"
       :aria-expanded="open">{{ __('site.menu.catalogo') }}</a>
    <div x-show="open" x-cloak x-transition
         class="w-full md:absolute md:inset-x-0 md:top-full z-40 mt-2 md:mt-0 bg-bianco border border-oro/40 shadow-xl py-6">
        <div class="contenitore">
            <div class="griglia griglia-alto">
                @foreach ($categories as $category)
                    @php $t = $category->translated($locale); @endphp
                    <a data-mega-tile href="{{ SiteUrls::catalog($locale, ['categoria' => $category->slug]) }}" class="col-sesto group">
                        @if ($category->image_path)
                            <img src="{{ asset('storage/'.$category->image_path) }}" alt="" loading="lazy" class="foto-prodotto">
                        @endif
                        <span class="block mt-2 testo-piccolo font-semibold text-viola group-hover:text-oro-scuro">{{ $t?->name ?? $category->slug }}</span>
                    </a>
                @endforeach
            </div>
            <p class="mt-6"><a href="{{ SiteUrls::catalog($locale) }}" class="btn-secondario">{{ __('site.catalog_all') }}</a></p>
        </div>
    </div>
</div>
