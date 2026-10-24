@extends('layouts.app')

@section('title', 'Vital Clean — Instalar la App')

@section('content')
    <div class="page-header"><h1>Instalar la App</h1></div>

    <div class="card" style="max-width:640px;">
        <p style="color:#6b7280; margin-top:0;">
            Puedes instalar Vital Clean como una <strong>app</strong> en tu
            celular, tableta o computadora: se abre en pantalla completa,
            con su propio ícono, y entras más rápido. No necesitas ninguna
            tienda de aplicaciones.
        </p>

        <div id="instalar-app-standalone" style="display:none; color:#1e7e34; font-weight:600;">
            ✅ Ya estás usando la app instalada.
        </div>

        <div id="instalar-app-instrucciones">
            <button type="button" id="btn-instalar-app" class="btn" style="display:none; margin-bottom:1rem;" onclick="InstalarApp.instalar()">
                Instalar la app
            </button>

            <ul style="margin:0; padding-left:1.2rem; line-height:1.6;">
                <li><strong>Android (Chrome):</strong> usa el botón "Instalar la app" de arriba (si aparece), o abre el menú ⋮ y elige "Instalar aplicación".</li>
                <li><strong>iPhone o iPad (Safari):</strong> toca el botón Compartir (el cuadro con la flecha ↑) y elige "Agregar a pantalla de inicio".</li>
                <li><strong>Computadora (Chrome o Edge):</strong> haz clic en el ícono de instalar que aparece a la derecha de la barra de direcciones.</li>
            </ul>
        </div>
    </div>

    <div class="form-actions">
        @switch(auth()->user()->rol)
            @case('VENDEDOR')
                <a href="{{ route('vendedor.home') }}" class="btn btn-secondary">Volver al Inicio</a>
                @break
            @default
                <a href="{{ route('operaciones.dashboard') }}" class="btn btn-secondary">Volver al Inicio</a>
        @endswitch
    </div>

    <script>
        (function () {
            // InstalarApp ya está cargado (ver layouts.app) y en marcha;
            // aquí solo reflejamos su estado en esta pantalla de
            // instrucciones, sin duplicar la lógica de captura.
            function actualizar() {
                var standalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;
                document.getElementById('instalar-app-standalone').style.display = standalone ? 'block' : 'none';
                document.getElementById('instalar-app-instrucciones').style.display = standalone ? 'none' : 'block';
            }

            window.addEventListener('beforeinstallprompt', function () {
                document.getElementById('btn-instalar-app').style.display = 'inline-block';
            });
            window.addEventListener('appinstalled', actualizar);

            actualizar();
        })();
    </script>
@endsection
