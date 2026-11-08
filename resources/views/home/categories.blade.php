@php
    use App\Models\Category;
    use App\Support\SiteUrls;
    $locale = app()->getLocale();
    $categories = Category::with('translations')->whereNotNull('image_path')->orderBy('sort')->limit(4)->get();
@endphp
@if ($categories->isNotEmpty())
<section class="py-16 px-6 bg-bianco"><div class="mx-auto max-w-6xl">
    <p class="text-xs font-semibold uppercase tracking-widest text-oro-scuro">{{ $locale === 'it' ? 'I nostri prodotti' : 'Our products' }}</p>
    <h2 class="mt-2 text-3xl font-bold">{{ $locale === 'it' ? 'Catene per il mercato professionale' : 'Chains for the professional market' }}</h2>
    <div class="mt-8 grid gap-6 grid-cols-2 md:grid-cols-4">
        @foreach ($categories as $category)
            <a data-home-category href="{{ SiteUrls::catalog($locale, ['categoria' => $category->slug]) }}" class="group block border border-oro/30 hover:border-oro">
                <img src="{{ asset('storage/'.$category->image_path) }}" alt="{{ $category->translated($locale)?->name ?? $category->slug }}" loading="lazy" class="h-40 w-full object-contain bg-avorio">
                <span class="block p-4 font-semibold text-viola group-hover:text-oro-scuro">{{ $category->translated($locale)?->name ?? $category->slug }}</span>
            </a>
        @endforeach
    </div>
    <p class="mt-8"><a href="{{ SiteUrls::catalog($locale) }}" class="btn-secondario">{{ __('site.catalog_all') }}</a></p>
</div></section>
@endif
