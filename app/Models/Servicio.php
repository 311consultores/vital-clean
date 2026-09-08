<?php

namespace App\Models;

use Database\Factories\ServicioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo: cat_servicios (SRS Vital Clean §10.1)
 */
class Servicio extends Model
{
    /** @use HasFactory<ServicioFactory> */
    use HasFactory;

    protected $table = 'cat_servicios';

    protected $primaryKey = 'id_servicio';

    protected $fillable = [
        'descripcion',
        'unidad',
        'categoria',
    ];

    public function tarifas()
    {
        return $this->hasMany(TarifaCliente::class, 'id_servicio', 'id_servicio');
    }
}
