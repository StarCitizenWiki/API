@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-2">

        @if ($paginator->onFirstPage())
            <span class="btn btn-outline btn-sm btn-disabled" aria-disabled="true">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-outline btn-sm">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-outline btn-sm">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="btn btn-outline btn-sm btn-disabled" aria-disabled="true">
                {!! __('pagination.next') !!}
            </span>
        @endif

    </nav>
@endif
