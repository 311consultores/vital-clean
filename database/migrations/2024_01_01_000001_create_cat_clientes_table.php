<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla: cat_clientes (SRS §10.1)
     * Almacena el catálogo de Hoteles y Restaurantes clientes de Vital Clean.
     */
    public function up(): void
    {
        Schema::create('cat_clientes', function (Blueprint $table) {
            $table->id('id_cliente');
            $table->string('nombre_comercial', 150);
            $table->string('razon_social', 150)->nullable();
            $table->string('rfc', 13)->unique()->nullable();
            $table->text('direccion')->nullable(); // Dirección de recolección en Mérida
            $table->string('telefono', 15)->nullable();
            $table->string('email_facturacion', 100)->nullable();
            // estatus_credito: 1 Activo, 0 Suspendido (RN-04: bloqueo por crédito suspendido)
            $table->boolean('estatus_credito')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cat_clientes');
    }
};
