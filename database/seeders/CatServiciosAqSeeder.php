<?php

namespace Database\Seeders;

use App\Models\Servicio;
use Illuminate\Database\Seeder;

/**
 * Catálogo de productos "AQ" (LISTA_PRODUCTOS.xlsx) -> cat_servicios.
 *
 * El archivo original solo trae el nombre del producto; unidad y categoría
 * se asignaron manualmente (unidad=PZA por defecto salvo servicios a granel
 * facturados por KG; categoría clasificada como Hotelería/Restaurante/
 * Uniformes/Otros según el tipo de prenda).
 */
class CatServiciosAqSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->productos() as [$descripcion, $unidad, $categoria]) {
            Servicio::updateOrCreate(
                ['descripcion' => $descripcion],
                ['unidad' => $unidad, 'categoria' => $categoria]
            );
        }
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: string}>
     */
    protected function productos(): array
    {
        return [
            ['COSTURA TAPETE', 'PZA', 'Otros'],
            ['COSTURA TOALLA BAÑO', 'PZA', 'Otros'],
            ['LAVADO DE ALMOHADA', 'PZA', 'Hotelería'],
            ['LAVADO DE ALMOHADA DE PLUMA', 'PZA', 'Hotelería'],
            ['LAVADO DE BAMBALINA', 'PZA', 'Hotelería'],
            ['LAVADO DE BANDERA', 'PZA', 'Otros'],
            ['LAVADO DE BATA DE BAÑO', 'PZA', 'Hotelería'],
            ['LAVADO DE CAMINO', 'PZA', 'Restaurante'],
            ['LAVADO DE CASACAS', 'PZA', 'Uniformes'],
            ['LAVADO DE CHAMARRA', 'PZA', 'Uniformes'],
            ['LAVADO DE COBERTOR', 'PZA', 'Hotelería'],
            ['LAVADO DE CORTINA', 'PZA', 'Hotelería'],
            ['LAVADO DE CUBRECHAROLA', 'PZA', 'Restaurante'],
            ['LAVADO DE CUBRECOLCHON', 'PZA', 'Hotelería'],
            ['LAVADO DE CUBREMANTEL', 'PZA', 'Restaurante'],
            ['LAVADO DE CUBRESILLA', 'PZA', 'Restaurante'],
            ['LAVADO DE DUVET', 'PZA', 'Hotelería'],
            ['LAVADO DE EDREDON', 'PZA', 'Hotelería'],
            ['LAVADO DE FUNDA DE BURRO', 'PZA', 'Otros'],
            ['LAVADO DE FUNDA DE COJIN', 'PZA', 'Hotelería'],
            ['LAVADO DE FUNDA DECORADA', 'PZA', 'Hotelería'],
            ['LAVADO DE FUNDAS', 'PZA', 'Hotelería'],
            ['LAVADO DE FUNDAS KING', 'PZA', 'Hotelería'],
            ['LAVADO DE GORRO', 'PZA', 'Uniformes'],
            ['LAVADO DE INSERTO', 'PZA', 'Restaurante'],
            ['LAVADO DE LAZO', 'PZA', 'Otros'],
            ['LAVADO DE LIMPION', 'KG', 'Restaurante'],
            ['LAVADO DE MANTEL CHICO', 'PZA', 'Restaurante'],
            ['LAVADO DE MANTEL DE FELPA', 'PZA', 'Restaurante'],
            ['LAVADO DE MANTEL GRANDE', 'PZA', 'Restaurante'],
            ['LAVADO DE MANTEL MEDIANO', 'PZA', 'Restaurante'],
            ['LAVADO DE MANTEL REDONDO', 'PZA', 'Restaurante'],
            ['LAVADO MANTEL TABLON', 'PZA', 'Restaurante'],
            ['LAVADO DE PANTUFLAS', 'PZA', 'Hotelería'],
            ['LAVADO DE PROTECTOR DE ALMOHADA', 'PZA', 'Hotelería'],
            ['LAVADO DE PROTECTOR DE COLCHON', 'PZA', 'Hotelería'],
            ['LAVADO DE RODAPIE', 'PZA', 'Hotelería'],
            ['LAVADO DE SABANAS', 'PZA', 'Hotelería'],
            ['LAVADO SABANA KING', 'PZA', 'Hotelería'],
            ['LAVADO DE SERVILLETAS', 'PZA', 'Restaurante'],
            ['LAVADO DE SOBRECAMA', 'PZA', 'Hotelería'],
            ['LAVADO DE TAPETE DE BAÑO', 'PZA', 'Hotelería'],
            ['LAVADO DE TERNOS', 'PZA', 'Uniformes'],
            ['LAVADO DE TOALLA CAFE', 'PZA', 'Hotelería'],
            ['LAVADO DE TOALLA DE ALBERCA', 'PZA', 'Hotelería'],
            ['LAVADO DE TOALLA DE BAÑO', 'PZA', 'Hotelería'],
            ['LAVADO DE TOALLA DE BAÑO CHICA', 'PZA', 'Hotelería'],
            ['LAVADO DE TOALLA DE BAÑO GRANDE', 'PZA', 'Hotelería'],
            ['LAVADO DE TOALLA DE MANO', 'PZA', 'Hotelería'],
            ['LAVADO DE TOALLA FACIAL', 'PZA', 'Hotelería'],
            ['LAVADO DE TORTILLERAS', 'PZA', 'Restaurante'],
            ['LAVADO DE UNIFORMES', 'PZA', 'Uniformes'],
            ['LAVADO FUNDA SILLON', 'PZA', 'Hotelería'],
            ['LAVADO MANDIL', 'PZA', 'Uniformes'],
            ['LAVADO PROTECTOR CUNA', 'PZA', 'Hotelería'],
            ['LAVANDERIA DE BLANCOS HOSPITALARIOS', 'KG', 'Otros'],
            ['LAVADO DE PANERA', 'PZA', 'Restaurante'],
            ['SERVICIO DE DESMANCHE', 'PZA', 'Otros'],
            ['SERVICIO DE LAVADO DE MANTELERIA', 'KG', 'Restaurante'],
            ['SERVICIO DE LAVANDERIA', 'KG', 'Otros'],
            ['SERVICIO DE LAVANDERIA INTEGRAL', 'KG', 'Otros'],
            ['SERVICIO DE TINTORERIA', 'PZA', 'Otros'],
        ];
    }
}
