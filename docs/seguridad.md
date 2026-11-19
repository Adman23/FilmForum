# Seguridad y administración

## Secretos

No se debe publicar `.env`, contraseñas reales, copias de seguridad, logs ni
claves privadas.

Usa contraseñas diferentes para:

- `DB_ROOT_PASSWORD`.
- `DB_PASSWORD`.
- `ADMIN_PASSWORD`.

El archivo `.env.example` contiene únicamente nombres y valores vacíos o
predeterminados seguros.

## Primer superusuario

Configura las tres variables:

```text
ADMIN_USERNAME=mi_admin
ADMIN_PASSWORD=contraseña-segura
ADMIN_EMAIL=correo@ejemplo.com
```

Al arrancar `app`, `backend/setup-admin.php` espera a MySQL y:

- crea el usuario si no existe;
- cifra la contraseña con `password_hash()`;
- asigna el rol `superuser`;
- actualiza una cuenta existente si el nombre coincide.

Deja las tres variables vacías después del primer arranque para evitar que la
contraseña vuelva a cambiar en reinicios posteriores.

## Asignar moderadores

Inicia sesión como superusuario y abre `/usuarios`.

Desde esa lista se pueden asignar los roles:

- `registered`
- `moderator`
- `manager`
- `superuser`

La interfaz oculta opciones según el rol, pero PHP también debe comprobar los
permisos: ocultar un botón no impide una petición directa.

## Consultas SQL

Las consultas con datos del usuario deben usar sentencias preparadas:

```php
$stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
$stmt->bind_param("s", $username);
```

Así se separan las instrucciones SQL de los valores recibidos.

## CSRF

CSRF impide que otro sitio provoque una acción utilizando la sesión del usuario.

El proceso es:

1. PHP crea un token aleatorio por sesión.
2. Twig lo incluye en formularios o atributos de datos.
3. El servidor compara el token recibido con el almacenado.
4. Si no coincide, responde con HTTP 403.

Las operaciones críticas deben comprobar también autenticación y rol.

## Sesiones

La aplicación configura cookies HttpOnly y SameSite. HTTPS activa también
Secure.

Después de iniciar sesión se llama a `session_regenerate_id(true)` para evitar
que la sesión anterior siga utilizándose.
