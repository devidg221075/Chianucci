<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div wire:ignore
         x-data="pageEditor({ html: $wire.$entangle('{{ $getStatePath() }}'), css: $wire.$entangle('{{ $getCssStatePath() }}') })"
         class="flex gap-4">
        <div x-ref="blocks" class="w-56 shrink-0 overflow-y-auto" style="max-height: 75vh"></div>
        <div x-ref="canvas" class="flex-1"></div>
    </div>
</x-dynamic-component>
