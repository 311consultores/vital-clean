<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tabla: ope_notas_remision (Cabecera) (SRS §10.2)
     * Registro maestro de cada movimiento de recolección. Controla el folio,
     * estatus y firmas digitales.
     */
    public function up(): void
    {
        Schema::create('ope_notas_remision', function (Blueprint $table) {
            $table->id('folio_sistema');

            // RN-05: folio_fisico es obligatorio - puente entre el papel y el digital.
            $table->string('folio_fisico', 20)->index();

            $table->foreignId('id_cliente')->constrained('cat_clientes', 'id_cliente')->restrictOnDelete();
            $table->foreignId('id_vendedor')->constrained('sys_usuarios', 'id_usuario')->restrictOnDelete();

            $table->dateTime('fecha_recoleccion')->useCurrent();
            $table->date('fecha_entrega_prog')->nullable();

            // RN-07: flujo estrictamente secuencial RUTA -> PLANTA_RECIBIDO -> PROCESO -> LISTO -> ENTREGADO.
            $table->enum('estatus_orden', [
                'RUTA', 'PLANTA_RECIBIDO', 'PROCESO', 'LISTO', 'ENTREGADO', 'CANCELADO',
            ])->default('RUTA');

            $table->binary('firma_cliente')->nullable(); // imagen de firma digital capturada en tablet
            $table->string('geolocalizacion', 100)->nullable(); // coordenadas GPS del punto de recolección

            // RN-03: una vez que Planta guarda y bloquea el conteo, solo ADMIN puede
            // modificar cantidades. Bandera de bloqueo a nivel de folio.
            $table->boolean('conteo_bloqueado')->default(false);

            $table->timestamps();
        });

        // MySQL: ampliar firma_cliente a MEDIUMBLOB (Laravel binary() crea BLOB por defecto).
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE ope_notas_remision MODIFY firma_cliente MEDIUMBLOB NULL');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ope_notas_remision');
    }
};
