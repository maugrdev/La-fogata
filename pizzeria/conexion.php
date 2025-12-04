<?php
// ==========================================================
// === REEMPLAZA ESTOS VALORES CON LAS CREDENCIALES DE RENDER ===
// ==========================================================
$host = dpg-d4ouedbe5dus73cjc0e0-a; 
$usuario = root; 
$password = ahFPq1Tu2Sj8QN9FgkYuS3fxAMmvtvwt; 
$dbname =pizza_3j9z; 
$port = 5432; // Puerto por defecto de PostgreSQL (de Render)


// --- CONEXIÓN USANDO FUNCIONES DE POSTGRESQL (pg_connect) ---
$conn_string = "host={$host} port={$port} dbname={$dbname} user={$usuario} password={$password}";
$conexion = pg_connect($conn_string);

if (!$conexion) {
    // Si la conexión falla, detiene la ejecución y muestra el error.
    // Esto es útil para depurar en el log de Render.
    die("Error de conexión a PostgreSQL: " . pg_last_error());
}

// Opcional: Establecer el juego de caracteres (Encoding)
pg_set_client_encoding($conexion, "UTF8");

// NOTA IMPORTANTE:
// Si tu código de menú usa funciones como 'mysqli_query', 'mysqli_fetch_assoc', etc.,
// tendrás que cambiarlas a 'pg_query', 'pg_fetch_assoc', etc., en todos los archivos PHP.
?>
