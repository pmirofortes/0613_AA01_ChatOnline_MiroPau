<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="icon" type="image/png" href="../assets/icons/holonet-favicon.png?v=3.0">
    <link rel="stylesheet" href="../assets/styles/style.css?v=3.1">
</head>
<body class="pagina-formulario">
<main class="formulario-contenido formulario-contenido-ancho">
<section class="formulario-panel">
<h1>Crear cuenta</h1>
<p>Completa el formulario para registrarte.</p>

<?php
// Asocia los códigos de error recibidos desde el procesador con su explicación.
$mensajes = [
    'campos' => 'Completa todos los campos obligatorios.',
    'username' => 'El usuario debe tener entre 3 y 30 letras o números.',
    'existe' => 'Ese usuario ya está registrado.',
    'nombre' => 'Nombre y apellidos solo pueden contener letras, espacios, apóstrofos o guiones.',
    'longitud' => 'Revisa la longitud de los campos. La contraseña debe tener entre 8 y 72 caracteres.',
    'contrasena' => 'Las contraseñas no coinciden.',
    'foto' => 'La foto debe ser JPG o PNG y no superar 2 MB.',
    'bd' => 'No se pudo crear la cuenta. Inténtalo de nuevo.'
];
// Solo muestra el mensaje cuando el código existe en la lista.
if (isset($_GET['error'], $mensajes[$_GET['error']])) {
    // Convierte caracteres especiales para mostrar el mensaje como texto y no como HTML.
    echo '<p role="alert">' . $mensajes[$_GET['error']] . '</p>';
}
?>

<!-- Envía los campos al procesador que los valida y guarda la cuenta. -->
<form class="formulario-cuenta" action="../proc/registro.php" method="POST" enctype="multipart/form-data">

    <label for="username">Usuario:</label>
    <input type="text" id="username" name="username">

    <label for="nombre">Nombre:</label>
    <input type="text" id="nombre" name="nombre">

    <label for="apellidos">Apellidos:</label>
    <input type="text" id="apellidos" name="apellidos">

    <label for="password">Contraseña:</label>
    <input type="password" id="password" name="password">

    <label for="confirma_password">Confirmar Contraseña:</label>
    <input type="password" id="confirma_password" name="confirma_password">

    <label for="foto">Foto de perfil:</label>
    <input type="file" id="foto" name="foto">

    <button type="submit">Registrarme</button>



</form>

<p class="formulario-enlace">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>

</section>
</main>
</body>
</html>
