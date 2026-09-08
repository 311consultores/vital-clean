<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\TarifaCliente>
 */
class TarifaClienteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_cliente' => Cliente::factory(),
            'id_servicio' => Servicio::factory(),
            'precio_pactado' => fake()->randomFloat(2, 5, 100),
        ];
    }
}
