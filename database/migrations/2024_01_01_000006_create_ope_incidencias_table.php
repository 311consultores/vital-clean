<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla: ope_incidencias (SRS §10.3)
     * Registro de evidencias fotográficas de daños detectados en prendas
     * durante la auditoría o producción.
     */
    public function up(): void
    {
        Schema::create('ope_incidencias', function (Blueprint $table) {
            $table->id('id_incidencia');
            $table->foreignId('id_detalle')->constrained('ope_detalle_remision', 'id_detalle')->restrictOnDelete();
            // Ruta en servidor de imágenes externo (S3/Cloudflare R2, RNF-07), no el binario.
            $table->string('foto_evidencia', 255)->nullable();
            $table->text('comentario')->nullable(); // ej. "Quemadura", "Mancha irreversible", "Roto"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ope_incidencias');
    }
};
