<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Servicio>
 */
class ServicioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'descripcion' => fake()->unique()->words(3, true),
            'unidad' => fake()->randomElement(['PZA', 'KG']),
            'categoria' => fake()->randomElement(['Hotelería', 'Restaurante', 'Uniformes', 'Otros']),
        ];
    }
}
