<?php

session_start();

if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/login.php');
    exit();
}

$idChat = $_POST['id_chat'] ?? '';
$reaccion = $_POST['reaccion'] ?? '';
$reaccionesPermitidas = ['👍', '❤️', '😂', '😮'];
if (!is_string($idChat) || !ctype_digit($idChat) ||
    !is_string($reaccion) || !in_array($reaccion, $reaccionesPermitidas, true)) {
    header('Location: ../view/conversaciones.php');
    exit();
}

include_once('../database/conexion.php');

$idUsuario = $_SESSION['id_usuario'];

$stmt = $conn->prepare('SELECT ch.id FROM chats ch INNER JOIN conversaciones c ON c.id = ch.id_conversacion WHERE ch.id = :id_chat AND (c.id_usuario_1 = :usuario_1 OR c.id_usuario_2 = :usuario_2)');
$stmt->execute([
    'id_chat' => $idChat,
    'usuario_1' => $idUsuario,
    'usuario_2' => $idUsuario
]);

if (!$stmt->fetch()) {
    header('Location: ../view/conversaciones.php');
    exit();
}

// Elimina la reacción anterior del usuario si ya tenía una
$sqlAnterior = "DELETE FROM reacciones
                WHERE id_usuario = :id_usuario AND id_chat = :id_chat";

$stmtAnterior = $conn->prepare($sqlAnterior);
$stmtAnterior->execute([
    'id_usuario' => $idUsuario,
    'id_chat' => $idChat
]);

// Guarda la nueva reacción
$sql = "INSERT INTO reacciones (reaccion, id_usuario, id_chat)
        VALUES (:reaccion, :id_usuario, :id_chat)";

$stmt = $conn->prepare($sql);
$stmt->execute([
    'reaccion' => $reaccion,
    'id_usuario' => $idUsuario,
    'id_chat' => $idChat
]);

// Vuelve al chat después de reaccionar
header('Location: ../view/conversaciones.php');
exit;
