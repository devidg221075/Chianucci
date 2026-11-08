@php $locale = app()->getLocale(); $action = url("/$locale/".config("site.pages.contatti.$locale")); @endphp
<section class="px-6 pb-14"><div class="mx-auto max-w-5xl">
    @if (session('status'))
        <p class="mb-6 border border-oro bg-bianco p-4 text-viola" role="status">{{ session('status') }}</p>
    @endif
    <form method="post" action="{{ $action }}" class="grid gap-4 md:grid-cols-2 bg-bianco border border-oro/40 p-6">
        @csrf
        @foreach ([['nome', 'name', 'text', true], ['azienda', 'company', 'text', false], ['email', 'email', 'email', true], ['telefono', 'phone', 'tel', false]] as [$field, $label, $type, $required])
            <label class="text-sm">{{ __('site.contact.'.$label) }}{{ $required ? ' *' : '' }}
                <input type="{{ $type }}" name="{{ $field }}" value="{{ old($field) }}" @required($required) maxlength="160" class="block w-full border border-oro/60 p-2 mt-1">
                @error($field)<span class="text-red-700">{{ $message }}</span>@enderror
            </label>
        @endforeach
        <label class="text-sm md:col-span-2">{{ __('site.contact.message') }} *
            <textarea name="messaggio" rows="6" required maxlength="5000" class="block w-full border border-oro/60 p-2 mt-1">{{ old('messaggio') }}</textarea>
            @error('messaggio')<span class="text-red-700">{{ $message }}</span>@enderror
        </label>
        <div class="hidden" aria-hidden="true"><label>Sito web <input type="text" name="sito_web" tabindex="-1" autocomplete="off"></label></div>
        <label class="text-sm md:col-span-2 flex gap-2 items-start">
            <input type="checkbox" name="consenso" value="1" required class="mt-1">
            <span>{{ __('site.contact.consent') }}</span>
        </label>
        @error('consenso')<p class="md:col-span-2 text-red-700 text-sm">{{ $message }}</p>@enderror
        <div class="md:col-span-2"><button type="submit" class="btn-primario">{{ __('site.contact.send') }}</button></div>
    </form>
</div></section>
