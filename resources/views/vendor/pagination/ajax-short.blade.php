@if ($paginator->hasPages())
    <nav>
        <ul class="pagination justify-content-center">
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">&laquo; Prev</span>
                </li>
            @else
                <li class="page-item">
                    <a href="{{ $paginator->previousPageUrl() }}" class="page-link">&laquo; Prev</a>
                </li>
            @endif

            @php
                $pages = collect([1, 2, $paginator->currentPage() - 1, $paginator->currentPage(), $paginator->currentPage() + 1, $paginator->lastPage() - 1, $paginator->lastPage()])
                    ->filter(fn ($page) => $page > 0 && $page <= $paginator->lastPage())
                    ->unique()
                    ->sort()
                    ->values();
                $previous = null;
            @endphp

            @foreach ($pages as $page)
                @if (!is_null($previous) && $page > $previous + 1)
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                @endif

                <li class="page-item {{ $page === $paginator->currentPage() ? 'active' : '' }}">
                    <a href="{{ $paginator->url($page) }}" class="page-link">{{ $page }}</a>
                </li>

                @php($previous = $page)
            @endforeach

            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a href="{{ $paginator->nextPageUrl() }}" class="page-link">Next &raquo;</a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">Next &raquo;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
