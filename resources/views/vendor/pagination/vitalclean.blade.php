{{-- Paginación propia, sin depender de Tailwind (que este proyecto no
     carga) — el paginador por defecto de Laravel usa clases de Tailwind
     para los íconos SVG y sin ellas se ven a tamaño natural (gigantes). --}}
@if ($paginator->hasPages())
    <nav style="display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; font-size:.85rem;">
        <p style="margin:0; color:#6b7280;">
            Mostrando {{ $paginator->firstItem() }} a {{ $paginator->lastItem() }} de {{ $paginator->total() }} resultados
        </p>

        <div style="display:flex; gap:.4rem;">
            @if ($paginator->onFirstPage())
                <span class="btn btn-sm btn-secondary" style="opacity:.5; pointer-events:none;">&laquo; Anterior</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-sm btn-secondary">&laquo; Anterior</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="btn btn-sm btn-secondary" style="opacity:.5; pointer-events:none;">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="btn btn-sm" style="pointer-events:none;">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="btn btn-sm btn-secondary">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-sm btn-secondary">Siguiente &raquo;</a>
            @else
                <span class="btn btn-sm btn-secondary" style="opacity:.5; pointer-events:none;">Siguiente &raquo;</span>
            @endif
        </div>
    </nav>
@endif
