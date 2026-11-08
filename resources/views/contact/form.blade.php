@php $locale = app()->getLocale(); $action = url("/$locale/".config("site.pages.contatti.$locale")); @endphp
<section class="sezione"><div class="contenitore">
    @if (session('status'))
        <p class="mb-6 border border-oro bg-bianco p-4 text-viola" role="status">{{ session('status') }}</p>
    @endif
    @if (session('error'))
        <p class="mb-6 border border-red-700 bg-bianco p-4 text-red-700" role="alert">{{ session('error') }}</p>
    @endif
    <form method="post" action="{{ $action }}" class="griglia griglia-alto sezione-chiara p-6 border border-oro/40">
        @csrf
        @foreach ([['nome', 'name', 'text', true], ['azienda', 'company', 'text', false], ['email', 'email', 'email', true], ['telefono', 'phone', 'tel', false]] as [$field, $label, $type, $required])
            <label class="etichetta col-meta">{{ __('site.contact.'.$label) }}{{ $required ? ' *' : '' }}
                <input type="{{ $type }}" name="{{ $field }}" value="{{ old($field) }}" @required($required) maxlength="160" class="campo">
                @error($field)<span class="text-red-700">{{ $message }}</span>@enderror
            </label>
        @endforeach
        <label class="etichetta col-intero">{{ __('site.contact.message') }} *
            <textarea name="messaggio" rows="6" required maxlength="5000" class="campo">{{ old('messaggio') }}</textarea>
            @error('messaggio')<span class="text-red-700">{{ $message }}</span>@enderror
        </label>
        <div class="hidden" aria-hidden="true"><label>Sito web <input type="text" name="sito_web" tabindex="-1" autocomplete="off"></label></div>
        <label class="etichetta col-intero flex gap-2 items-start">
            <input type="checkbox" name="consenso" value="1" required>
            <span>{{ __('site.contact.consent') }}</span>
        </label>
        @error('consenso')<p class="col-intero text-red-700 testo-piccolo">{{ $message }}</p>@enderror
        <div class="col-intero"><button type="submit" class="btn-primario">{{ __('site.contact.send') }}</button></div>
    </form>
</div></section>
