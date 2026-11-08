@php $c = config('site.company'); @endphp
<footer class="sezione sezione-scura testo-piccolo">
    <div class="contenitore">
        <div class="griglia griglia-alto">
            <div class="col-terzo">
                <img src="{{ asset('brand/chianucci-bianco.svg') }}" alt="{{ $c['name'] }}" class="h-12 mb-4">
                <p>{{ $c['name'] }}</p>
                <p>{{ $c['address'] }}</p>
            </div>
            <div class="col-terzo">
                @foreach ($c['phones'] as $phone)
                    <p><a href="tel:{{ preg_replace('/[^+0-9]/', '', $phone) }}">{{ $phone }}</a></p>
                @endforeach
                <p><a href="mailto:{{ $c['email'] }}">{{ $c['email'] }}</a></p>
                <p>PEC: {{ $c['pec'] }}</p>
            </div>
            <div class="col-terzo">
                <p>P.IVA e C.F. {{ $c['vat'] }}</p>
                <p>REA n. {{ $c['rea'] }}</p>
                <p>Cap. Soc. {{ $c['capital'] }}</p>
                <p>{{ $c['register'] }}</p>
                <p class="mt-2"><a class="underline" href="{{ \App\Support\SiteUrls::page('privacy', app()->getLocale()) }}">Privacy Policy</a></p>
            </div>
        </div>
    </div>
</footer>
