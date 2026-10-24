<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * #5: nueva/usada se captura desde que el Vendedor levanta el pedido
     * (rotación de blancos en HORECA). #11: color, para prendas que
     * requieren clasificarse por color (cat_servicios.requiere_color).
     * #12: es_desmanche marca la línea para que se sub-totalice aparte —
     * Desmanche es un servicio adicional sobre una prenda, no una prenda
     * en sí misma.
     */
    public function up(): void
    {
        Schema::table('ope_detalle_remision', function (Blueprint $table) {
            $table->enum('condicion_prenda', ['nueva', 'usada'])->nullable()->after('id_servicio');
            $table->string('color', 50)->nullable()->after('condicion_prenda');
            $table->boolean('es_desmanche')->default(false)->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('ope_detalle_remision', function (Blueprint $table) {
            $table->dropColumn(['condicion_prenda', 'color', 'es_desmanche']);
        });
    }
};
