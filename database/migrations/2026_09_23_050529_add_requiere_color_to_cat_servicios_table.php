<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * #11: algunas prendas existen en varias subdivisiones de color (p.
     * ej. blancos vs. de color) y hace falta poder anotar cuál es al
     * levantar el pedido — este check marca en el catálogo qué prendas
     * aplican.
     */
    public function up(): void
    {
        Schema::table('cat_servicios', function (Blueprint $table) {
            $table->boolean('requiere_color')->default(false)->after('categoria');
        });
    }

    public function down(): void
    {
        Schema::table('cat_servicios', function (Blueprint $table) {
            $table->dropColumn('requiere_color');
        });
    }
};
