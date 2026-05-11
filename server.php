<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';

use Infrastructure\Database\AR_MysqliConnection;
use Infrastructure\ActiveRecord;

// Conexion a DB
// 1. Crear la conexión nativa
// $mysqli = new mysqli('localhost', 'root', 'tu_password', 'erp_muebleria');

// // Verificar si hubo error al conectar a nivel de servidor
// if ($mysqli->connect_error) {
//     die("Error de conexión: " . $mysqli->connect_error);
// }

// // 2. Envolverla en nuestro Adaptador
// $dbAdapter = new AR_MysqliConnection($mysqli);

ActiveRecord::setDB( 
    'default',  
    new AR_MysqliConnection(
        getDBConnection()
    )
);
