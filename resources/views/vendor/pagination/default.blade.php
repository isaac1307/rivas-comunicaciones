@if ($paginator->hasPages())
    <nav class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-2 pb-1" aria-label="Navegación de páginas">
        <div class="text-muted small">
            Mostrando del <span class="fw-semibold text-dark">{{ $paginator->firstItem() }}</span> al <span class="fw-semibold text-dark">{{ $paginator->lastItem() }}</span> de <span class="fw-semibold text-dark">{{ $paginator->total() }}</span> resultados
        </div>

        <ul class="pagination pagination-sm mb-0 shadow-sm rounded-2 overflow-hidden border">
            {{-- Botón Anterior --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link text-muted bg-light border-0 px-3">
                        <i class="bi bi-chevron-left me-1"></i> Anterior
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link text-dark bg-white border-0 px-3" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        <i class="bi bi-chevron-left me-1"></i> Anterior
                    </a>
                </li>
            @endif

            {{-- Elementos de Paginación --}}
            @foreach ($elements as $element)
                {{-- Separador de puntos suspensivos (...) --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link text-muted bg-white border-0 px-3">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array de Enlaces --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link bg-primary text-white fw-bold border-0 px-3">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link text-dark bg-white border-0 px-3" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Botón Siguiente --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link text-dark bg-white border-0 px-3" href="{{ $paginator->nextPageUrl() }}" rel="next">
                        Siguiente <i class="bi bi-chevron-right ms-1"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link text-muted bg-light border-0 px-3">
                        Siguiente <i class="bi bi-chevron-right ms-1"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
