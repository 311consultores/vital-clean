-- Agrega los conceptos genéricos "Lavado" y "Desmanche" a cat_servicios (cambio #1).
-- Seguro de re-ejecutar: si la descripción ya existe, actualiza unidad/categoria en vez de duplicar.
-- Requiere que la tabla cat_servicios ya exista (ver vitalclean_schema.sql).

INSERT INTO `cat_servicios` (`descripcion`, `unidad`, `categoria`, `created_at`, `updated_at`) VALUES ('LAVADO', 'PZA', 'Otros', NOW(), NOW()) ON DUPLICATE KEY UPDATE `unidad` = VALUES(`unidad`), `categoria` = VALUES(`categoria`), `updated_at` = NOW();
INSERT INTO `cat_servicios` (`descripcion`, `unidad`, `categoria`, `created_at`, `updated_at`) VALUES ('DESMANCHE', 'PZA', 'Otros', NOW(), NOW()) ON DUPLICATE KEY UPDATE `unidad` = VALUES(`unidad`), `categoria` = VALUES(`categoria`), `updated_at` = NOW();
