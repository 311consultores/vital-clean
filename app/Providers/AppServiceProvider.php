<?php

namespace App\Providers;

use App\Models\NotaRemision;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // El proyecto no carga Tailwind (vista por defecto de Laravel para
        // paginación), así que se usa una vista propia en todo el sitio.
        Paginator::defaultView('vendor.pagination.vitalclean');

        // #11: campanita de notificaciones en el encabezado, visible en
        // cualquier pantalla de Admin/Operador (no solo en el dashboard).
        // Usa el mismo dato que el aviso del dashboard (misma sesión
        // 'dashboard_ultima_visita') pero sin actualizarla — eso solo pasa
        // al visitar el dashboard (DashboardController), que es lo que
        // "marca como visto" y limpia el número.
        View::composer('layouts.app', function ($view) {
            $usuario = auth()->user();
            $pedidosNuevosHeader = 0;

            if ($usuario && in_array($usuario->rol, ['ADMIN', 'OPERADOR'], true)) {
                $pedidosNuevosHeader = NotaRemision::contarNuevosDesde(session('dashboard_ultima_visita'));
            }

            $view->with('pedidosNuevosHeader', $pedidosNuevosHeader);
        });
    }
}
