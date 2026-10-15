-- Agrega la tabla idempotency_keys, usada por el nuevo "envío resiliente a
-- conexión intermitente" de Nuevo Pedido y Cierre de Entrega (ver
-- public/js/envio-resiliente.js y App\Support\Idempotencia): evita que un
-- reintento automático, cuando la respuesta del primer intento se perdió
-- por la señal, duplique el pedido o la entrega.
--
-- Alternativa recomendada: en Terminal (cPanel), `php artisan migrate --force`
-- aplica esta misma migración de forma segura e idempotente.

CREATE TABLE IF NOT EXISTS `idempotency_keys` (
  `clave` varchar(64) NOT NULL,
  `id_usuario` bigint(20) unsigned NOT NULL,
  `ruta` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`clave`),
  KEY `idempotency_keys_id_usuario_foreign` (`id_usuario`),
  CONSTRAINT `idempotency_keys_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `sys_usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
