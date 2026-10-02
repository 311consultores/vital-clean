<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * Búsqueda "por aproximación": separa el texto escrito en palabras y exige
 * que TODAS aparezcan en alguno de los campos indicados, sin importar el
 * orden en que se escribieron (ej. "Aluxes" encuentra "Los Aluxes", y
 * "Los Aluxes" encuentra "Aluxes Los" si alguien lo escribiera al revés).
 */
class Busqueda
{
    /**
     * @param  string[]  $campos  Columnas candidatas; basta con que una de
     *                            ellas contenga todas las palabras.
     */
    public static function porPalabras(Builder $query, array $campos, ?string $texto): Builder
    {
        $palabras = preg_split('/\s+/', trim((string) $texto), -1, PREG_SPLIT_NO_EMPTY);

        if (! $palabras) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($campos, $palabras) {
            foreach ($campos as $campo) {
                $q->orWhere(function (Builder $q2) use ($campo, $palabras) {
                    foreach ($palabras as $palabra) {
                        $q2->where($campo, 'like', '%'.$palabra.'%');
                    }
                });
            }
        });
    }
}
