<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo: ope_notas_remision (Cabecera) (SRS Vital Clean §10.2)
 */
class NotaRemision extends Model
{
    protected $table = 'ope_notas_remision';

    protected $primaryKey = 'folio_sistema';

    protected $fillable = [
        'folio_fisico',
        'folio_padre',
        'secuencia_subnota',
        'id_cliente',
        'id_vendedor',
        'fecha_recoleccion',
        'fecha_entrega_prog',
        'estatus_orden',
        'firma_cliente',
        'firma_entrega',
        'geolocalizacion',
        'conteo_bloqueado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_recoleccion' => 'datetime',
            'fecha_entrega_prog' => 'date',
            'conteo_bloqueado' => 'boolean',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function vendedor()
    {
        return $this->belongsTo(Usuario::class, 'id_vendedor', 'id_usuario');
    }

    public function detalle()
    {
        return $this->hasMany(DetalleRemision::class, 'folio_sistema', 'folio_sistema');
    }

    /**
     * Folio original del que se desprendió esta subnota (entrega parcial,
     * CU-04) — null si este folio nunca fue una subnota.
     */
    public function padre()
    {
        return $this->belongsTo(self::class, 'folio_padre', 'folio_sistema');
    }

    /**
     * Subnotas generadas a partir de este folio por entregas parciales
     * sucesivas (una o varias, si la mercancía se entrega en más de dos
     * partes).
     */
    public function subnotas()
    {
        return $this->hasMany(self::class, 'folio_padre', 'folio_sistema');
    }

    public function getRouteKeyName(): string
    {
        return 'folio_sistema';
    }

    /**
     * Folio visible: el sistema lo autogenera, ya no se captura a mano
     * (antes RN-05 exigía el folio de papel). Un folio raíz se ve
     * "VC-0002"; una subnota reutiliza el número del folio raíz con
     * prefijo "SUB-" y la cadena de posiciones desde la raíz hasta este
     * nodo — "SUB-0002-1" (primera subnota de VC-0002), "SUB-0002-1-1"
     * (subnota de esa subnota), etc. — para que nunca haya ambigüedad
     * aunque un folio se entregue en varias partes o una subnota se
     * vuelva a entregar parcialmente.
     */
    public function getFolioDisplayAttribute(): string
    {
        if ($this->folio_padre === null) {
            return 'VC-'.str_pad((string) $this->folio_sistema, 4, '0', STR_PAD_LEFT);
        }

        $cadena = [];
        $nodo = $this;

        while ($nodo->folio_padre !== null) {
            $cadena[] = $nodo->secuencia_subnota;
            $nodo = $nodo->relationLoaded('padre') ? $nodo->padre : $nodo->padre()->first();
        }

        return 'SUB-'.str_pad((string) $nodo->folio_sistema, 4, '0', STR_PAD_LEFT).'-'.implode('-', array_reverse($cadena));
    }

    /**
     * Prioridad para el Monitor de Órdenes (SRS §11, sistema de alertas),
     * simplificada a lo que hoy se puede derivar de los datos existentes:
     * Alta = pedido vencido; Media = vence hoy; Baja = todo lo demás.
     * No incluye aún incidencias sin validar (ese módulo no existe todavía).
     */
    public function getPrioridadAttribute(): string
    {
        if (in_array($this->estatus_orden, ['ENTREGADO', 'CANCELADO'], true) || ! $this->fecha_entrega_prog) {
            return 'baja';
        }

        $hoy = now()->startOfDay();

        if ($this->fecha_entrega_prog->lt($hoy)) {
            return 'alta';
        }

        if ($this->fecha_entrega_prog->isSameDay($hoy)) {
            return 'media';
        }

        return 'baja';
    }
}
