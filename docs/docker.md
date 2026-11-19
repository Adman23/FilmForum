# Docker y entorno

## Servicios

### Base de datos

El servicio `db` usa `mysql:8.4`.

- Los datos persisten en el volumen `mysql_data`.
- Los scripts SQL se montan en `/docker-entrypoint-initdb.d/`.
- El puerto no se publica al sistema anfitrión.
- PHP accede mediante el nombre interno `db:3306`.

### Aplicación

El servicio `app` usa PHP 8.2 con Apache y contiene:

- MySQLi.
- `mod_rewrite`.
- Composer.
- Twig.

Apache publica `APP_PORT` hacia el puerto 80 del contenedor.

## Volúmenes de desarrollo

Compose monta:

```yaml
- ./backend:/var/www/html
- backend_vendor:/var/www/html/vendor
- ./frontend:/var/www/html/assets
```

El volumen `backend_vendor` conserva las dependencias de Composer. Sin él,
montar `./backend` podría ocultar el directorio `vendor/` generado al construir
la imagen.

## Variables

Obligatorias:

```text
DB_ROOT_PASSWORD
DB_USER
DB_PASSWORD
```

Si están vacías o no existen, Compose no inicia los servicios.

Con valores predeterminados:

```text
DB_NAME=FILM_DB
APP_PORT=8080
```

Variables opcionales del superusuario:

```text
ADMIN_USERNAME
ADMIN_PASSWORD
ADMIN_EMAIL
```

El setup se ejecuta solamente si las tres tienen contenido.

## Healthcheck

MySQL se considera disponible después de que `mysqladmin ping` responda
correctamente.

El contenedor `app` espera al estado `service_healthy` antes de arrancar.

## Comandos útiles

```bash
docker compose ps
docker compose logs -f app
docker compose logs -f db
docker compose exec app composer install
docker compose exec db mysql -uroot -p
```
