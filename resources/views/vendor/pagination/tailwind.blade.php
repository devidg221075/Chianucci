@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="testo-piccolo">
        <div class="azioni" style="margin-top:0">
            @if ($paginator->onFirstPage())
                <span role="link" aria-disabled="true" class="btn-secondario" style="opacity:.5">{!! __('pagination.previous') !!}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn-secondario">{!! __('pagination.previous') !!}</a>
            @endif

            <span class="lead" aria-live="polite">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn-secondario">{!! __('pagination.next') !!}</a>
            @else
                <span role="link" aria-disabled="true" class="btn-secondario" style="opacity:.5">{!! __('pagination.next') !!}</span>
            @endif
        </div>
    </nav>
@endif
