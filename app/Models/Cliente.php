<?php

namespace App\Models;

use Database\Factories\ClienteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo: cat_clientes (SRS Vital Clean §10.1)
 */
class Cliente extends Model
{
    /** @use HasFactory<ClienteFactory> */
    use HasFactory;

    protected $table = 'cat_clientes';

    protected $primaryKey = 'id_cliente';

    protected $fillable = [
        'nombre_comercial',
        'razon_social',
        'rfc',
        'direccion',
        'telefono',
        'email_facturacion',
        'estatus_credito',
    ];

    protected function casts(): array
    {
        return [
            'estatus_credito' => 'boolean',
        ];
    }

    public function tarifas()
    {
        return $this->hasMany(TarifaCliente::class, 'id_cliente', 'id_cliente');
    }

    public function notasRemision()
    {
        return $this->hasMany(NotaRemision::class, 'id_cliente', 'id_cliente');
    }
}
