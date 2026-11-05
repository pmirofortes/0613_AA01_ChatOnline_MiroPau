<?php

session_start();

if (empty($_SESSION['id_usuario'])) {
    header('Location: view/login.php');
    exit();
}

header('Location: view/conversaciones.php');
exit();
