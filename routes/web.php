<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Entrega\EntregaController;
use App\Http\Controllers\Notas\NotaPdfController;
use App\Http\Controllers\Operaciones\ClienteController;
use App\Http\Controllers\Operaciones\DashboardController;
use App\Http\Controllers\Operaciones\OrdenController;
use App\Http\Controllers\Operaciones\ServicioController;
use App\Http\Controllers\Operaciones\TarifaClienteController;
use App\Http\Controllers\Operaciones\UsuarioController;
use App\Http\Controllers\Planta\AuditoriaController;
use App\Http\Controllers\Produccion\ProduccionController;
use App\Http\Controllers\Vendedor\PedidoController;
use App\Http\Controllers\Vendedor\RecoleccionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Si ya está autenticado, no debe volver a /login: eso crearía un
    // bucle de redirecciones contra el middleware 'guest'.
    if (auth()->check()) {
        return redirect()->to(
            (new LoginController)->redirectPathFor(auth()->user())
        );
    }

    return redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// PDF de la nota de remisión, para el enlace que va dentro del mensaje de
// WhatsApp (App\Support\WhatsApp). Pública pero con URL firmada: el cliente
// externo no tiene cuenta, pero la firma evita que alguien adivine folios
// consecutivos y descargue notas ajenas.
Route::get('/notas/{orden}/pdf', [NotaPdfController::class, 'show'])
    ->middleware('signed')
    ->name('notas.pdf');

// Detalle de folio (RF genérico): pantalla de solo lectura reutilizada como
// botón "Ver" desde el dashboard y desde Planta/Producción/Entrega, así que
// se abre a los tres roles operativos (no solo Admin/Operador).
Route::middleware(['auth', 'role:ADMIN,OPERADOR,VENDEDOR'])
    ->get('/operaciones/ordenes/{orden}', [OrdenController::class, 'show'])
    ->name('operaciones.ordenes.show');

// Panel Administrativo (Admin/Operador) — Anexo Panel Web.
Route::middleware(['auth', 'role:ADMIN,OPERADOR'])
    ->prefix('operaciones')
    ->name('operaciones.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Catálogos (A-02, A-03, A-06): "gestiona precios personalizados por
        // cliente" es responsabilidad del Administrador — solo ADMIN.
        Route::middleware('role:ADMIN')->group(function () {
            Route::resource('clientes', ClienteController::class)->except(['show']);
            Route::resource('servicios', ServicioController::class)->except(['show']);
            Route::resource('tarifas', TarifaClienteController::class)
                ->parameters(['tarifas' => 'tarifa'])
                ->except(['show']);

            // Catálogo de Usuarios (RF-01 extendido): antes se hacían las
            // altas/bajas manualmente por phpMyAdmin.
            Route::resource('usuarios', UsuarioController::class)
                ->parameters(['usuarios' => 'usuario'])
                ->except(['show']);
        });
    });

// CU-02: Auditoría de Conteo y Valoración (Planta) — GUI §12.2.
// El Operador de Planta usa este módulo; ADMIN puede entrar también para
// destrabar conteos ya bloqueados (RN-03).
Route::middleware(['auth', 'role:OPERADOR,ADMIN'])
    ->prefix('planta')
    ->name('planta.')
    ->group(function () {
        Route::get('/', [AuditoriaController::class, 'buscar'])->name('buscar');
        Route::post('/', [AuditoriaController::class, 'iniciar'])->name('iniciar');
        Route::get('/{orden}/conteo', [AuditoriaController::class, 'conteo'])->name('conteo');
        Route::post('/{orden}/conteo', [AuditoriaController::class, 'guardar'])->name('guardar');
    });

// CU-03: Control de Producción e Incidencias — Lavado/Secado/Planchado.
// Comparte el rol OPERADOR con Planta (el diccionario de datos solo define
// 3 roles); ADMIN puede entrar también para destrabar folios ya cerrados.
Route::middleware(['auth', 'role:OPERADOR,ADMIN'])
    ->prefix('produccion')
    ->name('produccion.')
    ->group(function () {
        Route::get('/', [ProduccionController::class, 'buscar'])->name('buscar');
        Route::post('/', [ProduccionController::class, 'iniciar'])->name('iniciar');
        Route::get('/{orden}', [ProduccionController::class, 'detalle'])->name('detalle');
        Route::post('/{orden}', [ProduccionController::class, 'guardar'])->name('guardar');
    });

// CU-04: Cierre de Ciclo y Liquidación.
Route::middleware(['auth', 'role:VENDEDOR,ADMIN'])
    ->prefix('entrega')
    ->name('entrega.')
    ->group(function () {
        Route::get('/', [EntregaController::class, 'buscar'])->name('buscar');
        Route::post('/', [EntregaController::class, 'iniciar'])->name('iniciar');
        Route::get('/{orden}', [EntregaController::class, 'remision'])->name('remision');
        Route::post('/{orden}', [EntregaController::class, 'confirmar'])->name('confirmar');
    });

// App de Vendedor (ruta/tablet) — Anexo App.
Route::middleware(['auth', 'role:VENDEDOR'])
    ->prefix('vendedor')
    ->name('vendedor.')
    ->group(function () {
        Route::get('/', function () {
            return view('vendedor.home');
        })->name('home');

        // CU-01: Levantamiento de Orden en Sitio (Anexo App, pantallas 02-08).
        Route::prefix('recoleccion')->name('recoleccion.')->group(function () {
            Route::get('/', [RecoleccionController::class, 'create'])->name('create');
            Route::post('/', [RecoleccionController::class, 'store'])->name('store');
            Route::get('/resumen', [RecoleccionController::class, 'resumen'])->name('resumen');
            Route::post('/confirmar', [RecoleccionController::class, 'confirmar'])->name('confirmar');
            Route::get('/{notaRemision}/exito', [RecoleccionController::class, 'exito'])->name('exito');
        });

        // Anexo App, pantallas 09-10: seguimiento de pedidos.
        Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
        Route::get('/pedidos/{notaRemision}', [PedidoController::class, 'show'])->name('pedidos.show');
    });
