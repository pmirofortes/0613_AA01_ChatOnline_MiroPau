<?php

session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../view/login.php');
    exit();
}

if ($_SESSION['rol'] !== 'administrador') {
    header('Location: ../index.php');
    exit();
}

$idUsuarioEliminar = $_GET['id'] ?? '';

if (!is_string($idUsuarioEliminar) || !ctype_digit($idUsuarioEliminar)) {
    header('Location: ../view/admin.php?error=usuario');
    exit();
}

include_once('../database/conexion.php');

$id = $idUsuarioEliminar;

$paramsChats = ['usuario_1' => $id, 'usuario_2' => $id, 'emisor' => $id];

try {
    $conn->beginTransaction();

    $stmt = $conn->prepare('SELECT foto FROM usuarios WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        $conn->rollBack();
        header('Location: ../view/admin.php?error=usuario');
        exit();
    }

    // Reacciones
    $sql = "DELETE r
            FROM reacciones r
            INNER JOIN chats ch ON ch.id = r.id_chat
            INNER JOIN conversaciones c ON c.id = ch.id_conversacion
            WHERE r.id_usuario = :usuario
            OR c.id_usuario_1 = :usuario_1 OR c.id_usuario_2 = :usuario_2 OR ch.id_emisor = :emisor";
    $stmt = $conn->prepare($sql);
    $stmt->execute($paramsChats + ['usuario' => $id]);

    // Respuestas
    $sql = "UPDATE chats hijo
            INNER JOIN chats ch ON ch.id = hijo.id_respuesta
            INNER JOIN conversaciones c ON c.id = ch.id_conversacion
            SET hijo.id_respuesta = NULL
            WHERE c.id_usuario_1 = :usuario_1 OR c.id_usuario_2 = :usuario_2 OR ch.id_emisor = :emisor";
    $stmt = $conn->prepare($sql);
    $stmt->execute($paramsChats);

    // Mensajes 
    $sql = "DELETE ch
            FROM chats ch
            INNER JOIN conversaciones c ON c.id = ch.id_conversacion
            WHERE c.id_usuario_1 = :usuario_1 OR c.id_usuario_2 = :usuario_2 OR ch.id_emisor = :emisor";
    $stmt = $conn->prepare($sql);
    $stmt->execute($paramsChats);

    // Fondos elegidos 
    $sql = "DELETE fc
            FROM fondos_conversaciones fc
            INNER JOIN conversaciones c ON c.id = fc.id_conversacion
            WHERE fc.id_usuario = :usuario OR c.id_usuario_1 = :usuario_1 OR c.id_usuario_2 = :usuario_2";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['usuario' => $id, 'usuario_1' => $id, 'usuario_2' => $id]);

    $stmt = $conn->prepare('DELETE FROM conversaciones WHERE id_usuario_1 = :usuario_1 OR id_usuario_2 = :usuario_2');
    $stmt->execute(['usuario_1' => $id, 'usuario_2' => $id]);

    $stmt = $conn->prepare('DELETE FROM peticiones WHERE id_solicitante = :solicitante OR id_solicitado = :solicitado');
    $stmt->execute(['solicitante' => $id, 'solicitado' => $id]);

    $stmt = $conn->prepare('DELETE FROM usuarios WHERE id = :id');
    $stmt->execute(['id' => $id]);

    $conn->commit();
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    header('Location: ../view/admin.php?error=bd');
    exit();
}


header('Location: ../view/admin.php?eliminado=ok');
exit();
