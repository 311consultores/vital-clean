<?php

namespace App\Models;

use Database\Factories\TarifaClienteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo: rel_tarifas_cliente (SRS Vital Clean §10.1, RN-01, RN-02)
 */
class TarifaCliente extends Model
{
    /** @use HasFactory<TarifaClienteFactory> */
    use HasFactory;

    protected $table = 'rel_tarifas_cliente';

    protected $primaryKey = 'id_tarifa';

    protected $fillable = [
        'id_cliente',
        'id_servicio',
        'precio_pactado',
    ];

    protected function casts(): array
    {
        return [
            'precio_pactado' => 'decimal:2',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'id_servicio', 'id_servicio');
    }
}
