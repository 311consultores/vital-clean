<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\TarifaCliente;
use App\Models\Usuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database con datos base de Vital Clean.
     */
    public function run(): void
    {
        $admin = Usuario::factory()->create([
            'username' => 'admin.vitalclean',
            'nombre_completo' => 'Administrador Vital Clean',
            'email' => 'admin@vitalclean.mx',
            'rol' => 'ADMIN',
        ]);

        $vendedor = Usuario::factory()->create([
            'username' => 'vendedor.vitalclean',
            'nombre_completo' => 'Vendedor Ruta 1',
            'email' => 'vendedor@vitalclean.mx',
            'rol' => 'VENDEDOR',
        ]);

        Usuario::factory()->create([
            'username' => 'operador.vitalclean',
            'nombre_completo' => 'Operador de Planta',
            'email' => 'operador@vitalclean.mx',
            'rol' => 'OPERADOR',
        ]);

        // cat_servicios: catálogo base de prendas (§10.1)
        $servicios = collect([
            ['descripcion' => 'Sábana King Size', 'unidad' => 'PZA', 'categoria' => 'Hotelería'],
            ['descripcion' => 'Sábana Queen', 'unidad' => 'PZA', 'categoria' => 'Hotelería'],
            ['descripcion' => 'Toalla de Baño', 'unidad' => 'PZA', 'categoria' => 'Hotelería'],
            ['descripcion' => 'Toalla de Manos', 'unidad' => 'PZA', 'categoria' => 'Hotelería'],
            ['descripcion' => 'Funda de Almohada', 'unidad' => 'PZA', 'categoria' => 'Hotelería'],
            ['descripcion' => 'Mantel', 'unidad' => 'KG', 'categoria' => 'Restaurante'],
            ['descripcion' => 'Uniforme Personal', 'unidad' => 'PZA', 'categoria' => 'Uniformes'],
        ])->map(fn (array $s) => Servicio::create($s));

        // cat_clientes: ejemplo tomado del anexo de diseño del panel web
        $cliente = Cliente::create([
            'nombre_comercial' => 'Grand Hotel de Mérida',
            'razon_social' => 'Grand Hotel de Mérida S.A. de C.V.',
            'rfc' => 'GHM850101XYZ',
            'direccion' => 'Calle 60 #450, Centro, Mérida, Yucatán, C.P. 97000',
            'telefono' => '9997808557',
            'email_facturacion' => 'facturacion@grandhotelmerida.com.mx',
            'estatus_credito' => true,
        ]);

        // rel_tarifas_cliente: precio pactado por contrato para cada servicio
        $servicios->each(fn (Servicio $servicio) => TarifaCliente::create([
            'id_cliente' => $cliente->id_cliente,
            'id_servicio' => $servicio->id_servicio,
            'precio_pactado' => fake()->randomFloat(2, 5, 60),
        ]));

        $this->call(CatServiciosAqSeeder::class);
    }
}
