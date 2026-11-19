# Arquitectura

## Tecnologías

- PHP 8.2 con Apache.
- Twig 3.
- MySQL 8.4.
- Composer.
- Docker Compose.
- HTML, CSS y JavaScript.

Hay dos servicios: `app` contiene PHP y Apache, y `db` contiene MySQL.

## Estructura

```text
backend/                 PHP y configuración
frontend/                Plantillas, estilos, JavaScript e imágenes
docker/mysql/init/       Inicialización de MySQL
docker/php/              Dockerfile y configuración Apache
docs/                    Documentación
docker-compose.yml       Servicios y volúmenes
.env                     Valores locales
.env.example             Plantilla pública
```

## Flujo de una petición

1. Apache recibe la URL.
2. `.htaccess` conserva rutas de archivos reales y dirige las rutas
   desconocidas a `backend/index.php`.
3. PHP carga configuración, sesión, Twig y MySQL.
4. El primer segmento de la URL selecciona la página o el controlador.
5. Los controladores consultan o modifican la base de datos.
6. Twig genera la respuesta HTML.

Ejemplos de rutas:

- `/portada`
- `/pelicula/123`
- `/usuarios`
- `/commentList`

## Archivos principales del backend

- `index.php`: controlador central.
- `config.php`: lectura de variables de entorno.
- `paths.php`: rutas públicas del frontend.
- `bdUtils.php`: usuarios, autenticación y funciones auxiliares.
- `pelicula.php`: películas.
- `logIn.php`: inicio de sesión y perfil.
- `usuarios.php`: administración de usuarios.
- `ajax_peliculas.php`: búsqueda de películas en JSON.
- `ajax_comment.php`: comentarios en JSON.
- `setup-admin.php`: creación opcional del primer superusuario.

## Frontend

El frontend está dentro de `/var/www/html/assets/`, por lo que Apache sirve sus
recursos directamente desde `/assets/` sin configuración de alias.

Las plantillas Twig usan datos de PHP y una configuración global con rutas,
usuario actual, imágenes compartidas y el token CSRF.
