<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla: sys_usuarios (SRS Vital Clean v1.1 - Diccionario de Datos §10.3)
     * Gestión de accesos al sistema por perfil de empleado.
     */
    public function up(): void
    {
        Schema::create('sys_usuarios', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('username', 50)->unique();
            $table->string('password_hash', 255); // bcrypt, RNF-03 / §14.4
            $table->enum('rol', ['VENDEDOR', 'OPERADOR', 'ADMIN']); // Ver §3 Actores del Sistema
            $table->string('nombre_completo', 150)->nullable();
            $table->string('email', 100)->unique()->nullable();
            $table->boolean('activo')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        // Framework: tabla de sesiones (SESSION_DRIVER=database)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index()
                ->constrained('sys_usuarios', 'id_usuario')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('sys_usuarios');
    }
};
