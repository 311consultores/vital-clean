<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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
    }
}
