@php
    use App\Models\Category;
    use App\Support\SiteUrls;
    $locale = app()->getLocale();
    $categories = Category::with('translations')->whereNotNull('image_path')->orderBy('sort')->limit(4)->get();
@endphp
@if ($categories->isNotEmpty())
<section class="sezione sezione-chiara"><div class="contenitore"><div class="griglia griglia-alto">
    <div class="col-intero">
        <p class="occhiello">{{ $locale === 'it' ? 'I nostri prodotti' : 'Our products' }}</p>
        <h2>{{ $locale === 'it' ? 'Catene per il mercato professionale' : 'Chains for the professional market' }}</h2>
    </div>
    @foreach ($categories as $category)
        <a data-home-category href="{{ SiteUrls::catalog($locale, ['categoria' => $category->slug]) }}" class="scheda col-quarto">
            <img src="{{ asset('storage/'.$category->image_path) }}" alt="{{ $category->translated($locale)?->name ?? $category->slug }}" loading="lazy" class="foto-prodotto">
            <span class="scheda-testo">{{ $category->translated($locale)?->name ?? $category->slug }}</span>
        </a>
    @endforeach
    <p class="col-intero"><a href="{{ SiteUrls::catalog($locale) }}" class="btn-secondario">{{ __('site.catalog_all') }}</a></p>
</div></div></section>
@endif
