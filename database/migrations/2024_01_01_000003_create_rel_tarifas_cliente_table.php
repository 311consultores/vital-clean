<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla: rel_tarifas_cliente (SRS §10.1, RN-01)
     * Precio pactado por contrato entre un cliente y cada tipo de servicio.
     * RN-01: el sistema debe consultar esta tabla usando el id_cliente antes
     * de calcular cualquier importe (el precio unitario NO es global).
     */
    public function up(): void
    {
        Schema::create('rel_tarifas_cliente', function (Blueprint $table) {
            $table->id('id_tarifa');
            $table->foreignId('id_cliente')->constrained('cat_clientes', 'id_cliente')->restrictOnDelete();
            $table->foreignId('id_servicio')->constrained('cat_servicios', 'id_servicio')->restrictOnDelete();
            $table->decimal('precio_pactado', 10, 2);
            $table->timestamps();

            // Un cliente no puede tener dos precios vigentes simultáneos para el mismo servicio.
            $table->unique(['id_cliente', 'id_servicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rel_tarifas_cliente');
    }
};
