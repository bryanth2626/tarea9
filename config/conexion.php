<?php

$host = "localhost";
$dbname = "productora_audiovisual";
$user = "root";
$pass = "";

try {

    $conexion = new mysqli($host, $user, $pass, $dbname);

    if ($conexion->connect_error) {
        throw new Exception($conexion->connect_error);
    }

    $conexion->set_charset("utf8");

} catch (Exception $e) {

    die("Error de conexión: " . $e->getMessage());

}