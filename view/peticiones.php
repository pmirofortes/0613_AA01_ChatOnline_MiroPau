<?php

session_start();
include_once('../database/conexion.php');

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit();
}

$idUsuario = $_SESSION['id_usuario'];
$peticiones = [];
$rutaFotosUsuarios = '../assets/uploads/usuarios/';
$fotoPredeterminada = '../assets/icons/usuario.png';

try {
        $sql = "SELECT p.id AS id_peticion, u.id, u.nombre, u.apellidos, u.foto, u.username
            FROM peticiones p
            INNER JOIN usuarios u ON p.id_solicitante = u.id
            WHERE p.id_solicitado = :id_usuario AND p.id_estado = 1";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['id_usuario' => $idUsuario]);
    $peticiones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'No se han podido cargar las peticiones.';
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peticiones de amistad</title>
    <link rel="icon" type="image/png" href="../assets/icons/holonet-favicon.png?v=3.0">
    <link rel="stylesheet" href="../assets/styles/style.css?v=3.1">
</head>
<body class="pagina-peticiones">
    <header>
        <h1>Peticiones de amistad</h1>
        <a class="volver-conversaciones" href="conversaciones.php">Volver a conversaciones</a>
        <span class="usuario-sesion">@<?= htmlspecialchars($_SESSION['username']) ?></span>
    </header>

    <main class="contenido-peticiones">
        <?php if (isset($error)): ?>
            <p><?= $error ?></p>
        <?php elseif (empty($peticiones)): ?>
            <p>No tienes peticiones de amistad.</p>
        <?php else: ?>
            <ul class="lista-peticiones">
                <?php foreach ($peticiones as $peticion): ?>
                    <?php
                    $fotoPeticion = $fotoPredeterminada;

                    if (!empty($peticion['foto'])) {
                        $fotoPeticion = $rutaFotosUsuarios . $peticion['foto'];
                    }
                    ?>
                    <li class="peticion">
                        <img
                            src="<?= htmlspecialchars($fotoPeticion) ?>"
                            alt="Foto de <?= htmlspecialchars($peticion['nombre']) ?>">
                        <div>
                            <strong><?= htmlspecialchars($peticion['nombre'] . ' ' . $peticion['apellidos']) ?></strong>
                            <span>@<?= htmlspecialchars($peticion['username']) ?></span>
                            <form action="../proc/gestionar_solicitud.php" method="POST">
                                <input type="hidden" name="id_peticion" value="<?= $peticion['id_peticion'] ?>">
                                <button type="submit" name="accion" value="aceptar">Aceptar</button>
                                <button type="submit" name="accion" value="rechazar">Rechazar</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
</body>
</html>
