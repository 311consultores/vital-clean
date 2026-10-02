@extends('layouts.app')

@section('title', 'Vital Clean — Vendedor')

@section('content')
    {{--
        Diseño de cards (mockup compartido por el cliente) en vez de la fila
        de botones anterior: una card grande primaria para la acción más
        usada (Nuevo Pedido) y dos cards secundarias debajo, apiladas en
        columna para que se vea igual en móvil (el dispositivo real de uso
        de este panel, Anexo App) y en escritorio.
    --}}
    <style>
        .vc-home { max-width: 460px; margin: 0 auto; }
        .vc-home-greeting { margin: 0 0 1.25rem; }
        .vc-home-greeting h1 { margin: 0; font-size: 1.3rem; color: var(--texto); }
        .vc-home-greeting h1 strong { color: var(--azul); }
        .vc-cards { display: flex; flex-direction: column; gap: 1rem; }
        .vc-card {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: .6rem; text-decoration: none; border-radius: .85rem; padding: 1.75rem 1.25rem;
            text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,.08);
        }
        .vc-card svg { width: 42px; height: 42px; }
        .vc-card span { font-size: 1.05rem; font-weight: 600; }
        .vc-card--primary { background: var(--azul); color: var(--blanco); }
        .vc-card--primary:hover, .vc-card--primary:active { background: var(--azul-claro); }
        .vc-card--secondary { background: var(--blanco); color: var(--azul); border: 1px solid #e5e7eb; }
        .vc-card--secondary:hover, .vc-card--secondary:active { border-color: var(--azul-claro); }
    </style>

    <div class="vc-home">
        <div class="vc-home-greeting">
            <h1>Bienvenido<br><strong>{{ auth()->user()->nombre_completo ?? auth()->user()->username }}</strong></h1>
        </div>

        <div class="vc-cards">
            <a href="{{ route('vendedor.recoleccion.create') }}" class="vc-card vc-card--primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 8L12 3 3 8v8l9 5 9-5V8z"/>
                    <path d="M3 8l9 5 9-5"/>
                    <path d="M12 13v8"/>
                    <circle cx="18" cy="6" r="4.2" fill="currentColor" stroke="none" opacity=".2"/>
                    <path d="M18 4.2v3.6M16.2 6h3.6" stroke-width="1.6"/>
                </svg>
                <span>Nuevo Pedido</span>
            </a>

            <a href="{{ route('vendedor.pedidos.index') }}" class="vc-card vc-card--secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="5" y="4" width="14" height="17" rx="2"/>
                    <path d="M9 3h6a1 1 0 0 1 1 1v1H8V4a1 1 0 0 1 1-1z"/>
                    <path d="M8 10h8M8 13h8M8 16h5"/>
                </svg>
                <span>Pedidos</span>
            </a>

            <a href="{{ route('entrega.buscar') }}" class="vc-card vc-card--secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 8L12 3 3 8v8l9 5 9-5V8z"/>
                    <path d="M3 8l9 5 9-5"/>
                    <path d="M12 13v8"/>
                    <circle cx="18" cy="6" r="4.2" fill="#28a745" stroke="none"/>
                    <path d="M16.3 6.1l1.15 1.15L19.7 5" stroke="#fff" stroke-width="1.5"/>
                </svg>
                <span>Cierre de Entrega</span>
            </a>
        </div>
    </div>
@endsection
