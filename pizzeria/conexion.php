<?php
// ⚠️ ESTO DEBE ESTAR AL PRINCIPIO DE TU ARCHIVO
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Tus credenciales confirmadas
$conexion = mysqli_connect(
    hostname: "localhost", 
    username: "root",
    password: "alumno", // ⚠️ ¡Verifica que esta sea tu contraseña real!
    database: "pizza"
) or die("Problemas con la conexión: " . mysqli_connect_error());

// Agrega esto para probar la conexión
 
// Si la conexión falla, se mostrará el mensaje de error del 'die()'.
?>