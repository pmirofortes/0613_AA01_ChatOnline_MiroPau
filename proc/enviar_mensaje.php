<?php

session_start();

if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/login.php');
    exit();
}

// Si el archivo supera el límite del servidor (post_max_size), PHP deja $_POST vacío
if (empty($_POST) && !empty($_SERVER['CONTENT_LENGTH'])) {
    header('Location: ../view/conversaciones.php?error=archivo');
    exit();
}

$idAmigo = $_POST['id_amigo'] ?? '';
$idConversacion = $_POST['id_conversacion'] ?? '';
$mensaje = $_POST['mensaje'] ?? '';
$idRespuesta = $_POST['id_respuesta'] ?? '';

if (!is_string($idAmigo) || !ctype_digit($idAmigo) ||
    !is_string($idConversacion) || !ctype_digit($idConversacion) ||
    !is_string($mensaje) || !is_string($idRespuesta)) {
    header('Location: ../view/conversaciones.php');
    exit();
}

$mensaje = trim($mensaje);
$hayArchivo = isset($_FILES['archivo']) && $_FILES['archivo']['error'] !== UPLOAD_ERR_NO_FILE;

// El mensaje necesita texto, un archivo o las dos cosas
if (($mensaje === '' && !$hayArchivo) || mb_strlen($mensaje) > 250) {
    header('Location: ../view/conversaciones.php');
    exit();
}

// Tipos de archivo permitidos: el tipo real del archivo indica la extensión y cómo se muestra
$tiposPermitidos = [
    'image/jpeg' => ['jpg', 'imagen'],
    'image/png' => ['png', 'imagen'],
    'image/gif' => ['gif', 'imagen'],
    'image/webp' => ['webp', 'imagen'],
    'audio/mpeg' => ['mp3', 'audio'],
    'audio/wav' => ['wav', 'audio'],
    'audio/x-wav' => ['wav', 'audio'],
    'audio/ogg' => ['ogg', 'audio'],
    'audio/mp4' => ['m4a', 'audio'],
    'audio/x-m4a' => ['m4a', 'audio'],
    'application/pdf' => ['pdf', 'archivo'],
    'text/plain' => ['txt', 'archivo'],
    'application/zip' => ['zip', 'archivo'],
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx', 'archivo'],
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx', 'archivo']
];
$tamanoMaximo = 20 * 1024 * 1024;
$archivoGuardado = null;
$nombreArchivo = null;
$tipoArchivo = null;

if ($hayArchivo) {
    $archivo = $_FILES['archivo'];

    if ($archivo['error'] !== UPLOAD_ERR_OK || $archivo['size'] > $tamanoMaximo) {
        header('Location: ../view/conversaciones.php?error=archivo');
        exit();
    }

    // Comprueba el tipo real del contenido, no la extensión que envía el navegador
    $tipo = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
    if (!isset($tiposPermitidos[$tipo])) {
        header('Location: ../view/conversaciones.php?error=archivo');
        exit();
    }

    $extension = $tiposPermitidos[$tipo][0];
    $tipoArchivo = $tiposPermitidos[$tipo][1];

    // Guarda el nombre original solo para mostrarlo, sin rutas y con 255 caracteres como máximo
    $nombreArchivo = mb_substr(basename($archivo['name']), 0, 255);
}

include_once('../database/conexion.php');

$idUsuario = $_SESSION['id_usuario'];

$stmt = $conn->prepare('SELECT c.id FROM conversaciones c INNER JOIN peticiones p ON p.id_estado = 2 AND ((p.id_solicitante = :usuario_solicitante AND p.id_solicitado = :amigo_solicitado) OR (p.id_solicitante = :amigo_solicitante AND p.id_solicitado = :usuario_solicitado)) WHERE c.id = :conversacion AND ((c.id_usuario_1 = :usuario_1 AND c.id_usuario_2 = :amigo_1) OR (c.id_usuario_2 = :usuario_2 AND c.id_usuario_1 = :amigo_2))');
$stmt->execute([
    'usuario_solicitante' => $idUsuario,
    'amigo_solicitado' => $idAmigo,
    'amigo_solicitante' => $idAmigo,
    'usuario_solicitado' => $idUsuario,
    'conversacion' => $idConversacion,
    'usuario_1' => $idUsuario,
    'amigo_1' => $idAmigo,
    'usuario_2' => $idUsuario,
    'amigo_2' => $idAmigo
]);

if (!$stmt->fetch()) {
    header('Location: ../view/conversaciones.php');
    exit();
}

if ($idRespuesta == '') {
    // Si no se está respondiendo a nadie se guarda como NULL
    $idRespuesta = null;
} elseif (!ctype_digit($idRespuesta)) {
    header('Location: ../view/conversaciones.php');
    exit();
} else {
    $stmt = $conn->prepare('SELECT id FROM chats WHERE id = :id AND id_conversacion = :conversacion');
    $stmt->execute([
        'id' => $idRespuesta,
        'conversacion' => $idConversacion
    ]);
    if (!$stmt->fetch()) {
        header('Location: ../view/conversaciones.php');
        exit();
    }
}

if ($hayArchivo) {
    // Mueve el archivo a la carpeta de adjuntos con un nombre único
    $carpetaArchivos = __DIR__ . '/../assets/uploads/chats';
    if (!is_dir($carpetaArchivos) && !mkdir($carpetaArchivos, 0755, true)) {
        header('Location: ../view/conversaciones.php?error=archivo');
        exit();
    }

    $archivoGuardado = $idUsuario . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    if (!move_uploaded_file($archivo['tmp_name'], $carpetaArchivos . '/' . $archivoGuardado)) {
        header('Location: ../view/conversaciones.php?error=archivo');
        exit();
    }
}

// Inserta el mensaje en la conversación seleccionada
$sql = "INSERT INTO chats (mensaje, archivo, nombre_archivo, tipo_archivo, id_emisor, id_conversacion, id_respuesta)
        VALUES (:mensaje, :archivo, :nombre_archivo, :tipo_archivo, :id_emisor, :id_conversacion, :id_respuesta)";

$stmt = $conn->prepare($sql);
$stmt->execute([
    'mensaje' => $mensaje,
    'archivo' => $archivoGuardado,
    'nombre_archivo' => $nombreArchivo,
    'tipo_archivo' => $tipoArchivo,
    'id_emisor' => $idUsuario,
    'id_conversacion' => $idConversacion,
    'id_respuesta' => $idRespuesta
]);

$_SESSION['id_conversacion'] = (int) $idConversacion;
$_SESSION['id_amigo'] = (int) $idAmigo;

// Vuelve al chat después de enviar el mensaje
header('Location: ../view/conversaciones.php');
exit;

