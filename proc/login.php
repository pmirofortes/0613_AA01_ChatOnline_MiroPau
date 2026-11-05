<?php
// Inicia o recupera la sesión para guardar al usuario autenticado.
session_start();

// Este proceso solo acepta los datos enviados desde el formulario por POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/login.php');
    exit();
}

// trim() quita espacios exteriores; ?? usa una cadena vacía si falta el campo.
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (!is_string($username) || !is_string($password)) {
    header('Location: ../view/login.php?error=credenciales');
    exit();
}

$username = trim($username);

// Rechaza campos vacíos y usernames con un formato que el registro no permite.
if ($username === '' || $password === '' || !preg_match('/^[a-zA-Z0-9]{3,30}$/', $username)) {
    header('Location: ../view/login.php?error=credenciales');
    exit();
}

// Usa la misma conexión que el resto de la aplicación.
require_once __DIR__ . '/../database/conexion.php';

try {
    $stmt = $conn->prepare('SELECT id, username, password, foto, rol FROM usuarios WHERE username = :username');
    $stmt->execute([':username' => $username]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    header('Location: ../view/login.php?error=bd');
    exit();
}

// password_verify() compara la contraseña recibida con el hash guardado en la base de datos.
if ($usuario && password_verify($password, $usuario['password'])) {
    // Cambia el identificador de sesión al iniciar sesión para protegerla.
    session_regenerate_id(true);
    // Guarda en la sesión los datos que identifican al usuario autenticado.
    $_SESSION['id_usuario'] = $usuario['id'];
    $_SESSION['username'] = $usuario['username'];
    $_SESSION['usuario_foto'] = $usuario['foto'];
    $_SESSION['rol'] = $usuario['rol'];
    // Envía al usuario autenticado a la página principal.
    header('Location: ../index.php');
    exit();
}

// Si el usuario no existe o la contraseña no coincide, muestra un error genérico.
header('Location: ../view/login.php?error=credenciales');
exit();