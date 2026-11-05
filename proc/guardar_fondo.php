<?php

session_start();
include_once('../database/conexion.php');

if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/login.php');
    exit();
}

$idUsuario = $_SESSION['id_usuario'];
$idConversacion = $_POST['id_conversacion'] ?? '';
$idAmigo = $_POST['id_amigo'] ?? '';
$idFondo = $_POST['id_fondo'] ?? '';

// Los tres datos deben ser números enteros positivos
if (!is_string($idConversacion) || !ctype_digit($idConversacion) ||
    !is_string($idAmigo) || !ctype_digit($idAmigo) ||
    !is_string($idFondo) || !ctype_digit($idFondo)) {
    header('Location: ../view/conversaciones.php');
    exit();
}

// Verifica que el fondo existe y que el usuario pertenece a la conversación
$sqlComprobar = "SELECT COUNT(*)
                 FROM conversaciones c
                 INNER JOIN fondos f ON f.id = :id_fondo
                 WHERE c.id = :id_conversacion
                   AND :id_usuario IN (c.id_usuario_1, c.id_usuario_2)
                   AND :id_amigo IN (c.id_usuario_1, c.id_usuario_2)";
$stmtComprobar = $conn->prepare($sqlComprobar);
$stmtComprobar->execute([
    'id_fondo' => $idFondo,
    'id_conversacion' => $idConversacion,
    'id_usuario' => $idUsuario,
    'id_amigo' => $idAmigo
]);

if ($stmtComprobar->fetchColumn() > 0) {
    $sqlGuardar = "INSERT INTO fondos_conversaciones (id_conversacion, id_usuario, id_fondo)
                   VALUES (:id_conversacion, :id_usuario, :id_fondo)
                   ON DUPLICATE KEY UPDATE id_fondo = :id_fondo_actualizado";
    $stmtGuardar = $conn->prepare($sqlGuardar);
    $stmtGuardar->execute([
        'id_conversacion' => $idConversacion,
        'id_usuario' => $idUsuario,
        'id_fondo' => $idFondo,
        'id_fondo_actualizado' => $idFondo
    ]);

    $_SESSION['id_conversacion'] = $idConversacion;
    $_SESSION['id_amigo'] = $idAmigo;
}

header('Location: ../view/conversaciones.php');
exit();
