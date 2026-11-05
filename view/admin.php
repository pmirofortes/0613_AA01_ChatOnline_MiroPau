<?php
session_start();

include_once('../database/conexion.php');

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['rol'] !== 'administrador') {
    header('Location: ../index.php');
    exit();
}

$idUsuario = $_SESSION['id_usuario'];
$rolUsuario = $_SESSION['rol'];


$sql = "SELECT id, nombre, apellidos, username, foto, rol FROM usuarios";
$stmt = $conn->prepare($sql);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$erroresEliminar = [
    'usuario' => 'El usuario no existe.',
    'bd' => 'No se ha podido eliminar el usuario. Inténtalo de nuevo.'
];
$error = $_GET['error'] ?? '';
$mensajeError = is_string($error) ? ($erroresEliminar[$error] ?? null) : null;

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="../assets/styles/style.css?v=3.1">
</head>
<body>
    <?php if ($mensajeError !== null): ?>
        <p><?php echo $mensajeError; ?></p>
    <?php endif; ?>
    <?php if (($_GET['eliminado'] ?? '') === 'ok'): ?>
        <p>Usuario eliminado correctamente.</p>
    <?php endif; ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>Username</th>
            <th>Foto</th>
            <th>Rol</th>
            <th>Editar</th>
            <th>Eliminar</th>
        </tr>
        <?php foreach ($usuarios as $usuario):?>
        <tr>
            <td><?php echo $usuario['id']; ?></td>
            <td><?php echo $usuario['nombre']; ?></td>
            <td><?php echo $usuario['apellidos']; ?></td>
            <td><?php echo $usuario['username']; ?></td>
            <td><img src="../assets/uploads/usuarios/<?php echo $usuario['foto']; ?>" alt="Foto" width="100"></td>
            <td><?php echo $usuario['rol']; ?></td>
            <td><a href="editar_usuario.php?id=<?php echo $usuario['id']; ?>">Editar</a></td>
            <td><?php if ($usuario['id'] != $idUsuario): ?>
                <a href="../proc/eliminar_usuario.php?id=<?php echo $usuario['id']; ?>">Eliminar</a>
            <?php endif; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <br>
    <p><- <a href="../index.php">Volver al inicio</a></p>
</body>
</html>