<?php
session_start();

if (!empty($_SESSION['id_usuario'])) {
    header('Location: ../index.php');
    exit();
}

// Mensajes que puede mostrar la página según el resultado del login o del registro.
$mensajes = [
    'credenciales' => 'Usuario o contraseña incorrectos.',
    'bd' => 'No se pudo iniciar sesión. Inténtalo de nuevo más tarde.',
    'registro' => 'Cuenta creada. Ya puedes iniciar sesión.'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <link rel="icon" type="image/png" href="../assets/icons/holonet-favicon.png?v=3.0">
    <link rel="stylesheet" href="../assets/styles/style.css?v=3.1">
</head>
<body class="pagina-formulario">
    <main class="formulario-contenido">
        <section class="formulario-panel">
            <h1>Iniciar sesión</h1>
            <p>Introduce tu usuario y contraseña para acceder a tu cuenta.</p>

            <?php
    // Si hay un código de error conocido, muestra el mensaje correspondiente.
    if (isset($_GET['error'], $mensajes[$_GET['error']])) {
        // Escapa caracteres especiales para evitar interpretar el mensaje como HTML.
        echo '<p role="alert">' . $mensajes[$_GET['error']] . '</p>';
    // registro=ok indica que el usuario acaba de crear su cuenta.
    } elseif (isset($_GET['registro']) && $_GET['registro'] === 'ok') {
        echo '<p>' . $mensajes['registro'] . '</p>';
    }
            ?>

            <!-- Envía el username y la contraseña al procesador de acceso. -->
            <form class="formulario-cuenta" action="../proc/login.php" method="POST">
                <label for="username">Usuario:</label>
                <input type="text" id="username" name="username">

                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password">

                <button type="submit">Entrar</button>
            </form>

            <p class="formulario-enlace">¿Todavía no tienes cuenta? <a href="registro.php">Regístrate</a></p>
        </section>
    </main>
</body>
</html>
