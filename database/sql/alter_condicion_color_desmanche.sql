-- Agrega las columnas de los ajustes de última hora (nueva/usada, color,
-- desmanche) a bases de datos ya desplegadas, sin necesitar
-- `php artisan migrate` (por si el hosting no tiene Terminal/Composer
-- disponible ese día). Seguro de reimportar: IF NOT EXISTS no duplica.

ALTER TABLE `ope_detalle_remision`
  ADD COLUMN IF NOT EXISTS `condicion_prenda` enum('nueva','usada') DEFAULT NULL AFTER `id_servicio`,
  ADD COLUMN IF NOT EXISTS `color` varchar(50) DEFAULT NULL AFTER `condicion_prenda`,
  ADD COLUMN IF NOT EXISTS `es_desmanche` tinyint(1) NOT NULL DEFAULT 0 AFTER `color`;

ALTER TABLE `cat_servicios`
  ADD COLUMN IF NOT EXISTS `requiere_color` tinyint(1) NOT NULL DEFAULT 0 AFTER `categoria`;
