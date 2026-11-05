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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/admin.php');
    exit();
}

$idEditar = $_POST['id'] ?? '';

if (!is_string($idEditar) || !ctype_digit($idEditar)) {
    header('Location: ../view/admin.php?error=usuario');
    exit();
}

// Todas las redirecciones conservan el id para volver al usuario que se edita
$urlVista = '../view/editar_usuario.php?id=' . $idEditar;

$username = $_POST['username'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$apellidos = $_POST['apellidos'] ?? '';
$password = $_POST['password'] ?? '';
$rol = $_POST['rol'] ?? '';

if (!is_string($username) || !is_string($nombre) || !is_string($apellidos) || !is_string($password) || !is_string($rol)) {
    header('Location: ' . $urlVista . '&error=campos');
    exit();
}

$username = trim($username);
$nombre = trim($nombre);
$apellidos = trim($apellidos);

if ($username === '' || $nombre === '' || $apellidos === '') {
    header('Location: ' . $urlVista . '&error=campos');
    exit();
}

if (!preg_match('/^[a-zA-Z0-9]{3,30}$/', $username)) {
    header('Location: ' . $urlVista . '&error=username');
    exit();
}

if (!preg_match("/^[\p{L}\p{M}]+(?:[ '-][\p{L}\p{M}]+)*$/u", $nombre) ||
    !preg_match("/^[\p{L}\p{M}]+(?:[ '-][\p{L}\p{M}]+)*$/u", $apellidos)) {
    header('Location: ' . $urlVista . '&error=nombre');
    exit();
}

if (strlen($nombre) > 100 || strlen($apellidos) > 150 ||
    ($password !== '' && (strlen($password) < 8 || strlen($password) > 72))) {
    header('Location: ' . $urlVista . '&error=longitud');
    exit();
}

if ($rol !== 'usuario' && $rol !== 'administrador') {
    header('Location: ' . $urlVista . '&error=rol');
    exit();
}

include_once('../database/conexion.php');

$fotoNueva = null;

try {
    $stmt = $conn->prepare('SELECT password, foto FROM usuarios WHERE id = :id');
    $stmt->execute(['id' => $idEditar]);
    $usuarioActual = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuarioActual) {
        header('Location: ../view/admin.php?error=usuario');
        exit();
    }

    $stmt = $conn->prepare('SELECT id FROM usuarios WHERE username = :username AND id <> :id');
    $stmt->execute([
        'username' => $username,
        'id' => $idEditar
    ]);

    if ($stmt->fetch()) {
        header('Location: ' . $urlVista . '&error=existe');
        exit();
    }

    $foto = $usuarioActual['foto'];

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        $archivo = $_FILES['foto'];

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            header('Location: ' . $urlVista . '&error=foto');
            exit();
        }

        $tipo = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
        $tiposPermitidos = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png'
        ];

        if ($archivo['size'] > 2 * 1024 * 1024 || !isset($tiposPermitidos[$tipo])) {
            header('Location: ' . $urlVista . '&error=foto');
            exit();
        }

        $carpetaFotos = __DIR__ . '/../assets/uploads/usuarios';
        if (!is_dir($carpetaFotos) && !mkdir($carpetaFotos, 0755, true)) {
            header('Location: ' . $urlVista . '&error=foto');
            exit();
        }

        $fotoNueva = $idEditar . '_' . time() . '.' . $tiposPermitidos[$tipo];
        if (file_exists($carpetaFotos . '/' . $fotoNueva) ||
            !move_uploaded_file($archivo['tmp_name'], $carpetaFotos . '/' . $fotoNueva)) {
            $fotoNueva = null;
            header('Location: ' . $urlVista . '&error=foto');
            exit();
        }

        $foto = $fotoNueva;
    }

    $passwordHash = $usuarioActual['password'];
    if ($password !== '') {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    }

    $sql = 'UPDATE usuarios SET username = :username, nombre = :nombre, apellidos = :apellidos, password = :password, foto = :foto, rol = :rol WHERE id = :id';
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'username' => $username,
        'nombre' => $nombre,
        'apellidos' => $apellidos,
        'password' => $passwordHash,
        'foto' => $foto,
        'id' => $idEditar,
        'rol' => $rol
    ]);

    // La sesión solo cambia si el administrador se edita a sí mismo
    if ((int) $idEditar === (int) $_SESSION['id_usuario']) {
        $_SESSION['username'] = $username;
        $_SESSION['usuario_foto'] = $foto;
        $_SESSION['rol'] = $rol;
    }

    if ($fotoNueva !== null && !empty($usuarioActual['foto'])) {
        $fotoAnterior = __DIR__ . '/../assets/uploads/usuarios/' . $usuarioActual['foto'];
        if (is_file($fotoAnterior)) {
            unlink($fotoAnterior);
        }
    }
} catch (PDOException $e) {
    if ($fotoNueva !== null && file_exists(__DIR__ . '/../assets/uploads/usuarios/' . $fotoNueva)) {
        unlink(__DIR__ . '/../assets/uploads/usuarios/' . $fotoNueva);
    }
    header('Location: ' . $urlVista . '&error=bd');
    exit();
}

header('Location: ' . $urlVista . '&actualizado=ok');
exit();

?>


