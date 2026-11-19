<?php

// Esto es una configuración necesaria para que devuelva json que luego
// usa el script
header('Content-Type: application/json');

require_once 'bdUtils.php'; // Para usuarios

// Configuramos variables básicas de la aplicación
$paths = require 'paths.php';
$config = require __DIR__ . '/config.php';
$servername = $config['db_host'];
$username = $config['db_user'];
$password = $config['db_password'];
$dbname = $config['db_name'];
$port = $config['db_port'];

// Abrimos la conexión a la base de datos (Se hace todo y luego lo cierra el php especifico)
$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}


// Obtenemos el id
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $id = filter_var($id, FILTER_VALIDATE_INT);
    if ($id === false) {
        http_response_code(404); // Establece el código de estado HTTP 404
        echo "<h1>Error 404: Algún problema con consulta AJAX</h1>";
        echo "<p>Lo sentimos, la página que buscas no existe 1.</p>";
        exit();
    } 
}

if (isset($_GET['order'])) {
    $order = $_GET['order'];
    if (!in_array($order, ['insert', 'edit', 'delete'])) {
        http_response_code(404); // Establece el código de estado HTTP 404
        echo "<h1>Error 404: Algún problema con consulta AJAX</h1>";
        echo "<p>Lo sentimos, la página que buscas no existe 2.</p>";
        exit();
    }
} else {
    $order = null;
}

if (isset($_GET['getAll'])){
    $getAll = $_GET['getAll'];
} else {
    $getAll = false;
}

session_start();
// Obtenemos el usuario
if (!isset($_SESSION['username'])) {
    $user = null;                                          // Si no hay usuario, lo inicializamos a null
} else {
    $user = $_SESSION['username']; // Si hay usuario lo obtenemos
}

if ($order && in_array($order, ['insert', 'edit', 'delete'], true)) {
    requireCsrf();
    if (!$user) {
        http_response_code(403);
        echo json_encode(['error' => 'Authentication required']);
        exit();
    }
}



// Tanto editar como borrar un comentario necesitan el id del comentario

// METER UN COMENTARIO NUEVO
try {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && $order == 'insert') {
        $fcontent = $_POST["fcontent"];
        $film_id = $id;

        $stmt = $conn->prepare("INSERT INTO comment(film_id, author, date, text) VALUES (?, ?, NOW(), ?)");
        $stmt->bind_param('iss', $film_id, $user, $fcontent);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
    }
} catch (Exception $e) {
    echo json_encode([
        "error" => $e->getMessage(),
        "order" => $order,
    ]);
    exit();
}

// EDITAR UN COMENTARIO EXISTENTE
try {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && $order == 'edit') {
        $fcontent = $_POST["fcontent"];
        $commentId = $_GET['commentId'] ?? null;
        
        $stmt = $conn->prepare("UPDATE comment SET text = ?, edited=1 WHERE id = ? AND author = ?");
        $stmt->bind_param('sis', $fcontent, $commentId, $user);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
    }
} catch (Exception $e) {
    echo json_encode([
        "error" => $e->getMessage(),
    ]);
    exit();
}


// BORRAR UN COMENTARIO
try {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && $order == 'delete') {
        $commentId = $_GET['commentId'] ?? null;
        $stmt = $conn->prepare("DELETE FROM comment WHERE id = ? AND author = ?");
        $stmt->bind_param('is', $commentId, $user);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
    }
} catch (Exception $e) {
    echo json_encode([
        "error" => $e->getMessage(),
    ]);
    exit();
}





// Esto lo hace siempre
// Para obtener los comentarios de la película (El resultado es una o más tuplas)
if (!$getAll) {
    $stmt = $conn->prepare("SELECT id, author, date, text, edited FROM comment WHERE film_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result_comments = $stmt->get_result();
}
else{
    $stmt_user = $conn->prepare("SELECT role FROM user WHERE username = ?");
    $stmt_user->bind_param('s', $user);
    $stmt_user->execute();
    $current_user = $stmt_user->get_result()->fetch_assoc();
    if (!$current_user || !in_array($current_user['role'], ['moderator', 'manager', 'superuser'], true)) {
        http_response_code(403);
        echo json_encode(['error' => 'Insufficient permissions']);
        exit();
    }
    $sql_comments = "SELECT comment.id AS comment_id,
                            comment.author, 
                            comment.date, 
                            comment.text, 
                            comment.edited, 
                            film.id AS film_id, 
                            film.name AS film_name
                            FROM comment 
                            JOIN film ON comment.film_id = film.id 
                            ORDER BY date DESC";
    $result_comments = $conn->query($sql_comments);
}


if ($result_comments->num_rows > 0) {
    while($row_comments = $result_comments->fetch_assoc()){
        $user_author = $row_comments["author"];
        $email = null;

        if ($user_author) {
            $stmt_email = $conn->prepare("SELECT role,email FROM user WHERE username = ?");
            $stmt_email->bind_param('s', $user_author);
            $stmt_email->execute();
            $result_email = $stmt_email->get_result();
            if ($result_email && $row_email = $result_email->fetch_assoc()) {
                $email = $row_email['email'];
                $role = $row_email['role'];
            }
        }
        if ($getAll){
            $comments[] = array(
                'id' => $row_comments["comment_id"] ?? null,
                'author' => $user_author ?? null,
                'role' => $role ?? null,
                'email' => $email ?? null,
                'date' => $row_comments["date"] ?? null,
                'text' => $row_comments["text"] ?? null,
                'edited' => $row_comments["edited"] ?? null,
                'film_id' => $row_comments["film_id"] ?? null,
                'film_name' => $row_comments["film_name"] ?? null,        
            );
        }
        else{
            $comments[] = array(
                'id' => $row_comments["id"] ?? null,
                'author' => $user_author ?? null,
                'role' => $role ?? null,
                'email' => $email ?? null,
                'date' => $row_comments["date"] ?? null,
                'text' => $row_comments["text"] ?? null,
                'edited' => $row_comments["edited"] ?? null,    
            );
        }
    }
} else {
    $comments = array();
}

$conn->close();


$q = isset($_POST['q']) ? trim($_POST['q']) : '';
$filtered_comments = [];


if ($q !== '') {
    foreach ($comments as $comment) {
        if (
            stripos($comment['text'], $q) !== false
        ) {
            $filtered_comments[] = $comment;
        }
    }
} else {
    $filtered_comments = $comments;
}


echo json_encode($filtered_comments);
?>