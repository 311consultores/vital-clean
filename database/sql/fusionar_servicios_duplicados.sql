-- Fusiona 4 productos duplicados detectados en cat_servicios (mismo
-- producto, dado de alta dos veces con variación de mayúsculas/acentos o
-- singular/plural — probablemente por una importación repetida):
--
--   3  "Toalla de Baño"      <- se conserva (27 tarifas vs 26 en el 236)
--   6  "Mantel"              <- se conserva (5 tarifas vs 4 en el 171)
--   16 "LAVADO DE CASACAS"   <- se conserva (empate 1 a 1, se queda el más antiguo)
--   240 "Toalla de Mano"     <- se conserva (25 tarifas vs solo 1 en el "4 Toalla de Manos")
--
-- Para cada cliente que ya tenía tarifa bajo el duplicado que se borra:
--   - Si NO tenía tarifa bajo el que se conserva, se la reasigna (no pierde
--     el precio pactado).
--   - Si YA tenía tarifa bajo el que se conserva, se descarta la del
--     duplicado (no se puede tener dos tarifas iguales por cliente+servicio
--     — UNIQUE constraint — así que se prioriza la del id que se conserva).
-- Lo mismo para pedidos ya capturados (ope_detalle_remision), por si acaso.
--
-- ⚠️ Antes de correr esto, haz un respaldo (igual que con el script de
-- limpieza de pedidos). Es seguro volver a correrlo dos veces: si ya no
-- quedan filas con el id duplicado, los UPDATE/DELETE simplemente no
-- afectan ninguna fila.

START TRANSACTION;

-- Toalla de Baño: conservar 3, eliminar 236
UPDATE rel_tarifas_cliente rt
SET rt.id_servicio = 3
WHERE rt.id_servicio = 236
  AND NOT EXISTS (SELECT 1 FROM rel_tarifas_cliente rt2 WHERE rt2.id_cliente = rt.id_cliente AND rt2.id_servicio = 3);
DELETE FROM rel_tarifas_cliente WHERE id_servicio = 236;
UPDATE ope_detalle_remision SET id_servicio = 3 WHERE id_servicio = 236;
DELETE FROM cat_servicios WHERE id_servicio = 236;

-- Mantel: conservar 6, eliminar 171
UPDATE rel_tarifas_cliente rt
SET rt.id_servicio = 6
WHERE rt.id_servicio = 171
  AND NOT EXISTS (SELECT 1 FROM rel_tarifas_cliente rt2 WHERE rt2.id_cliente = rt.id_cliente AND rt2.id_servicio = 6);
DELETE FROM rel_tarifas_cliente WHERE id_servicio = 171;
UPDATE ope_detalle_remision SET id_servicio = 6 WHERE id_servicio = 171;
DELETE FROM cat_servicios WHERE id_servicio = 171;

-- Lavado de Casacas: conservar 16, eliminar 163
UPDATE rel_tarifas_cliente rt
SET rt.id_servicio = 16
WHERE rt.id_servicio = 163
  AND NOT EXISTS (SELECT 1 FROM rel_tarifas_cliente rt2 WHERE rt2.id_cliente = rt.id_cliente AND rt2.id_servicio = 16);
DELETE FROM rel_tarifas_cliente WHERE id_servicio = 163;
UPDATE ope_detalle_remision SET id_servicio = 16 WHERE id_servicio = 163;
DELETE FROM cat_servicios WHERE id_servicio = 163;

-- Toalla de Mano(s): conservar 240, eliminar 4
UPDATE rel_tarifas_cliente rt
SET rt.id_servicio = 240
WHERE rt.id_servicio = 4
  AND NOT EXISTS (SELECT 1 FROM rel_tarifas_cliente rt2 WHERE rt2.id_cliente = rt.id_cliente AND rt2.id_servicio = 240);
DELETE FROM rel_tarifas_cliente WHERE id_servicio = 4;
UPDATE ope_detalle_remision SET id_servicio = 240 WHERE id_servicio = 4;
DELETE FROM cat_servicios WHERE id_servicio = 4;

COMMIT;
