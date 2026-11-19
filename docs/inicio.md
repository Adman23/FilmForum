# Inicio

## Requisitos

- Docker.
- Docker Compose.

## Primer arranque

```bash
cp .env.example .env
docker compose up -d --build
```

La aplicación estará disponible en `http://localhost:8080` por defecto.
Si cambias `APP_PORT`, utiliza el puerto elegido.

## Variables necesarias

Configura al menos:

```text
DB_NAME=FILM_DB
DB_ROOT_PASSWORD=contraseña-root
DB_USER=filmforum
DB_PASSWORD=contraseña-aplicación
APP_PORT=8080
```

Las variables `ADMIN_*` pueden quedar vacías si no se quiere crear el primer
administrador automáticamente.

## Uso diario

Los directorios `backend/` y `frontend/` están montados en el contenedor,
por lo que sus cambios suelen verse sin reconstruir la imagen.

- PHP: `backend/`.
- Plantillas: `frontend/plantillas/`.
- CSS y JavaScript: `frontend/css/` y `frontend/js/`.

## Comandos básicos

```bash
docker compose ps
docker compose logs -f app
docker compose up -d --build
docker compose down
```

Para borrar también los datos MySQL:

```bash
docker compose down -v
docker compose up -d --build
```

Esta última operación elimina permanentemente las películas, usuarios y
comentarios almacenados en el volumen.
