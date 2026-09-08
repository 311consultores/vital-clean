<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extensión sobre el diccionario de datos original (SRS §10.2) para
     * soportar CU-03 (Control de Producción e Incidencias): el flujo pide
     * "validar cantidades entrada vs. salida" y el diccionario solo define
     * cantidad_entrada. cantidad_salida es lo que efectivamente regresa de
     * Producción (lavado/secado/planchado) por línea de prenda.
     */
    public function up(): void
    {
        Schema::table('ope_detalle_remision', function (Blueprint $table) {
            $table->integer('cantidad_salida')->nullable()->after('cantidad_entrada');
        });
    }

    public function down(): void
    {
        Schema::table('ope_detalle_remision', function (Blueprint $table) {
            $table->dropColumn('cantidad_salida');
        });
    }
};
