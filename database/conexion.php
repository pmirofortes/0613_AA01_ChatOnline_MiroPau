<?php


$dbport = 3306;
$servername = "mysql"; // Nombre del servidor
$dbusername = "daw"; // Nombre de usuario
$dbpassword = "DAW2026"; // Contraseña
$dbname = "db_whatsapp"; // Nombre de la base de datos
try {
     // Se crea una instancia de la clase PDO para establecer la conexión
    $conn = new PDO(
        "mysql:host=$servername;port=$dbport;dbname=$dbname;charset=utf8mb4",$dbusername,$dbpassword
    );

     // Se establece el modo de errores de PDO para lanzar excepciones en lugar de advertencias
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {

    // Se captura la excepción y se muestra el mensaje de error
    echo "Error en la conexión a la base de datos: " . $e->getMessage();
    die();

}
