-- Limpieza previa a la primera prueba en vivo con el cliente.
--
-- Borra TODO lo operativo (pedidos/notas de remisión, su detalle e
-- incidencias) generado durante las pruebas internas, para que el panel
-- arranque limpio. NO toca catálogos ni usuarios:
--   - cat_clientes, cat_servicios, rel_tarifas_cliente: intactos.
--   - sys_usuarios: intactos (se asume que las cuentas actuales son las
--     reales que usará el equipo).
--
-- Usa DELETE (no TRUNCATE) a propósito: así el contador de folio_sistema
-- NO se reinicia — si quieres que el primer pedido real salga como
-- VC-0001, dilo y te mando la variante que sí lo reinicia.
--
-- ⚠️ IRREVERSIBLE. Haz un respaldo completo de la base de datos desde
-- cPanel → phpMyAdmin → Exportar (o "Respaldo" en cPanel) ANTES de correr
-- esto, por si acaso.

START TRANSACTION;

-- Orden obligatorio por las llaves foráneas: primero las tablas hijas.
DELETE FROM `ope_incidencias`;
DELETE FROM `ope_detalle_remision`;
DELETE FROM `ope_notas_remision`;

COMMIT;
