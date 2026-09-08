# Vital Clean — Sistema de Gestión Operativa

Aplicación Laravel para la gestión operativa de la lavandería industrial Vital Clean
(Mérida, Yucatán). Basada en la Especificación de Requerimientos del Sistema (SRS) v1.1.

## Base de datos

El esquema implementa las 7 tablas descritas en el diccionario de datos del SRS (§10):

| Tabla | Descripción |
|---|---|
| `sys_usuarios` | Accesos al sistema por perfil (VENDEDOR, OPERADOR, ADMIN). |
| `cat_clientes` | Catálogo de hoteles/restaurantes clientes. |
| `cat_servicios` | Catálogo de tipos de prenda/servicio. |
| `rel_tarifas_cliente` | Precio pactado por contrato (cliente + servicio). |
| `ope_notas_remision` | Cabecera de cada folio de recolección/entrega. |
| `ope_detalle_remision` | Conteo real por prenda, ligado a un folio. |
| `ope_incidencias` | Evidencia fotográfica de daños por prenda. |

Las migraciones están en `database/migrations/` y reflejan las reglas de negocio
del SRS (RN-01 a RN-07): precios por contrato, congelamiento del precio histórico,
bloqueo de conteo, bloqueo por crédito suspendido, folio físico obligatorio,
integridad referencial (`ON DELETE RESTRICT`) y flujo de estados secuencial.

### Opción A — Levantar el proyecto con Laravel (recomendado)

1. Copia `.env.example` a `.env` y ajusta `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
   a tu configuración local de MySQL/phpMyAdmin.
2. Crea la base de datos vacía en phpMyAdmin (o `CREATE DATABASE vitalclean;`).
3. Corre:

   ```bash
   composer install
   php artisan key:generate
   php artisan migrate --seed
   ```

   Esto crea las tablas y carga datos de catálogo de ejemplo (usuarios demo,
   servicios/prendas base y un cliente de ejemplo con tarifas).

### Opción B — Importar directamente en phpMyAdmin

Si solo necesitas subir la base de datos sin correr el proyecto en local:

1. Crea una base de datos vacía en phpMyAdmin (collation `utf8mb4_unicode_ci`).
2. Ve a la pestaña **Importar** y selecciona uno de estos archivos:
   - `database/sql/vitalclean_schema.sql` — solo estructura (tablas vacías).
   - `database/sql/vitalclean_schema_seed.sql` — estructura + datos de ejemplo.

Ambos archivos fueron generados y probados contra MySQL/MariaDB (import limpio
verificado). Reflejan exactamente lo que producen las migraciones de Laravel.

### Catálogo de productos (LISTA_PRODUCTOS.xlsx)

`database/sql/insert_productos_aq.sql` carga 62 productos adicionales en
`cat_servicios` (tomados de un catálogo de cliente entregado en Excel, sin
unidad/categoría explícitas). Se clasificaron así:

- `unidad`: `PZA` por defecto; `KG` para servicios a granel (limpiones,
  blancos hospitalarios, mantelería/lavandería por servicio).
- `categoria`: `Hotelería`, `Restaurante`, `Uniformes` u `Otros`, según el
  tipo de prenda.

Es seguro reimportarlo: usa `INSERT ... ON DUPLICATE KEY UPDATE` sobre
`descripcion` (que ahora tiene restricción `UNIQUE`), así que no duplica
productos si se corre más de una vez. Impórtalo desde phpMyAdmin igual que
los archivos anteriores, después de tener la tabla `cat_servicios` creada.
Equivalente en Laravel: `php artisan db:seed --class=CatServiciosAqSeeder`.

## Usuarios de prueba (seeder)

| Usuario | Rol | Password |
|---|---|---|
| `admin.vitalclean` | ADMIN | `password` |
| `vendedor.vitalclean` | VENDEDOR | `password` |
| `operador.vitalclean` | OPERADOR | `password` |

> Cambia estas credenciales antes de usar el sistema en producción.

## Pendientes conocidos (ver hallazgos de contradicciones del SRS v1.1)

- El SRS define un actor "Operador de Producción" y un módulo de Control de
  Producción sin mockup ni pantallas de captura definidas todavía.
- La paleta de colores del SRS principal (§12) no coincide con la del Anexo
  de la app móvil — pendiente de definir una paleta única antes de maquetar.
- El campo `folio_fisico` es obligatorio por regla de negocio (RN-05) pero no
  aparece como campo explícito en el flujo de 10 pantallas del Anexo App;
  revisar con el cliente antes de implementar el formulario de recolección.
