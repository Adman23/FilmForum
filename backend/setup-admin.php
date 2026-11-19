<?php

$username = getenv('ADMIN_USERNAME') ?: '';
$password = getenv('ADMIN_PASSWORD') ?: '';
$email = getenv('ADMIN_EMAIL') ?: '';

// Omite el setup si no se proporcionaron las tres variables.
if ($username === '' || $password === '' || $email === '') {
    exit(0);
}

$host = getenv('DB_HOST') ?: 'db';
$port = (int) (getenv('DB_PORT') ?: 3306);
$database = getenv('DB_NAME') ?: 'FILM_DB';
$dbUser = getenv('DB_USER') ?: '';
$dbPassword = getenv('DB_PASSWORD') ?: '';

$conn = null;
for ($attempt = 1; $attempt <= 20; $attempt++) {
    mysqli_report(MYSQLI_REPORT_OFF);
    $candidate = new mysqli($host, $dbUser, $dbPassword, $database, $port);
    if (!$candidate->connect_error) {
        $conn = $candidate;
        break;
    }
    sleep(1);
}

if (!$conn) {
    fwrite(STDERR, "No se pudo conectar a MySQL para crear el superusuario.\n");
    exit(1);
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("SELECT username FROM user WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$exists = $stmt->get_result()->num_rows > 0;
$stmt->close();

if ($exists) {
    $stmt = $conn->prepare(
        "UPDATE user SET password = ?, role = 'superuser', email = ? WHERE username = ?"
    );
    $stmt->bind_param('sss', $passwordHash, $email, $username);
} else {
    $stmt = $conn->prepare(
        "INSERT INTO user (username, password, role, email, age) VALUES (?, ?, 'superuser', ?, 18)"
    );
    $stmt->bind_param('sss', $username, $passwordHash, $email);
}

if (!$stmt->execute()) {
    fwrite(STDERR, "No se pudo crear o actualizar el superusuario.\n");
    $stmt->close();
    $conn->close();
    exit(1);
}

$stmt->close();
$conn->close();
