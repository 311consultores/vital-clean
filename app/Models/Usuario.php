<?php

namespace App\Models;

use Database\Factories\UsuarioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Modelo: sys_usuarios (SRS Vital Clean §10.3 - RF-01 Autenticación por rol)
 */
#[Fillable(['username', 'password_hash', 'rol', 'nombre_completo', 'email', 'activo'])]
#[Hidden(['password_hash', 'remember_token'])]
class Usuario extends Authenticatable
{
    /** @use HasFactory<UsuarioFactory> */
    use HasFactory, Notifiable;

    protected $table = 'sys_usuarios';

    protected $primaryKey = 'id_usuario';

    /**
     * Vital Clean guarda el hash en la columna password_hash (RNF-03: bcrypt)
     * en lugar de la convención por defecto de Laravel ('password').
     */
    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password_hash' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function notasRemision()
    {
        return $this->hasMany(NotaRemision::class, 'id_vendedor', 'id_usuario');
    }
}
