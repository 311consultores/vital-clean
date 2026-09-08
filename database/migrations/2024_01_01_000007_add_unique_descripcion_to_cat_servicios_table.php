<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Evita productos duplicados en el catálogo (cat_servicios) y permite
     * usar INSERT ... ON DUPLICATE KEY UPDATE al cargar catálogos desde
     * archivos externos (ej. LISTA_PRODUCTOS.xlsx).
     */
    public function up(): void
    {
        Schema::table('cat_servicios', function (Blueprint $table) {
            $table->unique('descripcion');
        });
    }

    public function down(): void
    {
        Schema::table('cat_servicios', function (Blueprint $table) {
            $table->dropUnique(['descripcion']);
        });
    }
};
