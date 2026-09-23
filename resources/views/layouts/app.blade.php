<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Vital Clean')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('images/logo-vital-clean.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600&display=swap" rel="stylesheet">
    {{--
        Paleta de colores — imagen de marca Vital Clean (azul marino + acento
        cian, referencia: avada.website/plumber). --rojo y --amarillo se
        conservan como colores funcionales (errores, cancelado, prioridad y
        estatus RN-07), no de marca.
    --}}
    <style>
        :root {
            --azul: #26437D;
            --azul-claro: #17A9E0;
            --blanco: #FFFFFF;
            --gris-claro: #F5F5F5;
            --rojo: #C0392B;
            --amarillo: #F39C12;
            --texto: #1f2937;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Poppins', system-ui, -apple-system, "Segoe UI", sans-serif;
            background: var(--gris-claro);
            color: var(--texto);
        }
        header.app-header {
            background: var(--azul);
            color: var(--blanco);
            padding: .7rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
        }
        header.app-header a { color: var(--blanco); text-decoration: none; font-weight: 600; }
        header.app-header .brand { font-size: 1.1rem; letter-spacing: .02em; flex-shrink: 0; }
        header.app-header .header-user {
            display: flex; align-items: center; gap: .6rem; min-width: 0;
        }
        header.app-header .header-user-name { white-space: nowrap; }
        header.app-header .btn-logout {
            display: flex; align-items: center; gap: .4rem;
            background: var(--rojo); flex-shrink: 0;
        }
        header.app-header .btn-logout svg { flex-shrink: 0; }
        /* #11: campanita de notificaciones — estilo "burbuja" tipo redes
           sociales (Instagram/Facebook): ícono con un punto/número rojo
           sobrepuesto en la esquina cuando hay pedidos nuevos. */
        .header-bell {
            position: relative; display: inline-flex; align-items: center;
            justify-content: center; color: var(--blanco); flex-shrink: 0;
            padding: .3rem; border-radius: 999px;
        }
        .header-bell:hover { background: rgba(255,255,255,.12); }
        .header-bell-badge {
            position: absolute; top: -2px; right: -2px;
            background: var(--rojo); color: var(--blanco);
            font-size: .62rem; font-weight: 700; line-height: 1;
            min-width: 16px; height: 16px; border-radius: 999px;
            display: flex; align-items: center; justify-content: center;
            padding: 0 3px; border: 2px solid var(--azul);
        }
        /* Pantallas medianas: el nombre completo ya no cabe entero junto a
           la insignia de rol y "Salir" — se trunca con elipsis en vez de
           empujar el botón fuera de la pantalla o encimarse. */
        @media (max-width: 700px) {
            header.app-header .header-user-name {
                overflow: hidden; text-overflow: ellipsis; max-width: 160px;
            }
        }
        /* Móvil (uso real de este panel en campo, Anexo App): con poco
           ancho, mejor ocultar el nombre por completo y que "Salir" sea un
           ícono solo — la insignia de rol y el ícono bastan para
           identificar la sesión activa. */
        @media (max-width: 480px) {
            header.app-header .header-user-name { display: none; }
            header.app-header .btn-logout .logout-text { display: none; }
            header.app-header .btn-logout { padding: .5rem .65rem; }
        }

        /* Identidad de marca — ver resources/views/partials/logo.blade.php */
        .vc-logo { display: flex; align-items: center; gap: .45rem; }
        .vc-logo-icon { flex-shrink: 0; display: block; }
        .vc-logo-text { display: flex; align-items: baseline; gap: .3rem; line-height: 1; }
        .vc-logo-super { display: none; }
        .vc-logo-main { font-size: 1.05rem; font-weight: 900; letter-spacing: .02em; font-family: Arial, "Helvetica Neue", sans-serif; }
        .vc-logo-script { font-size: .95rem; font-style: italic; font-family: "Brush Script MT", "Segoe Script", cursive; }
        .layout { display: flex; align-items: flex-start; }
        nav.sidebar {
            width: 210px;
            flex-shrink: 0;
            background: var(--blanco);
            min-height: calc(100vh - 58px);
            padding: 1.25rem 0;
            border-right: 1px solid #e5e7eb;
        }
        .sidebar-toggle {
            display: none;
            background: transparent; border: none; color: var(--blanco);
            padding: .4rem; cursor: pointer; flex-shrink: 0; border-radius: .375rem;
        }
        .sidebar-toggle:hover { background: rgba(255,255,255,.12); }
        .sidebar-close { display: none; }
        .sidebar-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 45; }
        .sidebar-backdrop.is-open { display: block; }
        /* El sidebar de Admin/Operador (Panel Web) era fijo y nunca
           colapsaba, así que en pantallas angostas empujaba TODA la página
           a un ancho enorme y forzaba scroll horizontal general — en vez de
           eso, aquí se vuelve un cajón ("drawer") que se desliza desde la
           izquierda al tocar el ícono de menú en el encabezado. */
        @media (max-width: 860px) {
            .sidebar-toggle { display: inline-flex; align-items: center; justify-content: center; }
            nav.sidebar {
                position: fixed; top: 0; left: 0; bottom: 0; z-index: 50;
                width: 250px; max-width: 80vw;
                transform: translateX(-100%);
                transition: transform .25s ease;
                overflow-y: auto;
                box-shadow: 2px 0 12px rgba(0,0,0,.2);
            }
            nav.sidebar.is-open { transform: translateX(0); }
            .sidebar-close {
                display: block; margin: 0 0 .5rem 1.25rem; background: transparent;
                border: none; font-size: 1.5rem; line-height: 1; color: #6b7280; cursor: pointer;
            }
            .content { padding: 1rem; }
        }
        nav.sidebar .section-title {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #9ca3af;
            padding: .5rem 1.25rem .25rem;
        }
        nav.sidebar a {
            display: block;
            padding: .55rem 1.25rem;
            color: var(--texto);
            text-decoration: none;
            font-size: .92rem;
        }
        nav.sidebar a:hover { background: var(--gris-claro); }
        nav.sidebar a.active { background: var(--gris-claro); font-weight: 600; color: var(--azul); border-right: 3px solid var(--azul); }
        /* min-width:0 es necesario porque, en móvil, .content queda como
           único hijo flex de .layout (nav.sidebar se vuelve position:fixed)
           — sin esto, un flex item nunca se encoge más allá del ancho
           mínimo de su contenido (inputs con min-width, tablas, etc.) y
           empuja toda la página a desbordarse horizontalmente. */
        .content { flex: 1; min-width: 0; padding: 1.5rem; max-width: 1080px; }
        main { max-width: 960px; margin: 0 auto; padding: 1.5rem; }
        .card {
            background: var(--blanco);
            border-radius: .5rem;
            padding: 1.25rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.08);
        }
        .btn {
            display: inline-block;
            background: var(--azul);
            color: var(--blanco);
            border: none;
            padding: .6rem 1.1rem;
            border-radius: .375rem;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
        }
        .btn:hover { background: var(--azul-claro); }
        .badge { display: inline-block; padding: .15rem .5rem; border-radius: 999px; font-size: .75rem; color: var(--blanco); }
        .badge-admin { background: var(--azul); }
        .badge-operador { background: var(--azul-claro); }
        .badge-vendedor { background: var(--amarillo); }
        .alert { padding: .75rem 1rem; border-radius: .375rem; margin-bottom: 1rem; font-size: .9rem; }
        .alert-status { background: #eafaf1; border: 1px solid #28a745; color: #1e7e34; }
        .alert-error { background: #fdecea; border: 1px solid var(--rojo); color: var(--rojo); }
        table.data-table { width: 100%; border-collapse: collapse; background: var(--blanco); border-radius: .5rem; overflow: hidden; }
        table.data-table th, table.data-table td { text-align: left; padding: .65rem .9rem; border-bottom: 1px solid #eee; font-size: .9rem; }
        table.data-table th { background: var(--azul); color: var(--blanco); font-weight: 600; }
        table.data-table tr:hover td { background: #fafafa; }
        table.data-table tr[data-href] { cursor: pointer; }
        .page-header { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .5rem; margin-bottom: 1rem; }
        /* Buscadores con min-width fijo (dashboard, catálogos) — a partir
           de aquí ya no compiten en la misma fila que el título, y pueden
           encoger sin forzar scroll horizontal. */
        .page-header form { display: flex; flex-wrap: wrap; gap: .4rem; }
        .page-header h1 { margin: 0; font-size: 1.3rem; color: var(--azul); }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-size: .85rem; font-weight: 600; margin-bottom: .3rem; }
        .form-group input[type=text], .form-group input[type=email], .form-group input[type=number],
        .form-group select, .form-group textarea {
            width: 100%; max-width: 420px; padding: .55rem .7rem; font-size: .95rem;
            border: 1px solid #d1d5db; border-radius: .375rem;
        }
        .form-actions { margin-top: 1.25rem; display: flex; gap: .6rem; align-items: center; }
        .btn-secondary { background: #6b7280; }
        .btn-secondary:hover { background: #4b5563; }
        .btn-danger { background: var(--rojo); }
        .btn-danger:hover { opacity: .9; }
        .btn-sm { padding: .3rem .7rem; font-size: .85rem; }
        .field-error { color: var(--rojo); font-size: .8rem; margin-top: .2rem; }
        .actions-cell { display: flex; gap: .4rem; }
        /* RN-07: colores por estatus_orden */
        .badge-ruta { background: var(--amarillo); }
        .badge-planta_recibido { background: var(--azul-claro); }
        .badge-proceso { background: #8e44ad; }
        .badge-listo { background: #28a745; }
        .badge-entregado { background: #1e7e34; }
        .badge-cancelado { background: var(--rojo); }

        /* RN-07: timeline de estatus_orden (estilo "seguimiento de paquete"). */
        .timeline { display: flex; align-items: flex-start; margin: 1.25rem 0; }
        .timeline-step { flex: 1; text-align: center; position: relative; min-width: 0; }
        .timeline-step .timeline-line {
            position: absolute; top: 15px; left: -50%; width: 100%; height: 4px;
            background: #e5e7eb; z-index: 0;
        }
        .timeline-step:first-child .timeline-line { display: none; }
        .timeline-step.completado .timeline-line { background: #28a745; }
        .timeline-step .timeline-circle {
            width: 32px; height: 32px; border-radius: 50%;
            background: var(--blanco); border: 3px solid #e5e7eb;
            color: #9ca3af;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto; font-weight: 700; font-size: .95rem;
            position: relative; z-index: 1;
        }
        .timeline-step.completado .timeline-circle { background: #28a745; border-color: #28a745; color: var(--blanco); }
        .timeline-step.actual .timeline-circle { background: var(--azul); border-color: var(--azul); color: var(--blanco); box-shadow: 0 0 0 4px rgba(38,67,125,.18); }
        .timeline-step .timeline-label { margin-top: .5rem; font-size: .8rem; color: #9ca3af; }
        .timeline-step.completado .timeline-label,
        .timeline-step.actual .timeline-label { color: var(--texto); font-weight: 600; }
        .timeline-cancelado {
            background: #fdecea; border: 1px solid var(--rojo); color: var(--rojo);
            padding: .75rem 1rem; border-radius: .375rem; margin: 1.25rem 0; font-weight: 600;
            text-align: center;
        }

        /* "+ Reportar daño/incidencia" en Planta y Producción: el
           <summary> de un <details> estilizado como chip tipo "pill", con
           suficiente área de toque para verse bien en móvil/tablet (uso
           real de estas dos pantallas). */
        summary.btn-pill {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            cursor: pointer;
            font-size: .85rem;
            font-weight: 600;
            color: var(--azul);
            background: var(--gris-claro);
            border: 1px solid #d7dee8;
            border-radius: 999px;
            padding: .45rem 1rem;
            user-select: none;
            list-style: none;
        }
        summary.btn-pill::-webkit-details-marker { display: none; }
        summary.btn-pill:hover { border-color: var(--azul-claro); }
        details[open] > summary.btn-pill { background: var(--azul); color: var(--blanco); border-color: var(--azul); }
    </style>
</head>
<body>
    @auth
        <header class="app-header">
            <span style="display:flex; align-items:center; gap:.4rem; min-width:0;">
                @if (in_array(auth()->user()->rol, ['ADMIN', 'OPERADOR'], true))
                    <button type="button" class="sidebar-toggle" id="btn-sidebar-toggle" aria-label="Abrir menú">
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <line x1="3" y1="12" x2="21" y2="12"/>
                            <line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                @endif
                <span class="brand">@include('partials.logo', ['size' => 30, 'stacked' => false, 'light' => true])</span>
            </span>
            <span class="header-user">
                @if (in_array(auth()->user()->rol, ['ADMIN', 'OPERADOR'], true))
                    <a href="{{ route('operaciones.dashboard') }}" class="header-bell" title="Pedidos nuevos">
                        <svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        @if ($pedidosNuevosHeader > 0)
                            <span class="header-bell-badge">{{ $pedidosNuevosHeader > 9 ? '9+' : $pedidosNuevosHeader }}</span>
                        @endif
                    </a>
                @endif
                <span class="header-user-name">{{ auth()->user()->nombre_completo ?? auth()->user()->username }}</span>
                <span class="badge badge-{{ strtolower(auth()->user()->rol) }}">{{ auth()->user()->rol }}</span>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-logout" title="Salir">
                        <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        <span class="logout-text">Salir</span>
                    </button>
                </form>
            </span>
        </header>
    @endauth

    @auth
        @if (in_array(auth()->user()->rol, ['ADMIN', 'OPERADOR'], true))
            <div class="layout">
                <nav class="sidebar" id="app-sidebar">
                    <button type="button" class="sidebar-close" id="btn-sidebar-close" aria-label="Cerrar menú">&times;</button>
                    <a href="{{ route('operaciones.dashboard') }}" class="{{ request()->routeIs('operaciones.dashboard') ? 'active' : '' }}">Operaciones</a>
                    <a href="{{ route('planta.buscar') }}" class="{{ request()->routeIs('planta.*') ? 'active' : '' }}">Auditoría de Planta</a>
                    <a href="{{ route('produccion.buscar') }}" class="{{ request()->routeIs('produccion.*') ? 'active' : '' }}">Control de Producción</a>
                    @if (auth()->user()->rol === 'ADMIN')
                        <a href="{{ route('entrega.buscar') }}" class="{{ request()->routeIs('entrega.*') ? 'active' : '' }}">Cierre de Entrega</a>
                    @endif
                    @if (auth()->user()->rol === 'ADMIN')
                        <div class="section-title">Catálogos</div>
                        <a href="{{ route('operaciones.clientes.index') }}" class="{{ request()->routeIs('operaciones.clientes.*') ? 'active' : '' }}">Clientes</a>
                        <a href="{{ route('operaciones.servicios.index') }}" class="{{ request()->routeIs('operaciones.servicios.*') ? 'active' : '' }}">Servicios</a>
                        <a href="{{ route('operaciones.tarifas.index') }}" class="{{ request()->routeIs('operaciones.tarifas.*') ? 'active' : '' }}">Tarifarios</a>
                        <a href="{{ route('operaciones.usuarios.index') }}" class="{{ request()->routeIs('operaciones.usuarios.*') ? 'active' : '' }}">Usuarios</a>
                    @endif
                </nav>
                <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
                <div class="content">
                    @if (session('status'))
                        <div class="alert alert-status">{{ session('status') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-error">{{ session('error') }}</div>
                    @endif
                    @yield('content')
                </div>
            </div>
            <script>
                (function () {
                    var toggle = document.getElementById('btn-sidebar-toggle');
                    var cerrar = document.getElementById('btn-sidebar-close');
                    var sidebar = document.getElementById('app-sidebar');
                    var backdrop = document.getElementById('sidebar-backdrop');
                    if (!toggle || !sidebar || !backdrop) return;

                    function abrirMenu() {
                        sidebar.classList.add('is-open');
                        backdrop.classList.add('is-open');
                    }
                    function cerrarMenu() {
                        sidebar.classList.remove('is-open');
                        backdrop.classList.remove('is-open');
                    }

                    toggle.addEventListener('click', abrirMenu);
                    if (cerrar) cerrar.addEventListener('click', cerrarMenu);
                    backdrop.addEventListener('click', cerrarMenu);
                    sidebar.querySelectorAll('a').forEach(function (enlace) {
                        enlace.addEventListener('click', cerrarMenu);
                    });
                })();
            </script>
        @else
            <main>
                @yield('content')
            </main>
        @endif
    @else
        <main>
            @yield('content')
        </main>
    @endauth

    {{--
        En los grids (dashboard, Planta, Producción, Entrega, Mis Pedidos),
        dar clic a cualquier parte de la fila abre el registro — no hace
        falta apuntarle al botón "Ver". Los botones/enlaces propios de la
        fila (Auditar, Entregar, Ver, etc.) siguen funcionando normal, sin
        doble navegación.
    --}}
    <script>
        document.addEventListener('click', function (e) {
            var fila = e.target.closest('tr[data-href]');
            if (!fila) return;
            if (e.target.closest('a, button, input, select, textarea, label')) return;
            window.location = fila.dataset.href;
        });
    </script>
</body>
</html>
