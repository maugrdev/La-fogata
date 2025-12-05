<?php
// ==========================================================
// === CRÍTICO: ¡DEBEN USAR COMILLAS SIMPLES! ===
// ==========================================================
$host = 'dpg-d4ouedbe5dus73cjc0e0-a'; // Corregido: ¡Le faltaban comillas!
$usuario = 'root'; // Corregido: ¡Le faltaban comillas!
$password = 'ahFPq1Tu2Sj8QN9FgkYuS3fxAMmvtvwt'; // Corregido: ¡Le faltaban comillas!
$dbname = 'pizza_3j9z'; // Corregido: ¡Le faltaban comillas!
$port = 5432; 

// --- CONEXIÓN USANDO FUNCIONES DE POSTGRESQL (pg_connect) ---
$conn_string = "host={$host} port={$port} dbname={$dbname} user={$usuario} password={$password}";
$conexion = pg_connect($conn_string);

if (!$conexion) {
    // Si la conexión falla, ahora mostrará un mensaje de error útil
    die("Error de conexión a PostgreSQL: " . pg_last_error());
}

// Opcional: Establecer el juego de caracteres (Encoding)
pg_set_client_encoding($conexion, "UTF8");

// ... (restos de tu código)
?>
