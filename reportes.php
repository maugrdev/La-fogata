<?php
include 'conexion.php'; 
session_start();

// 1. SEGURIDAD
if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header("Location: login.php");
    exit();
}

$fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-01'); // Por defecto: primer día del mes
$fecha_fin = $_POST['fecha_fin'] ?? date('Y-m-d');     // Por defecto: hoy
$ventas_detalladas = [];
$total_final = 0.00;
$mensaje = '';

// 2. Lógica de Consulta (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Consulta principal para obtener las ventas dentro del rango de fechas
    $sql_ventas = "SELECT 
                    v.id AS idVenta, 
                    v.fecha, 
                    v.total,
                    v.IVA,
                    v.subtotal,
                    t.nombre AS nombreTrabajador 
                    FROM ventas v
                    JOIN trabajadores t ON v.idTrab = t.id
                    WHERE DATE(v.fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin'
                    ORDER BY v.fecha DESC";

    $resultado_ventas = pg_query($conexion, $sql_ventas);

    if ($resultado_ventas) {
        if (pg_num_rows($resultado_ventas) > 0) {
            while ($venta = pg_fetch_assoc($resultado_ventas)) {
                $ventas_detalladas[] = $venta;
                $total_final += $venta['total'];
            }
        } else {
            $mensaje = "<div class='error-msg'><i class='fas fa-exclamation-triangle'></i> No se encontraron ventas en el rango seleccionado.</div>";
        }
    } else {
        $mensaje = "<div class='error-msg'><i class='fas fa-times-circle'></i> Error en la consulta: " . pg_error($conexion) . "</div>";
    }
} else {
    // Si no se ha enviado el formulario
    $mensaje = "<div class='info-msg'><i class='fas fa-info-circle'></i> Selecciona un rango de fechas para generar el reporte histórico.</div>";
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>📊 Reporte Histórico de Ventas (Totaléo)</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* ========================================================= */
        /* --- 1. PALETA Y FUENTES (La Fogata) --- */
        /* ========================================================= */
        :root {
            --color-rojo-tomate: #D93043;    
            --color-verde-albahaca: #2A9D8F; 
            --color-amarillo-queso: #F4D35E; 
            --color-beige-masa: #F1E0C5;     
            --color-marron-horno: #8D6E63;   
            --color-negro-carbon: #101010;   
            --color-gris-claro-suave: #ECECEC; 
        }
        
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Bebas+Neue&display=swap');

        /* ========================================================= */
        /* --- 2. BASE Y ESTRUCTURA (Glassmorphism) --- */
        /* ========================================================= */
        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            color: white;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 40px 20px;
            background-image: linear-gradient(135deg, #101010 0%, #303030 100%); 
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px;
            border-radius: 20px;
            /* Glassmorphism */
            background-color: rgba(255, 255, 255, 0.05); 
            backdrop-filter: blur(10px); 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.7);
            color: white;
        }

        /* Título Principal */
        h1 {
            font-family: 'Bebas Neue', sans-serif;
            color: var(--color-amarillo-queso); 
            margin-bottom: 30px;
            font-size: 3.5em;
            text-align: center;
            letter-spacing: 3px;
        }

        h2 {
            color: var(--color-beige-masa);
            font-size: 1.8em;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 10px;
            margin-top: 30px;
        }

        /* --- 3. FILTRO DE FECHAS --- */
        .form-filtro {
            display: flex; 
            gap: 20px; 
            align-items: flex-end; 
            margin-bottom: 30px; 
            background: rgba(0, 0, 0, 0.3); /* Oscuro semi-transparente */
            padding: 20px; 
            border-radius: 10px; 
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .form-group { flex-grow: 1; }
        .form-group label { 
            display: block; 
            font-weight: 600; 
            margin-bottom: 5px; 
            color: var(--color-gris-claro-suave); 
        }
        .form-group input[type="date"] { 
            padding: 10px; 
            border: 1px solid var(--color-marron-horno); 
            border-radius: 5px; 
            width: 100%; 
            box-sizing: border-box; 
            background-color: rgba(255, 255, 255, 0.9); /* Para que el input se vea bien */
            color: var(--color-negro-carbon);
            font-weight: 600;
        }
        .btn-generar { 
            background-color: var(--color-marron-horno); 
            color: white; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: 700; 
            transition: background-color 0.2s, transform 0.1s; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            font-size: 1.05em;
        }
        .btn-generar:hover { 
            background-color: #6d554a; 
            transform: translateY(-1px);
        }
        
        /* --- 4. RESUMEN TOTAL --- */
        .total-summary { 
            background-color: var(--color-verde-albahaca); 
            color: white; 
            padding: 20px; 
            border-radius: 10px; 
            margin-bottom: 25px; 
            font-size: 1.6em; 
            font-weight: 700; 
            text-align: center;
            box-shadow: 0 5px 15px rgba(42, 157, 143, 0.5);
        }
        .total-summary span { 
            font-family: 'Bebas Neue', sans-serif;
            font-size: 2.2em; 
            margin-left: 15px; 
            color: var(--color-amarillo-queso);
            text-shadow: 0 0 10px rgba(244, 211, 94, 0.5);
        }
        
        /* --- 5. TABLA DE DETALLE --- */
        .tabla-reporte { 
            width: 100%; 
            border-collapse: separate; 
            border-spacing: 0 10px;
            margin-top: 15px; 
            color: var(--color-negro-carbon);
            text-align: left;
        }
        .tabla-reporte th { 
            background-color: var(--color-rojo-tomate); 
            color: white; 
            padding: 15px; 
            font-weight: 700; 
            text-transform: uppercase; 
            font-size: 0.9em;
        }
        .tabla-reporte tr:first-child th:first-child { border-top-left-radius: 10px; }
        .tabla-reporte tr:first-child th:last-child { border-top-right-radius: 10px; }
        
        .tabla-reporte td { 
            background-color: var(--color-gris-claro-suave); 
            padding: 12px 15px; 
            border: none;
        }
        .tabla-reporte tbody tr {
            transition: background-color 0.2s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .tabla-reporte tbody tr:hover {
             background-color: white;
        }
        
        /* Mensajes de feedback (Ajustados al Glassmorphism) */
        .success-msg, .error-msg, .info-msg { 
            padding: 15px; 
            border-radius: 8px; 
            margin-bottom: 20px; 
            font-weight: 600; 
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }
        .success-msg { 
            background-color: rgba(42, 157, 143, 0.9); 
            color: var(--color-beige-masa); 
            border: 1px solid var(--color-verde-albahaca); 
        }
        .error-msg { 
            background-color: rgba(217, 48, 67, 0.9); 
            color: var(--color-beige-masa); 
            border: 1px solid var(--color-rojo-tomate); 
        }
        .info-msg { 
            background-color: rgba(141, 110, 99, 0.9); /* Marrón-Horno para info */
            color: white; 
            border: 1px solid var(--color-marron-horno); 
        }

        /* Botón de Volver */
        .volver-menu {
            display: block;
            margin-top: 40px;
            text-align: center;
        }
        .volver-menu a {
            color: var(--color-amarillo-queso);
            text-decoration: none;
            font-weight: 700;
            padding: 10px 20px;
            background-color: var(--color-marron-horno);
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
            transition: background-color 0.2s;
        }
        .volver-menu a:hover {
            background-color: #6d554a;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1><i class="fas fa-chart-line"></i> Reporte Histórico de Ventas (Totaléo)</h1>
        
        <?php echo $mensaje; ?>
        
        <form action="reporte_ventas.php" method="POST" class="form-filtro">
            <div class="form-group">
                <label for="fecha_inicio">Fecha de Inicio:</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>" required>
            </div>
            <div class="form-group">
                <label for="fecha_fin">Fecha Fin:</label>
                <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>" required>
            </div>
            <button type="submit" class="btn-generar"><i class="fas fa-search"></i> Generar Reporte</button>
        </form>

        <?php if (!empty($ventas_detalladas)): ?>
            
            <div class="total-summary">
                TOTAL VENDIDO EN EL PERIODO: 
                <span>$<?php echo number_format($total_final, 2); ?></span>
            </div>

            <h2>Detalle de Ventas (<?php echo $fecha_inicio; ?> al <?php echo $fecha_fin; ?>)</h2>
            <table class="tabla-reporte">
                <thead>
                    <tr>
                        <th>ID Venta</th>
                        <th>Fecha y Hora</th>
                        <th>Trabajador</th>
                        <th>Subtotal</th>
                        <th>IVA</th>
                        <th>TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventas_detalladas as $venta): ?>
                        <tr>
                            <td><?php echo $venta['idVenta']; ?></td>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($venta['fecha'])); ?></td>
                            <td><?php echo htmlspecialchars($venta['nombreTrabajador']); ?></td>
                            <td>$<?php echo number_format($venta['subtotal'], 2); ?></td>
                            <td>$<?php echo number_format($venta['IVA'], 2); ?></td>
                            <td style="font-weight: 700; color: var(--color-verde-albahaca);">$<?php echo number_format($venta['total'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php endif; ?>

        <div class="volver-menu">
            <a href="interfaz.php"><i class="fas fa-arrow-left"></i> Volver al Menú Principal</a>
        </div>
    </div>

</body>
</html>

<?php
pg_close($conexion);

?>
