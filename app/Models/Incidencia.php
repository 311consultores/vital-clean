<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo: ope_incidencias (SRS Vital Clean §10.3)
 */
class Incidencia extends Model
{
    protected $table = 'ope_incidencias';

    protected $primaryKey = 'id_incidencia';

    protected $fillable = [
        'id_detalle',
        'foto_evidencia',
        'comentario',
    ];

    public function detalle()
    {
        return $this->belongsTo(DetalleRemision::class, 'id_detalle', 'id_detalle');
    }
}
