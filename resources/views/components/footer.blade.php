@php $c = config('site.company'); @endphp
<footer class="bg-viola text-avorio">
    <div class="mx-auto max-w-6xl px-6 py-10 grid gap-8 md:grid-cols-3 text-sm">
        <div>
            <img src="{{ asset('brand/chianucci-bianco.svg') }}" alt="{{ $c['name'] }}" class="h-12 mb-4">
            <p>{{ $c['name'] }}</p>
            <p>{{ $c['address'] }}</p>
        </div>
        <div>
            @foreach ($c['phones'] as $phone)
                <p><a href="tel:{{ preg_replace('/[^+0-9]/', '', $phone) }}">{{ $phone }}</a></p>
            @endforeach
            <p><a href="mailto:{{ $c['email'] }}">{{ $c['email'] }}</a></p>
            <p>PEC: {{ $c['pec'] }}</p>
        </div>
        <div>
            <p>P.IVA e C.F. {{ $c['vat'] }}</p>
            <p>REA n. {{ $c['rea'] }}</p>
            <p>Cap. Soc. {{ $c['capital'] }}</p>
            <p>{{ $c['register'] }}</p>
            <p class="mt-2"><a class="underline" href="{{ \App\Support\SiteUrls::page('privacy', app()->getLocale()) }}">Privacy Policy</a></p>
        </div>
    </div>
</footer>
