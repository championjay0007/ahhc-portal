@if ($paginator->hasPages())
    <nav aria-label="Pagination" class="d-flex justify-content-between">
        <div>
            @if ($paginator->onFirstPage())
                <span class="page-link disabled"></span>
            @else
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"></a>
            @endif
        </div>
        <div>
            @if ($paginator->hasMorePages())
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next"></a>
            @else
                <span class="page-link disabled"></span>
            @endif
        </div>
    </nav>
@endif
