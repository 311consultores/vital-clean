<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Protege envíos repetidos de las pantallas resilientes a conexión
 * intermitente (firma de Nuevo Pedido y de Cierre de Entrega — ver
 * public/js/envio-resiliente.js): si el mismo envío se reintenta porque la
 * respuesta original se perdió (señal intermitente en campo), la clave ya
 * registrada evita duplicar el pedido/la entrega.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->string('clave', 64)->primary();
            $table->foreignId('id_usuario')->constrained('sys_usuarios', 'id_usuario');
            $table->string('ruta', 100);
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotency_keys');
    }
};
