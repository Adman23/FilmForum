<?php
function csrfToken(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): bool {
    $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return is_string($token) && isset($_SESSION['csrf_token']) &&
        hash_equals($_SESSION['csrf_token'], $token);
}

function requireCsrf(): void {
    if (!verifyCsrf()) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid CSRF token']);
        exit();
    }
}

function requireRole(array $roles, ?array $user): bool {
    if (!$user || !isset($user['role']) || !in_array($user['role'], $roles, true)) {
        http_response_code(403);
        echo '<h1>403 Forbidden</h1>';
        exit();
    }
    return true;
}

// Cuando se usa get_user se entiende que se ha hecho checkLogin y guardado el usuario en la sesión
// en caso de algún error lanza un error 500 porque no debería de haber problema
function getUser($username, $conn) {
    // Obtenemos el usuario de la base de datos a partir del username
    if ($conn->ping()){
        $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            // Devuelve directamente el array asociativo del usuario, parametros son:
            // username, password, role(enum -> superuser, manager, moderator, registered), email, age
            return $result->fetch_assoc(); // Devolvemos el usuario, username es primary key, solo una tupla
        } else {
            http_response_code(500); // Error interno del servidor, condición inesperada
            echo "<h1>Error 500: Error interno del servidor</h1>";
            echo "<p>Lo sentimos, ha ocurrido un error inesperado #1.</p>";
            exit();
        }
    }
    else{
        http_response_code(500); // Error interno del servidor, condición inesperada
        echo "<h1>Error 500: Error interno del servidor</h1>";
        echo "<p>Lo sentimos, ha ocurrido un error inesperado #2.</p>";
        exit();
    }
}

function checkLogin($username, $password, $conn){
    // Comprobamos si el usuario existe

    $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // Si existe, comprobamos la contraseña
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            return 'success'; // Contraseña correcta
        } else {
            return 'wrong password'; // Contraseña incorrecta
        }
    } else {
        return 'username doesnt exists'; // Usuario no encontrado
    }
}

function singUp($username, $password, $email, $role, $age, $conn){
    // Comprobamos si el usuario ya existe
    
    $stmt = $conn->prepare("SELECT username FROM user WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$role) {
        $role = 'registered'; // Si no hay rol, el predeterminado es 'registered'
    }
    if ($result->num_rows > 0) {
        return 'username already exists'; // Usuario ya existe
    } else {
        // Si no existe, lo creamos
        // Esto no debería de hacer falta porque el formulario ya hace required, pero por si acaso 
        if ($username == null || $password == null || $email == null || $age == null || $role == null) {
            return 'missing fields'; // Campos obligatorios no proporcionados
        }
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO user (username, password, role, email, age) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssi', $username, $password_hash, $role, $email, $age);
        if ($stmt->execute()) {
            return 'success'; // Usuario creado correctamente
        } else {
            return $conn->error; // Error al crear el usuario
        }
    }
}

function logEdit($username, $new_username,  $password, $new_password, $email, $role, $age, $conn){
    // Comprobamos si el usuario ya existe
    $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $result = $result->fetch_assoc();
        $fields = [];
        $types = '';
        $values = [];

        // Solo se añaden los campos que el usuario realmente decidió cambiar.
        if ($new_username !== null && $new_username !== $username && $new_username !== '') {
            $fields[] = 'username=?';
            $types .= 's';
            $values[] = $new_username;
        }
        if ($new_password !== null && $new_password !== '' && $new_password !== $password) {
            if (password_verify($password, $result['password'])) {
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $fields[] = "password" . "=?";
                $types .= 's';
                $values[] = $password_hash;
            } else {
                return 'wrong password';
            }
        }
        if ($email !== null && $email !== '' && $result['email'] !== $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $fields[] = 'email=?';
                $types .= 's';
                $values[] = $email;
            }
            else {
                return 'invalid email';
            }
        }
        if ($role !== null && $role !== '' && $result['role'] !== $role) {
            // Consultamos que el rol se vaya a cambiar y que no sea el único superuser
            $sql_superuser = "SELECT * FROM user WHERE role = 'superuser'";
            $result_superuser = $conn->query($sql_superuser);
            if ($result_superuser->num_rows == 1) {
                return 'cannot change role to the only superuser';
            }
            else{
                $fields[] = 'role=?';
                $types .= 's';
                $values[] = $role;
            }
        }
        if ($age !== null && $age !== '' && $result['age'] !== $age) {
            $fields[] = 'age=?';
            $types .= 'i';
            $values[] = (int) $age;
        }

        if (empty($fields)) {
            return 'no fields to update';
        }

        $sql_update = "UPDATE user SET " . implode(', ', $fields) . " WHERE username=?";
        $types .= 's';
        $values[] = $username;
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param($types, ...$values);

        if ($stmt->execute()) {
            return 'success'; // Usuario editado correctamente
        } else {
            return $conn->error; // Error al editar el usuario
        }
    } else {
        return 'username doesnt exists'; // Usuario no encontrado
    }
}

?>
