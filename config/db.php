<?php
function getDBConnection(): mysqli {
    // Configuración de la conexión
    $host = "localhost";     // Servidor de la base de datos
    $user = "root";          // Usuario de la base de datos
    $password = "root";          // Contraseña del usuario
    $dbname = "bienesraices_crud";     // Nombre de la base de datos

    // Crear conexión
    $conn = new mysqli($host, $user, $password, $dbname);

    // Verificar errores
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    // Opcional: establecer charset
    $conn->set_charset("utf8mb4");

    return $conn;
}
