<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo: ope_detalle_remision (Partidas) (SRS Vital Clean §10.2, RN-02, RN-06)
 */
class DetalleRemision extends Model
{
    protected $table = 'ope_detalle_remision';

    protected $primaryKey = 'id_detalle';

    protected $fillable = [
        'folio_sistema',
        'id_servicio',
        'cantidad_entrada',
        'cantidad_salida',
        'precio_aplicado',
        'subtotal',
        'observacion_prenda',
    ];

    protected function casts(): array
    {
        return [
            'precio_aplicado' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function notaRemision()
    {
        return $this->belongsTo(NotaRemision::class, 'folio_sistema', 'folio_sistema');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'id_servicio', 'id_servicio');
    }

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class, 'id_detalle', 'id_detalle');
    }
}
