@if ($paginator->hasPages())
    <div class="jl_pagination">
        @if ($paginator->onFirstPage())
            <span class="jl_page_prev jl_disabled">&laquo;</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="jl_page_prev">&laquo;</a>
        @endif

        @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
            @if ($page == $paginator->currentPage())
                <span class="jl_page_link jl_current">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="jl_page_link">{{ $page }}</a>
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="jl_page_next">&raquo;</a>
        @else
            <span class="jl_page_next jl_disabled">&raquo;</span>
        @endif
    </div>
@endif
