<?php

session_start();

if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/login.php');
    exit();
}

$idChat = $_POST['id_chat'] ?? '';
$mensaje = $_POST['mensaje'] ?? '';
if (!is_string($idChat) || !ctype_digit($idChat) || !is_string($mensaje)) {
    header('Location: ../view/conversaciones.php');
    exit();
}

$mensaje = trim($mensaje);
// mb_strlen cuenta caracteres (acentos y emojis incluidos), no bytes
if ($mensaje === '' || mb_strlen($mensaje) > 250) {
    header('Location: ../view/conversaciones.php');
    exit();
}

include_once('../database/conexion.php');

$idUsuario = $_SESSION['id_usuario'];

// Solo permite editar mensajes propios de conversaciones del usuario actual.
$sql = "UPDATE chats
        SET mensaje = :mensaje
        WHERE id = :id_chat AND id_emisor = :id_usuario
        AND id_conversacion IN (
            SELECT id FROM conversaciones
            WHERE id_usuario_1 = :usuario_1 OR id_usuario_2 = :usuario_2
        )";

$stmt = $conn->prepare($sql);
$stmt->execute([
    'mensaje' => $mensaje,
    'id_chat' => $idChat,
    'id_usuario' => $idUsuario,
    'usuario_1' => $idUsuario,
    'usuario_2' => $idUsuario
]);

// Vuelve al chat después de editar el mensaje
header('Location: ../view/conversaciones.php');
exit;
