@if ($paginator->hasPages())
<div class="shop-pagination">
    @if ($paginator->onFirstPage())
        <span class="page-disabled">&laquo; Prev</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo; Prev</a>
    @endif

    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="page-ellipsis">{{ $element }}</span>
        @endif

        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="page-active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" rel="next">Next &raquo;</a>
    @else
        <span class="page-disabled">Next &raquo;</span>
    @endif
</div>
@endif