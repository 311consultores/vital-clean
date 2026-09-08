# Guía de Despliegue — Vital Clean en Banahosting (cPanel)

Esta guía documenta el proceso real usado para desplegar y actualizar Vital
Clean en el hosting compartido de producción, sin acceso a SSH, Git ni
Composer en el servidor. Todo se hace desde el **Administrador de Archivos**
de cPanel.

## 0. Datos del entorno

| Dato | Valor |
|---|---|
| Hosting | Banahosting (cPanel) |
| Panel | `https://bh8924.banahosting.com:2083` |
| Usuario cPanel | `wojttyoo` |
| Dominio | `vitalclean.311consultores.com` |
| Base de datos | `wojttyoo_vitalclean` (MySQL) |
| Carpeta del proyecto | `/home/wojttyoo/vitalclean.311consultores.com/` (el Document Root del subdominio apunta directo aquí; `public/` es la subcarpeta pública) |
| Acceso Terminal/Composer | **Sí disponible** — cPanel tiene un ícono "Terminal" (sección Avanzado). Ver §1.6 |

**Estructura clave:** el código de Laravel (`app/`, `vendor/`, `routes/`,
etc.) vive en una carpeta **fuera** de `public_html` por seguridad. El
Document Root del subdominio apunta a la subcarpeta `public/` de esa
carpeta (`.../vitalclean.311consultores.com/public`), o bien el contenido de `public/` se
copió directamente dentro de `public_html` con las rutas de `index.php`
ajustadas — depende de qué opción hayas usado en el primer despliegue.

---

## 1. Primer despliegue completo (desde cero)

Si alguna vez necesitas reinstalar desde cero (servidor nuevo, dominio
nuevo, etc.):

### 1.1 Subir el código

1. Pide el paquete completo (`vitalclean_deploy.zip`): incluye todo el
   proyecto Laravel con `vendor/` ya compilado (sin dependencias de
   desarrollo), para que **no necesites Composer en el servidor**. No
   incluye `.env` ni `.git`.
2. Sube el zip a tu home (`/home/wojttyoo/`), **no** dentro de `public_html`.
3. Extráelo. Renombra la carpeta resultante a algo identificable, por
   ejemplo `vitalclean.311consultores.com`.

### 1.2 Configurar el Document Root (recomendado si tu plan lo permite)

1. cPanel → **Dominios**, busca `vitalclean.311consultores.com`.
2. Cambia el Document Root a:
   ```
   /home/wojttyoo/vitalclean.311consultores.com/public
   ```

Si tu plan **no** permite cambiar el Document Root, la alternativa es
copiar el *contenido* de `vitalclean.311consultores.com/public/` (no la carpeta, su
contenido: `index.php`, `.htaccess`, `favicon.ico`, `robots.txt`) dentro de
`public_html/`, y editar ese `index.php` copiado para que las dos líneas
que apuntan a `__DIR__.'/../vendor/autoload.php'` y
`__DIR__.'/../bootstrap/app.php'` apunten en cambio a
`__DIR__.'/../vitalclean.311consultores.com/vendor/autoload.php'` y
`__DIR__.'/../vitalclean.311consultores.com/bootstrap/app.php'`.

### 1.3 Configurar `.env`

Dentro de `vitalclean.311consultores.com/`, copia `.env.example` a `.env` y edítalo:

```
APP_NAME="Vital Clean"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://vitalclean.311consultores.com
APP_KEY=                     # ver más abajo cómo generarla

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=wojttyoo_vitalclean
DB_USERNAME=wojttyoo_xxxxx   # tu usuario de MySQL en cPanel
DB_PASSWORD=tu_password_de_mysql

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

**Generar `APP_KEY` sin Composer/artisan:** es una cadena
`base64:` + 32 bytes aleatorios en base64. Pide una nueva la próxima vez
que la necesites, o genera una tú mismo con cualquier herramienta que
tengas a mano (debe ser única por proyecto, no la reutilices).

### 1.4 Base de datos (phpMyAdmin)

1. Crea la base de datos vacía (`wojttyoo_vitalclean`) con collation
   `utf8mb4_unicode_ci`, si no existe ya.
2. Importa, en este orden, los archivos de `database/sql/` del proyecto:
   1. `vitalclean_schema.sql` (o `vitalclean_schema_seed.sql` si quieres
      datos de ejemplo) — crea las 7 tablas de negocio.
   2. `vitalclean_framework_tables.sql` — crea `sessions`, `cache`,
      `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `migrations`
      (tablas internas de Laravel; sin esto verás el error
      *"Base table or view not found: sessions"*).
   3. `insert_productos_aq.sql` (opcional) — catálogo de productos
      adicional; es seguro reimportarlo, no duplica.

### 1.5 Permisos

Con el Administrador de Archivos, dale permisos **775** (recursivo) a:
- `vitalclean.311consultores.com/storage/`
- `vitalclean.311consultores.com/bootstrap/cache/`

### 1.6 Terminal + Composer (¡SÍ disponible! — usar para cambios de dependencias)

Contrario a lo que se asumió al inicio del proyecto, este hosting **sí
tiene Composer accesible vía Terminal** (cPanel → sección Avanzado →
ícono "Terminal"). Esto se descubrió tras un incidente real: instalar una
librería nueva (`dompdf/dompdf`) subiendo manualmente los archivos de
`vendor/` dejó el sitio caído por horas porque el Administrador de
Archivos corrompe/trunca archivos grandes o carpetas con muchos archivos
al extraer zips (ver §4 para el caso completo).

**Para cualquier cambio que agregue o actualice una dependencia de
Composer (una librería nueva en `composer.json`), usa esto en vez de
subir `vendor/` a mano:**

1. Sube (o edita directo con el editor de cPanel) los archivos
   `composer.json` y `composer.lock` actualizados, y el resto del código
   nuevo (`app/`, `routes/`, `resources/views/`) como siempre.
2. Abre **Terminal** desde cPanel.
3. ```
   cd ~/vitalclean.311consultores.com
   composer install --no-interaction
   ```
   (los avisos de "Deprecation Notice" son ruido normal de PHP 8.4 en
   CLI, no son errores — ignóralos).
4. Espera a que termine (puede tardar 1-3 minutos). Debe terminar con
   "Generating optimized autoload files" y la lista de paquetes
   descubiertos, sin "Fatal error" en medio.
5. Limpieza de caché de siempre (sección 3).

Esto reconstruye `vendor/` completo y consistente en un solo paso,
eliminando por completo el riesgo de archivos truncados o carpetas
incompletas por una extracción fallida del Administrador de Archivos.
**Es el método preferido de aquí en adelante** para cualquier
actualización que tenga que ver con `vendor/` — no se necesita volver a
subir zips grandes de dependencias nunca más.

---

## 2. Cómo aplicar actualizaciones de código

Cada vez que se agrega una funcionalidad nueva, el flujo es:

1. Se te entrega un `.zip` con la carpeta `paquete/` que refleja la
   estructura real del proyecto (`app/`, `routes/`, `resources/`, etc.).
2. Sube ese zip a la **raíz de tu carpeta del proyecto**
   (`vitalclean.311consultores.com/`), no dentro de `public_html`.
3. Extráelo ahí mismo.
4. Copia el contenido de `paquete/` hacia las carpetas correspondientes de
   tu proyecto, **sobrescribiendo** los archivos que ya existan. Cuando el
   paquete incluye "todas las vistas" (no solo las nuevas), puedes
   reemplazar toda tu carpeta `resources/views/` sin miedo a dejar algo
   desactualizado.
5. Borra la carpeta `paquete/` y el `.zip` cuando termines.
6. **Limpia la caché (siempre, después de cada actualización — ver
   sección 3).**
7. Prueba el cambio en el navegador.

---

## 3. Limpieza de caché — hazlo después de CADA actualización

Laravel guarda cachés que **no se invalidan solas** cuando reemplazas
archivos manualmente (a diferencia de un despliegue normal con
`php artisan optimize:clear`). Sin esto, tus cambios pueden no verse
reflejados aunque el archivo en disco ya esté actualizado.

### 3.1 Borrar caché de rutas/config/servicios

En el Administrador de Archivos, entra a `vitalclean.311consultores.com/bootstrap/cache/`
y borra **todos los archivos `.php`** que encuentres ahí (`routes-v7.php`,
`packages.php`, `services.php`, `config.php`, etc.). Deja únicamente el
`.gitignore`.

### 3.2 Reiniciar OPcache de PHP

Aunque borres la caché de Laravel, **OPcache de PHP** puede seguir
sirviendo una versión compilada vieja de los archivos `.php` — es la causa
más común de "ya subí el archivo pero sigue fallando igual".

1. Pide (o reutiliza) el archivo `clear-opcache.php`.
2. Súbelo a `public_html/` (junto al `index.php` público del sitio).
3. Visítalo una vez en el navegador:
   `https://vitalclean.311consultores.com/clear-opcache.php`
4. Debe decir "OPcache reiniciado correctamente."
5. **Bórralo del servidor de inmediato** — es de un solo uso; si lo dejas,
   cualquiera podría visitarlo.

---

## 4. Solución de problemas conocidos

Todos estos ya se presentaron y resolvieron durante el desarrollo; se
documentan aquí para no repetir el diagnóstico desde cero.

| Síntoma | Causa | Solución |
|---|---|---|
| **404 genérico del servidor** (no la página 404 de Laravel) | El servidor web no encuentra `index.php` en el Document Root — la carpeta `public/` quedó anidada en vez de ser la raíz | Revisa la sección 1.2: el Document Root debe apuntar directo a `public/`, o el contenido de `public/` debe estar copiado en `public_html/` con `index.php` editado |
| `SQLSTATE[42S02]: Base table or view not found: sessions` | Faltan las tablas internas de Laravel | Importa `vitalclean_framework_tables.sql` (sección 1.4, paso 2) |
| `Route [operaciones.xxx.index] not defined` después de actualizar `routes/web.php` | Caché de rutas vieja en `bootstrap/cache/` u OPcache | Sección 3: borra `bootstrap/cache/*.php` y usa `clear-opcache.php` |
| `View [operaciones.xxx.index] not found` | Faltan archivos de vista, o no se recrearon bien las subcarpetas al copiar manualmente | Verifica que la estructura de carpetas de `resources/views/` en el servidor coincida exactamente con la del paquete; usa "Extraer" del zip directo dentro de `resources/` en vez de copiar archivo por archivo |
| El sitio entero da **"te redireccionó demasiadas veces"** (`ERR_TOO_MANY_REDIRECTS`) | Bug ya corregido: un usuario autenticado visitando `/` o `/login` entraba en un bucle | Asegúrate de tener la versión más reciente de `routes/web.php` (con el chequeo `auth()->check()` en la ruta `/`) |
| Cambios que no se reflejan pese a haber subido el archivo correcto | Casi siempre OPcache | Repite el paso 3.2 |
| **Sitio caído por completo (500 en todo, incluido `/login`)** tras subir manualmente carpetas de `vendor/` para una librería nueva | El Administrador de Archivos corrompe/trunca archivos grandes (`vendor/composer/autoload_classmap.php` puede pesar 600KB+) o deja carpetas de paquetes incompletas al extraer zips grandes. El error aparece en `error_log` (dentro de `public/`, **no** en `storage/logs/laravel.log` — un error tan temprano en el arranque de PHP ocurre antes de que Laravel pueda registrar nada) como `PHP Fatal error: Uncaught Error: Failed opening required '.../vendor/<algún-paquete>/...'` | **No subas `vendor/` a mano.** Usa Terminal + `composer install` (sección 1.6) — reconstruye todo el árbol de dependencias de una sola vez, sin depender de que la extracción de un zip haya sido perfecta |

---

## 5. Checklist rápido para cada actualización

- [ ] Subir y extraer el `.zip` de la actualización en la raíz del proyecto
      (no en `public_html`)
- [ ] Copiar/sobrescribir los archivos indicados
- [ ] Borrar `bootstrap/cache/*.php` (dejar `.gitignore`)
- [ ] Subir `clear-opcache.php` a `public_html/`, visitarlo, borrarlo
- [ ] Probar el cambio en el navegador (idealmente en incógnito, para
      descartar caché del navegador)

## 6. Usuarios de prueba

| Usuario | Rol | Password |
|---|---|---|
| `admin.vitalclean` | ADMIN | `password` |
| `vendedor.vitalclean` | VENDEDOR | `password` |
| `operador.vitalclean` | OPERADOR | `password` |

> Cámbialas antes de dar acceso real a producción.
