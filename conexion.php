<?php
// ==========================================================
// === CRÍTICO: ¡DEBEN SER LOS VALORES EXACTOS DE RENDER! ===
// ==========================================================
$host = 'dpg-d4ouedbe5dus73cjc0e0-a.oregon-postgres.render.com'; // **¡HOST COMPLETO!**
$usuario = 'pizza_3j9z'; // **¡USUARIO CORRECTO DE POSTGRESQL!**
$password = 'ahFPq1Tu2Sj8QN9FgkYuS3fxAMmvtvwt'; 
$dbname = 'pizza_3j9z'; 
$port = 5432; 

// --- CONEXIÓN USANDO FUNCIONES DE POSTGRESQL (pg_connect) ---
$conn_string = "host={$host} port={$port} dbname={$dbname} user={$usuario} password={$password}";
$conexion = pg_connect($conn_string);

if (!$conexion) {
    die("Error de conexión a PostgreSQL: " . pg_last_error());
}
// ... (resto del código)
?>
