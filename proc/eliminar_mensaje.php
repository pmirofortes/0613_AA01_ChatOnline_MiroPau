<?php

session_start();

if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/login.php');
    exit();
}

$idChat = $_POST['id_chat'] ?? '';
if (!is_string($idChat) || !ctype_digit($idChat)) {
    header('Location: ../view/conversaciones.php');
    exit();
}

include_once('../database/conexion.php');

$idUsuario = $_SESSION['id_usuario'];

$stmt = $conn->prepare('SELECT ch.id, ch.archivo FROM chats ch INNER JOIN conversaciones c ON c.id = ch.id_conversacion WHERE ch.id = :id_chat AND ch.id_emisor = :emisor AND (c.id_usuario_1 = :usuario_1 OR c.id_usuario_2 = :usuario_2)');
$stmt->execute([
    'id_chat' => $idChat,
    'emisor' => $idUsuario,
    'usuario_1' => $idUsuario,
    'usuario_2' => $idUsuario
]);

$chat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$chat) {
    header('Location: ../view/conversaciones.php');
    exit();
}

// Elimina primero los datos relacionados con el mensaje propio.
$sqlReacciones = "DELETE FROM reacciones WHERE id_chat = :id_chat";
$stmtReacciones = $conn->prepare($sqlReacciones);
$stmtReacciones->execute(['id_chat' => $idChat]);

$sqlRespuestas = "UPDATE chats SET id_respuesta = NULL WHERE id_respuesta = :id_chat";
$stmtRespuestas = $conn->prepare($sqlRespuestas);
$stmtRespuestas->execute(['id_chat' => $idChat]);

$sql = "DELETE FROM chats
        WHERE id = :id_chat AND id_emisor = :id_usuario";

$stmt = $conn->prepare($sql);
$stmt->execute([
    'id_chat' => $idChat,
    'id_usuario' => $idUsuario
]);

// Borra también el archivo adjunto del servidor, si lo tenía
if (!empty($chat['archivo'])) {
    $rutaArchivo = __DIR__ . '/../assets/uploads/chats/' . basename($chat['archivo']);
    if (is_file($rutaArchivo)) {
        unlink($rutaArchivo);
    }
}

// Vuelve al chat después de eliminar el mensaje
header('Location: ../view/conversaciones.php');
exit;
