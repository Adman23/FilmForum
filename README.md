# FilmForum

Aplicación PHP/Twig para gestionar películas, usuarios y comentarios.

> Proyecto de práctica publicado con fines educativos. No está destinado a producción ni se ofrece con garantía de funcionamiento o seguridad.

## Documentación

Consulta la [documentación del proyecto](docs/README.md).

## Lanzamiento con Docker

Requisitos: Docker y Docker Compose.

```bash
cp .env.example .env
docker compose up -d --build
```

La aplicación queda disponible en <http://localhost:8080/>.

No se crea una cuenta administrativa conocida. Configura `DB_PASSWORD`
con un secreto propio antes de iniciar MySQL.

Para crear automáticamente el primer superusuario, define en `.env`:

```text
ADMIN_USERNAME=mi_admin
ADMIN_PASSWORD=una_clave_privada
ADMIN_EMAIL=mi_correo@ejemplo.com
```

Al arrancar `app`, [backend/setup-admin.php](/home/Adnam23/Atlas/dev/personal_proyects/FilmForum_repo/backend/setup-admin.php)
crea la cuenta o actualiza una existente a `superuser`. Deja las tres variables
vacías para omitir el setup.

La configuración está en `.env`. El código PHP está en `backend/` y las plantillas, hojas de estilo, JavaScript e imágenes están en `frontend/`.

## Base de datos

La imagen oficial de MySQL crea la base indicada por `DB_NAME` en `.env` y ejecuta, en orden:

1. `docker/mysql/init/schema.sql`: tablas, índices y relaciones.
2. `docker/mysql/init/seed.sql`: películas e imágenes iniciales.

La base de datos se persiste en un volumen Docker gestionado por Compose.
Con la configuración actual se llama `filmforum_mysql_data`.

Reiniciar o recrear los contenedores no borra los datos:

```bash
docker compose down
docker compose up -d
```

Los scripts de `docker/mysql/init/` solo se ejecutan cuando el volumen MySQL está vacío. Para borrar completamente los datos y volver a cargar los valores iniciales:

```bash
docker compose down -v
docker compose up -d --build
```

`down -v` elimina permanentemente el volumen de desarrollo.

## Copia de seguridad

Para exportar el estado actual:

```bash
docker compose exec db sh -c \
  'exec mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' > backup.sql
```

Para restaurarlo:

```bash
docker compose exec -T db sh -c \
  'exec mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' < backup.sql
```
