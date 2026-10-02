<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El folio ya no se captura a mano (antes era el papel preimpreso,
     * RN-05) — el sistema lo autogenera y lo muestra con prefijo VC-
     * (folio raíz) o SUB- (subnota). folio_fisico se sigue llenando
     * automáticamente con ese mismo valor para no romper el resto del
     * código que ya lo usa como referencia visible/buscable.
     *
     * secuencia_subnota: posición de esta subnota entre las subnotas
     * inmediatas de su propio padre (1, 2, 3...) — permite mostrar
     * SUB-0002-1, SUB-0002-2, y encadenado SUB-0002-1-1 para una
     * subnota de subnota. Null en cualquier folio que no sea subnota.
     */
    public function up(): void
    {
        Schema::table('ope_notas_remision', function (Blueprint $table) {
            $table->unsignedTinyInteger('secuencia_subnota')->nullable()->after('folio_padre');
        });
    }

    public function down(): void
    {
        Schema::table('ope_notas_remision', function (Blueprint $table) {
            $table->dropColumn('secuencia_subnota');
        });
    }
};
