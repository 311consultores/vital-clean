<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Protege contra duplicados cuando un envío (firma de Nuevo Pedido o de
 * Cierre de Entrega) se reintenta tras perder la respuesta original —
 * típicamente porque la señal se cortó justo cuando el servidor ya había
 * procesado todo, pero la confirmación nunca llegó a la tableta (ver
 * public/js/envio-resiliente.js, que reintenta guardando la misma clave).
 *
 * Debe usarse DENTRO de la misma transacción que hace el trabajo real: si
 * esa transacción falla por otra razón (ej. un error de validación), la
 * clave no queda registrada y un reintento legítimo puede seguir
 * procesándose con normalidad.
 */
class Idempotencia
{
    /**
     * Reclama la clave de forma atómica (INSERT con la clave como llave
     * primaria: dos peticiones con la misma clave no pueden reclamarla las
     * dos, sin necesidad de un lock manual).
     *
     * @return bool true si esta llamada la reclamó (hay que hacer el
     *              trabajo real); false si ya estaba reclamada — por un
     *              reintento ya exitoso antes, o por una petición
     *              concurrente idéntica que llegó primero — y no debe
     *              repetirse el trabajo.
     */
    public static function reclamar(?string $clave, int $idUsuario, string $ruta): bool
    {
        if (! $clave) {
            return true;
        }

        $filas = DB::table('idempotency_keys')->insertOrIgnore([
            'clave' => $clave,
            'id_usuario' => $idUsuario,
            'ruta' => $ruta,
            'created_at' => now(),
        ]);

        return $filas > 0;
    }
}
