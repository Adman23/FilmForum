# Base de datos

## Inicialización

MySQL ejecuta los archivos de `docker/mysql/init/` cuando el volumen está vacío.

El orden previsto es:

1. `schema.sql`: crea las tablas.
2. `seed.sql`: carga las películas e imágenes iniciales.

No se incluye un usuario administrativo conocido en el seed.

## Tablas

### `user`

Contiene nombre de usuario, contraseña cifrada, rol, correo y edad.

Los roles son `superuser`, `manager`, `moderator` y `registered`.

### `film`

Contiene nombre, año, género, directores, actores, descripción, hashtags y
estado de publicación.

### `image`

Contiene imágenes binarias relacionadas con películas. Puede representar
portadas, imágenes adicionales o recursos compartidos.

### `comment`

Contiene texto, autor, fecha, película y estado de edición.

### `banned_words`

Contiene las palabras que el JavaScript utiliza para filtrar comentarios.

## Volumen y reinicio

`docker compose down` conserva los datos.

`docker compose down -v` elimina el volumen y todos los datos almacenados.
Al volver a arrancar se ejecutarán los scripts de inicialización.

## Copia de seguridad

Exportar:

```bash
docker compose exec db sh -c \
  'exec mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' > backup.sql
```

Restaurar:

```bash
docker compose exec -T db sh -c \
  'exec mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < backup.sql
```
