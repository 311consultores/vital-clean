<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Extensión sobre el diccionario de datos original (SRS §10.2) para
     * soportar CU-04 (Cierre de Ciclo y Liquidación): firma_cliente ya
     * existe pero es la firma de RECOLECCIÓN (CU-01); CU-04 pide "capturar
     * firma de recepción" al momento de la ENTREGA, que es un acto distinto
     * y no debe sobrescribir la firma original.
     */
    public function up(): void
    {
        Schema::table('ope_notas_remision', function (Blueprint $table) {
            $table->binary('firma_entrega')->nullable()->after('firma_cliente');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE ope_notas_remision MODIFY firma_entrega MEDIUMBLOB NULL');
        }
    }

    public function down(): void
    {
        Schema::table('ope_notas_remision', function (Blueprint $table) {
            $table->dropColumn('firma_entrega');
        });
    }
};
