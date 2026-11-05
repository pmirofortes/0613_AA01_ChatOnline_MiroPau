<?php

session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../view/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/conversaciones.php');
    exit();
}

$idUsuario = $_SESSION['id_usuario'];
$accion = $_POST['accion'] ?? '';
$buscar = $_POST['buscar'] ?? '';
$idOtroUsuario = $_POST['id_usuario'] ?? '';
$idPeticion = $_POST['id_peticion'] ?? '';

if (!is_string($buscar)) {
    $buscar = '';
}

$urlRegreso = '../view/conversaciones.php?buscar=' . urlencode($buscar);

if (!is_string($accion)) {
    header('Location: ' . $urlRegreso);
    exit();
}

include_once('../database/conexion.php');

try {
    if ($accion === 'enviar' && is_string($idOtroUsuario) && ctype_digit($idOtroUsuario)) {
        $idOtroUsuario = (int) $idOtroUsuario;

        if ($idOtroUsuario === (int) $idUsuario) {
            header('Location: ' . $urlRegreso);
            exit();
        }

        $stmt = $conn->prepare('SELECT id, id_estado FROM peticiones WHERE (id_solicitante = :solicitante AND id_solicitado = :solicitado) OR (id_solicitante = :solicitante_inverso AND id_solicitado = :solicitado_inverso) ORDER BY id DESC LIMIT 1');
        $stmt->execute([
            'solicitante' => $idUsuario,
            'solicitado' => $idOtroUsuario,
            'solicitante_inverso' => $idOtroUsuario,
            'solicitado_inverso' => $idUsuario
        ]);
        $peticion = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$peticion) {
            $stmt = $conn->prepare('INSERT INTO peticiones (id_estado, id_solicitante, id_solicitado) VALUES (1, :solicitante, :solicitado)');
            $stmt->execute([
                'solicitante' => $idUsuario,
                'solicitado' => $idOtroUsuario
            ]);
        } elseif ((int) $peticion['id_estado'] === 3) {
            $stmt = $conn->prepare('UPDATE peticiones SET id_estado = 1, id_solicitante = :solicitante, id_solicitado = :solicitado WHERE id = :id');
            $stmt->execute([
                'solicitante' => $idUsuario,
                'solicitado' => $idOtroUsuario,
                'id' => $peticion['id']
            ]);
        }

        header('Location: ' . $urlRegreso . '&solicitud=enviada');
        exit();
    }

    if (($accion === 'aceptar' || $accion === 'rechazar') &&
        is_string($idPeticion) && ctype_digit($idPeticion)) {
        $conn->beginTransaction();

        $nuevoEstado = $accion === 'aceptar' ? 2 : 3;
        $stmt = $conn->prepare('UPDATE peticiones SET id_estado = :estado WHERE id = :id AND id_solicitado = :usuario AND id_estado = 1');
        $stmt->execute([
            'estado' => $nuevoEstado,
            'id' => $idPeticion,
            'usuario' => $idUsuario
        ]);

        if ($stmt->rowCount() === 1 && $accion === 'aceptar') {
            $stmt = $conn->prepare('SELECT id_solicitante FROM peticiones WHERE id = :id');
            $stmt->execute(['id' => $idPeticion]);
            $idAmigo = (int) $stmt->fetchColumn();

            $usuario1 = min((int) $idUsuario, $idAmigo);
            $usuario2 = max((int) $idUsuario, $idAmigo);

            $stmt = $conn->prepare('SELECT id FROM conversaciones WHERE id_usuario_1 = :usuario1 AND id_usuario_2 = :usuario2');
            $stmt->execute([
                'usuario1' => $usuario1,
                'usuario2' => $usuario2
            ]);

            if (!$stmt->fetch()) {
                $stmt = $conn->prepare('INSERT INTO conversaciones (id_usuario_1, id_usuario_2) VALUES (:usuario1, :usuario2)');
                $stmt->execute([
                    'usuario1' => $usuario1,
                    'usuario2' => $usuario2
                ]);
            }
        }

        $conn->commit();
        header('Location: ' . $urlRegreso . '&solicitud=' . ($accion === 'aceptar' ? 'aceptada' : 'rechazada'));
        exit();
    }
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    header('Location: ' . $urlRegreso . '&error=solicitud');
    exit();
}

header('Location: ' . $urlRegreso);
exit();

?>