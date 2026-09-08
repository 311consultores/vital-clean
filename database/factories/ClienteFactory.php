<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Cliente>
 */
class ClienteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre_comercial' => fake()->unique()->company(),
            'razon_social' => fake()->company().' S.A. de C.V.',
            'rfc' => strtoupper(fake()->unique()->bothify('???######???')),
            'direccion' => fake()->address(),
            'telefono' => fake()->numerify('##########'),
            'email_facturacion' => fake()->unique()->companyEmail(),
            'estatus_credito' => true,
        ];
    }
}
