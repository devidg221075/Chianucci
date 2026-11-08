<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div wire:ignore
         x-data="pageEditor({ html: $wire.$entangle('{{ $getStatePath() }}'), css: $wire.$entangle('{{ $getCssStatePath() }}'), siteCss: @js(\Illuminate\Support\Facades\Vite::asset('resources/css/site.css')) })"
         style="display:flex;gap:1rem;align-items:flex-start">
        <div x-ref="blocks" style="width:14rem;flex-shrink:0;max-height:75vh;overflow-y:auto"></div>
        <div x-ref="canvas" style="flex:1;min-width:0"></div>
    </div>
</x-dynamic-component>
