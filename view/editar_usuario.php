<?php

session_start();

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
$idUsuarioEditar = $_GET['id'] ?? '';

if (!is_string($idUsuarioEditar) || !ctype_digit($idUsuarioEditar)) {
    header('Location: admin.php?error=usuario');
    exit();
}

include_once('../database/conexion.php');


try {
    $stmt = $conn->prepare('SELECT username, nombre, apellidos, password, foto, rol FROM usuarios WHERE id = :id');
    $stmt->execute(['id' => $idUsuarioEditar]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        header('Location: admin.php?error=usuario');
        exit();
    }
} catch (PDOException $e) {
    $error = 'No se pudieron cargar los datos del usuario.';
}

$mensajes = [
    'campos' => 'Completa todos los campos obligatorios.',
    'username' => 'El usuario debe tener entre 3 y 30 letras o números.',
    'existe' => 'Ese nombre de usuario ya está en uso.',
    'nombre' => 'Nombre y apellidos solo pueden contener letras, espacios, apóstrofos o guiones.',
    'longitud' => 'La nueva contraseña debe tener entre 8 y 72 caracteres.',
    'foto' => 'La foto debe ser JPG o PNG y no superar 2 MB.',
    'rol' => 'El rol no es válido.',
    'bd' => 'No se pudieron guardar los cambios. Inténtalo de nuevo.'
];

if (isset($_GET['error'], $mensajes[$_GET['error']])) {
    $error = $mensajes[$_GET['error']];
} elseif (isset($_GET['actualizado']) && $_GET['actualizado'] === 'ok') {
    $mensaje = 'Los cambios se han guardado correctamente.';
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar usuario - Administrador</title>
    <link rel="icon" type="image/png" href="../assets/icons/holonet-favicon.png?v=3.0">
    <link rel="stylesheet" href="../assets/styles/style.css?v=3.1">
</head>
<body class="pagina-formulario">
    <header>
        <h1>Editar usuario</h1>
        <span class="usuario-sesion">@<?= htmlspecialchars($_SESSION['username']) ?></span>
    </header>

    <main class="perfil-contenido">
        <?php if (isset($error)): ?>
            <p class="perfil-mensaje" role="alert"><?= $error ?></p>
        <?php elseif (isset($mensaje)): ?>
            <p class="perfil-mensaje" role="status"><?= $mensaje ?></p>
        <?php endif; ?>

        <?php if (isset($usuario)): ?>
        <form class="perfil-formulario" action="../proc/editar_usuario_admin.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= htmlspecialchars($idUsuarioEditar) ?>">

            <label for="username">Usuario:</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($usuario['username']) ?>">

            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>">

            <label for="apellidos">Apellidos:</label>
            <input type="text" id="apellidos" name="apellidos" value="<?= htmlspecialchars($usuario['apellidos']) ?>">

            <label for="rol">Rol:</label>
            <select id="rol" name="rol">
                <option value="usuario" <?= $usuario['rol'] === 'usuario' ? 'selected' : '' ?>>Usuario</option>
                <option value="administrador" <?= $usuario['rol'] === 'administrador' ? ' selected' : '' ?>>Administrador</option>
            </select>

            <label for="password">Nueva contraseña (opcional):</label>
            <input type="password" id="password" name="password" autocomplete="new-password">

            <label for="foto">Nueva foto de perfil (opcional):</label>
            <input type="file" id="foto" name="foto">
            <?php if (!empty($usuario['foto'])): ?>
                <img class="perfil-foto" src="../assets/uploads/usuarios/<?= htmlspecialchars($usuario['foto']) ?>" alt="Foto de perfil actual">
            <?php endif; ?>

            <div class="perfil-acciones">
                <button type="submit">Aplicar cambios</button>
                <a href="admin.php">Volver a Gestión de Usuarios</a>
            </div>
        </form>
        <?php else: ?>
            <a href="admin.php">Volver a Gestión de Usuarios</a>
        <?php endif; ?>
    </main>
</body>
</html>
