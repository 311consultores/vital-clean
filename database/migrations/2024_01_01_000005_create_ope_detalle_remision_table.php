<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla: ope_detalle_remision (Partidas) (SRS §10.2)
     * Registro línea por línea del conteo real de prendas realizado en planta.
     * RN-06: no puede existir un registro sin un folio_sistema previo (ON DELETE RESTRICT).
     * RN-02: precio_aplicado queda "congelado" en el momento exacto del conteo.
     */
    public function up(): void
    {
        Schema::create('ope_detalle_remision', function (Blueprint $table) {
            $table->id('id_detalle');
            $table->foreignId('folio_sistema')->constrained('ope_notas_remision', 'folio_sistema')->restrictOnDelete();
            $table->foreignId('id_servicio')->constrained('cat_servicios', 'id_servicio')->restrictOnDelete();
            $table->integer('cantidad_entrada')->default(0); // cantidad real contada en planta
            $table->decimal('precio_aplicado', 10, 2)->nullable(); // precio vigente "congelado" al momento del conteo
            $table->decimal('subtotal', 10, 2)->nullable(); // cantidad_entrada x precio_aplicado
            $table->text('observacion_prenda')->nullable(); // ej. "mancha de vino"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ope_detalle_remision');
    }
};
