<?php
include 'conexion.php'; 
session_start();

// 1. SEGURIDAD: Redirigir si no está logueado
if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header("Location: login.php");
    exit();
}

$mensaje = '';
$fecha_actual = date("Y-m-d");

// 2. Lógica para REALIZAR el Corte de Caja (Guardar el total del día)
if (isset($_POST['action']) && $_POST['action'] == 'corte') {
    // Nota: El uso de mysqli_real_escape_string es seguro, pero se recomienda mysqli_prepare
    // para consultas con variables (lo mantengo como estaba en tu código original).
    $venta_dia = mysqli_real_escape_string($conexion, $_POST['venta_dia']);
    $fecha_corte = mysqli_real_escape_string($conexion, $_POST['fecha_corte']);

    // Verificar si ya existe un corte para la fecha
    $sql_check = "SELECT id FROM cortecaja WHERE fecha = '$fecha_corte'";
    $result_check = mysqli_query($conexion, $sql_check);

    if (mysqli_num_rows($result_check) == 0) {
        // No existe, se inserta el corte
        $sql_insert = "INSERT INTO cortecaja (fecha, VentaDia) VALUES ('$fecha_corte', '$venta_dia')";
        if (mysqli_query($conexion, $sql_insert)) {
            // Se usa redirección para evitar reenvío de formulario (Post/Redirect/Get pattern)
            header("Location: corte_caja.php?success=1");
            exit(); 
        } else {
            $mensaje = "<div class='error-msg'><i class='fas fa-times-circle'></i> ERROR al guardar el corte: " . mysqli_error($conexion) . "</div>";
        }
    } else {
        $mensaje = "<div class='error-msg'><i class='fas fa-exclamation-triangle'></i> Ya existe un Corte de Caja registrado para la fecha $fecha_corte.</div>";
    }
}

// 3. Obtener el total de ventas para la FECHA ACTUAL (desde la tabla VENTAS)
$sql_ventas_hoy = "SELECT SUM(total) as total_dia 
                   FROM ventas 
                   WHERE DATE(fecha) = '$fecha_actual'";
$resultado_ventas_hoy = mysqli_query($conexion, $sql_ventas_hoy);
$fila_ventas_hoy = mysqli_fetch_assoc($resultado_ventas_hoy);
$total_ventas_hoy = $fila_ventas_hoy['total_dia'] ?: 0.00;

// 4. Obtener el HISTORIAL de Cortes de Caja (Tabla CORTESCAJA)
$sql_historial = "SELECT id, fecha, VentaDia FROM cortecaja ORDER BY fecha DESC";
$resultado_historial = mysqli_query($conexion, $sql_historial);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>💰 Reporte de Ventas (Corte de Caja)</title>
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
            padding: 40px 20px; 
            color: white; 
            min-height: 100vh; 
            display: flex;
            justify-content: center;
            background-image: linear-gradient(135deg, #101010 0%, #303030 100%); 
        }
        .container { 
            width: 100%;
            max-width: 1000px; 
            margin: 0 auto; 
            padding: 40px; 
            border-radius: 20px; 
            background-color: rgba(255, 255, 255, 0.05); 
            backdrop-filter: blur(10px); 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.7);
        }
        
        /* Título Principal */
        h1 { 
            font-family: 'Bebas Neue', sans-serif;
            color: var(--color-amarillo-queso); 
            margin-bottom: 30px; 
            font-size: 3.5em; 
            text-align: center; 
            letter-spacing: 3px;
            text-shadow: 0 0 10px rgba(244, 211, 94, 0.3);
        }
        
        /* Título de Sección */
        .section-title { 
            color: var(--color-gris-claro-suave); 
            margin-top: 30px; 
            border-bottom: 2px solid var(--color-rojo-tomate); 
            padding-bottom: 10px; 
            margin-bottom: 20px; 
            font-size: 2em; 
            font-weight: 700; 
            text-align: left;
        }
        
        /* Panel del Corte Actual */
        .corte-actual {
            background-color: var(--color-marron-horno); 
            border: 2px solid var(--color-amarillo-queso); 
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.5);
        }
        .corte-actual h2 {
            margin-top: 0;
            color: var(--color-beige-masa);
            font-size: 1.8em;
        }
        .corte-actual p {
            font-size: 1.1em;
            margin-bottom: 15px;
            color: rgba(255, 255, 255, 0.9);
        }
        .total-monto {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 5em; 
            font-weight: 900;
            color: var(--color-verde-albahaca); 
            margin-top: 5px;
            line-height: 1em;
            text-shadow: 0 0 15px rgba(42, 157, 143, 0.5);
        }
        
        /* Botón de Corte */
        .btn-corte {
            background-color: var(--color-verde-albahaca);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            font-size: 1.2em;
            transition: background-color 0.2s, transform 0.1s;
            margin-top: 20px;
            box-shadow: 0 4px 15px rgba(42, 157, 143, 0.5);
        }
        .btn-corte:hover {
            background-color: #228b7e;
            transform: translateY(-2px);
        }
        
        /* Estilo de la Tabla de Historial (Tarjetas Claras sobre Fondo Oscuro) */
        .tabla-historial {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px; 
            margin-top: 25px;
            text-align: left;
        }
        .tabla-historial th {
            background-color: var(--color-rojo-tomate); 
            color: white;
            padding: 15px;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: 5px 5px 0 0;
        }
        .tabla-historial td {
            background-color: var(--color-gris-claro-suave); 
            color: var(--color-negro-carbon);
            padding: 15px;
            text-align: left;
        }
        .tabla-historial tbody tr {
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            transition: transform 0.2s;
        }
        .tabla-historial tbody tr:hover {
            transform: translateY(-3px);
            background-color: white;
        }
        
        /* Mensajes de feedback */
        .success-msg, .error-msg {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
        }
        .success-msg { background-color: var(--color-verde-albahaca); color: white; border: 1px solid #1f7d73; }
        .error-msg { background-color: var(--color-rojo-tomate); color: white; border: 1px solid #a62432; }
        
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
        
        <h1><i class="fas fa-money-check-alt"></i> Reporte y Corte de Ventas</h1>
        
        <?php 
        // Muestra el mensaje de éxito después de la redirección
        if (isset($_GET['success']) && $_GET['success'] == 1) {
            $venta_corte = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT VentaDia FROM cortecaja WHERE fecha = '$fecha_actual' ORDER BY id DESC LIMIT 1"));
            $monto = number_format($venta_corte['VentaDia'] ?? 0.00, 2);
            echo "<div class='success-msg'><i class='fas fa-check-circle'></i> ¡Corte de Caja realizado exitosamente! Total: $$monto</div>";
        }
        // Muestra otros mensajes de error/advertencia
        echo $mensaje; 
        ?>

        <div class="corte-actual">
            <h2>Ventas Acumuladas del Día (<span style="color: var(--color-amarillo-queso);"><?php echo $fecha_actual; ?></span>)</h2>
            <p>Este es el monto total de ventas registradas hoy en la tabla `ventas`:</p>
            <div class="total-monto">$<?php echo number_format($total_ventas_hoy, 2); ?></div>

            <form action="corte_caja.php" method="POST"> 
                <input type="hidden" name="action" value="corte">
                <input type="hidden" name="venta_dia" value="<?php echo $total_ventas_hoy; ?>">
                <input type="hidden" name="fecha_corte" value="<?php echo $fecha_actual; ?>">
                
                <?php 
                // Verificar si ya se realizó el corte hoy para determinar qué mostrar
                $corte_realizado = mysqli_num_rows(mysqli_query($conexion, "SELECT id FROM cortecaja WHERE fecha = '$fecha_actual'"));

                if ($total_ventas_hoy > 0 && $corte_realizado == 0): 
                ?>
                    <button type="submit" class="btn-corte" onclick="return confirm('¿Confirma que desea realizar el Corte de Caja con $<?php echo number_format($total_ventas_hoy, 2); ?>?')">
                        <i class="fas fa-cash-register"></i> Realizar Corte de Caja y Guardar Monto
                    </button>
                <?php elseif ($corte_realizado > 0): ?>
                    <p style="color: var(--color-amarillo-queso); font-weight: 600; margin-top: 15px;">
                        ⚠️ El corte de caja para hoy ya fue realizado.
                    </p>
                <?php else: ?>
                    <p style="color: rgba(255, 255, 255, 0.7); margin-top: 15px;">
                        No hay ventas registradas hoy para realizar el corte.
                    </p>
                <?php endif; ?>
            </form>
        </div>

        <div class="section-title"><i class="fas fa-history"></i> Historial de Cortes Registrados</div>

        <table class="tabla-historial">
            <thead>
                <tr>
                    <th style="border-top-left-radius: 10px;">ID</th>
                    <th>Fecha de Corte</th>
                    <th style="border-top-right-radius: 10px;">Venta Registrada ($)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($resultado_historial) > 0): ?>
                    <?php while($corte = mysqli_fetch_assoc($resultado_historial)): ?>
                        <tr>
                            <td><?php echo $corte['id']; ?></td>
                            <td><?php echo htmlspecialchars($corte['fecha']); ?></td>
                            <td style="font-weight: 700; color: var(--color-verde-albahaca);">$<?php echo number_format($corte['VentaDia'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center; background-color: var(--color-gris-claro-suave); border-radius: 0 0 10px 10px;">No hay cortes de caja registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="volver-menu">
            <a href="interfaz.php"><i class="fas fa-arrow-left"></i> Volver al Menú Principal</a>
        </div>
    </div>

</body>
</html>

<?php
mysqli_close($conexion);
?>
