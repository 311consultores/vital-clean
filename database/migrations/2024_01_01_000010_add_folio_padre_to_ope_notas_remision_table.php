<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extensión sobre el diccionario de datos original (SRS §10.2) para
     * soportar entregas parciales dentro de CU-04 (Cierre de Ciclo y
     * Liquidación): cuando en la auditoría de cierre no se entrega toda
     * la mercancía, la parte pendiente se ampara en una "subnota" nueva
     * (mismo cliente/vendedor, folio_fisico propio, RN-05) para poder
     * facturar por parcialidades. folio_padre enlaza la subnota con el
     * folio original del que se desprendió.
     */
    public function up(): void
    {
        Schema::table('ope_notas_remision', function (Blueprint $table) {
            $table->foreignId('folio_padre')->nullable()->after('folio_fisico')
                ->constrained('ope_notas_remision', 'folio_sistema')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ope_notas_remision', function (Blueprint $table) {
            $table->dropConstrainedForeignId('folio_padre');
        });
    }
};
