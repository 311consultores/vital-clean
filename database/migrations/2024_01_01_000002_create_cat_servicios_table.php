<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla: cat_servicios (SRS §10.1)
     * Catálogo de tipos de prendas o servicios que ofrece la lavandería.
     */
    public function up(): void
    {
        Schema::create('cat_servicios', function (Blueprint $table) {
            $table->id('id_servicio');
            $table->string('descripcion', 100); // Sábana, Toalla, Funda, etc.
            $table->enum('unidad', ['PZA', 'KG']);
            $table->string('categoria', 50)->nullable(); // Hotelería, Restaurante, Uniformes
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cat_servicios');
    }
};
