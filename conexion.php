<?php
// ==========================================================
// === CRÍTICO: ¡DEBEN SER LOS VALORES EXACTOS DE RENDER! ===
// ==========================================================
$host = 'dpg-d4ouedbe5dus73cjc0e0-a.oregon-postgres.render.com'; // **¡HOST COMPLETO!**
$usuario = 'root'; // **¡USUARIO CORRECTO DE POSTGRESQL!**
$password = 'ahFPq1Tu2Sj8QN9FgkYuS3fxAMmvtvwt'; 
$dbname = 'pizza_3j9z'; 
$port = 5432; 

// --- CONEXIÓN USANDO FUNCIONES DE POSTGRESQL (pg_connect) ---
$conn_string = "host={$host} port={$port} dbname={$dbname} user={$usuario} password={$password} sslmode=require";
$conexion = pg_connect($conn_string);

if (!$conexion) {
    die("Error de conexión a PostgreSQL: " . pg_last_error());
}
else
{ echo "conexion valida";

            $venta_corte = pg_fetch_assoc(pg_query($conexion, "SELECT VentaDia FROM cortecaja"));
            $monto = number_format($venta_corte['VentaDia'] ?? 0.00, 2);
            echo "<div class='success-msg'><i class='fas fa-check-circle'></i> ¡Corte de Caja realizado exitosamente! Total: $$monto</div>";
        
        // Muestra otros mensajes de error/advertencia

}
// ... (resto del código)
?>
