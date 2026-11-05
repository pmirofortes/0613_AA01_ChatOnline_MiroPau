<?php
// Inicia o recupera la sesión que se va a cerrar.
session_start();
// Elimina las variables guardadas en la sesión y destruye la sesión en el servidor.
session_unset();
session_destroy();

// Al cerrar sesión, vuelve a la pantalla de acceso.
header('Location: ../view/login.php');
exit();