<?php

// Este procesador solo debe recibir datos enviados desde un formulario POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Si se abre directamente, vuelve al formulario de registro.
    header('Location: ../view/registro.php');
    // Detiene el script para que no siga procesando una petición incorrecta.
    exit();
}

// trim() quita espacios al principio y al final de los datos de texto.
// El operador ?? usa una cadena vacía si el campo no se envió.
$username = trim($_POST['username'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
// La contraseña no se recorta: los espacios pueden formar parte de ella.
$password = $_POST['password'] ?? '';
$confirma_password = $_POST['confirma_password'] ?? '';

// Comprueba que se hayan completado todos los campos necesarios.
if ($username === '' || $nombre === '' || $apellidos === '' || $password === '' || $confirma_password === '') {
    // El formulario usa este código para mostrar el aviso correspondiente.
    header('Location: ../view/registro.php?error=campos');
    exit();
}

// La expresión regular permite solo letras y números, con una longitud de 3 a 30.
// preg_match() devuelve 1 si el usuario cumple el formato.
if (!preg_match('/^[a-zA-Z0-9]{3,30}$/', $username)) {
    header('Location: ../view/registro.php?error=username');
    exit();
}

// Permite letras, espacios, apóstrofos y guiones en nombres y apellidos.
if (!preg_match("/^[\p{L}\p{M}]+(?:[ '-][\p{L}\p{M}]+)*$/u", $nombre) ||
    !preg_match("/^[\p{L}\p{M}]+(?:[ '-][\p{L}\p{M}]+)*$/u", $apellidos)) {
    header('Location: ../view/registro.php?error=nombre');
    exit();
}

// Comprueba los límites admitidos por la tabla y por el algoritmo BCrypt.
if (strlen($nombre) > 100 || strlen($apellidos) > 150 || strlen($password) < 8 || strlen($password) > 72) {
    header('Location: ../view/registro.php?error=longitud');
    exit();
}

// Compara las dos contraseñas antes de guardar la cuenta.
if ($password !== $confirma_password) {
    header('Location: ../view/registro.php?error=contrasena');
    exit();
}

// La foto es opcional. Si se envía, se guardará su nombre en la base de datos.
$foto = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
    $archivo = $_FILES['foto'];

    // Si PHP indica un error de subida, no hay un archivo válido que revisar.
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        header('Location: ../view/registro.php?error=foto');
        exit();
    }

    // Solo se aceptan imágenes JPG o PNG de hasta 2 MB.
    $tipo = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
    $tipos_permitidos = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png'
    ];

    if ($archivo['size'] > 2 * 1024 * 1024 || !isset($tipos_permitidos[$tipo])) {
        header('Location: ../view/registro.php?error=foto');
        exit();
    }

    // Crea la carpeta de fotos si todavía no existe.
    $carpeta_fotos = __DIR__ . '/../assets/uploads/usuarios';
    if (!is_dir($carpeta_fotos) && !mkdir($carpeta_fotos, 0755, true)) {
        header('Location: ../view/registro.php?error=foto');
        exit();
    }

    // Conserva el nombre del archivo elegido, quitando cualquier ruta y carácter no permitido.
    $nombre_original = str_replace('\\', '/', $archivo['name']);
    $nombre_sin_extension = pathinfo($nombre_original, PATHINFO_FILENAME);
    $nombre_sin_extension = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nombre_sin_extension);
    if ($nombre_sin_extension === '') {
        header('Location: ../view/registro.php?error=foto');
        exit();
    }

    // Se usa la extensión correspondiente al tipo de imagen comprobado en el servidor.
    $foto = $nombre_sin_extension . '.' . $tipos_permitidos[$tipo];
    if (file_exists($carpeta_fotos . '/' . $foto)) {
        header('Location: ../view/registro.php?error=foto');
        exit();
    }

    if (!move_uploaded_file($archivo['tmp_name'], $carpeta_fotos . '/' . $foto)) {
        header('Location: ../view/registro.php?error=foto');
        exit();
    }
}

// Carga la conexión PDO desde la carpeta database.
require_once __DIR__ . '/../database/conexion.php';

// try permite capturar errores que puedan ocurrir al consultar o guardar en la base de datos.
try {
    // Prepara la búsqueda del usuario; el parámetro evita insertar el dato directamente en SQL.
    $stmt = $conn->prepare('SELECT id FROM usuarios WHERE username = :username');
    // Sustituye :username por el valor introducido y ejecuta la consulta.
    $stmt->execute([':username' => $username]);
    // fetch() devuelve una fila si el usuario ya existe; si no, devuelve false.
    if ($stmt->fetch()) {
        // Borra la foto subida porque no se va a crear una cuenta para ella.
        if ($foto !== null && file_exists(__DIR__ . '/../assets/uploads/usuarios/' . $foto)) {
            unlink(__DIR__ . '/../assets/uploads/usuarios/' . $foto);
        }
        header('Location: ../view/registro.php?error=existe');
        exit();
    }

    // Guarda los datos de la cuenta y el nombre de la foto, si se ha subido.
    $sql = 'INSERT INTO usuarios (username, nombre, apellidos, password, foto) VALUES (:username, :nombre, :apellidos, :password, :foto)';
    $stmt = $conn->prepare($sql);
    // Asocia cada parámetro SQL con su dato y ejecuta la inserción.
    $stmt->execute([
        ':username' => $username,
        ':nombre' => $nombre,
        ':apellidos' => $apellidos,
        // PASSWORD_BCRYPT cifra la contraseña con el algoritmo BCrypt pedido en el enunciado.
        ':password' => password_hash($password, PASSWORD_BCRYPT),
        ':foto' => $foto
    ]);
// PDOException contiene los errores que PDO produce en operaciones de base de datos.
} catch (PDOException $e) {
    // Si falla el registro, borra la foto para no dejar un archivo sin usuario asociado.
    if ($foto !== null && file_exists(__DIR__ . '/../assets/uploads/usuarios/' . $foto)) {
        unlink(__DIR__ . '/../assets/uploads/usuarios/' . $foto);
    }
    // Si falla la operación, vuelve al formulario para mostrar el error de base de datos.
    header('Location: ../view/registro.php?error=bd');
    exit();
}

// Tras crear la cuenta, dirige al usuario a iniciar sesión.
header('Location: ../view/login.php?registro=ok');
exit();

?>
