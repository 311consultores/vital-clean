-- Actualización incremental para bases de datos de producción ya existentes
-- (que ya tienen la columna folio_padre de la entrega parcial/CU-04).
-- Agrega secuencia_subnota, usada por el nuevo esquema de folios VC-000X / SUB-000X-N.
-- Alternativa recomendada: en Terminal (cPanel), `php artisan migrate --force`
-- aplica esta misma migración de forma segura e idempotente.

ALTER TABLE `ope_notas_remision`
  ADD COLUMN IF NOT EXISTS `secuencia_subnota` tinyint(3) unsigned DEFAULT NULL AFTER `folio_padre`;
