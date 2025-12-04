<?php
include 'conexion.php'; 
session_start();

// 1. SEGURIDAD: Redirigir si no está logueado
if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header("Location: login.php");
    exit();
}

// 2. Comprobar método POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: pos.php"); // Redirigir si no se accede por POST
    exit();
}

// Obtener el ID del trabajador logueado
$id_trabajador = $_SESSION['id_usuario']; 

// 3. Obtener y sanitizar datos del POST
$cliente_id = (int)($_POST['cliente_id'] ?? 1); // Usar cliente 1 por defecto (PENDIENTE DE USAR EN DB)
$subtotal_final = filter_var($_POST['subtotal_final'] ?? 0, FILTER_VALIDATE_FLOAT);
$iva_final = filter_var($_POST['iva_final'] ?? 0, FILTER_VALIDATE_FLOAT);
$total_final = filter_var($_POST['total_final'] ?? 0, FILTER_VALIDATE_FLOAT);
$productos_json = $_POST['pedido_json'] ?? '[]'; 

$productos_vendidos = json_decode($productos_json, true);

// Validaciones básicas
if ($total_final <= 0 || empty($productos_vendidos)) {
    // Usamos 'mensaje_pos' para la sesión de feedback
    $_SESSION['mensaje_pos'] = "<p class='error-msg'>❌ Error: El total de la venta es cero o no hay productos seleccionados.</p>";
    header("Location: pos.php");
    exit();
}

// 4. INICIAR TRANSACCIÓN
mysqli_autocommit($conexion, FALSE);
$todo_ok = true;
$error_msg = "";

// 5. INSERTAR en la tabla VENTAS (Encabezado)
// Columnas esperadas en 'ventas' (según tu DB): id, fecha, idTrab, subtotal, IVA, total
$fecha_venta = date('Y-m-d H:i:s');

// **** CORRECCIÓN DE AUTO_INCREMENT ****
// Se omite la columna 'id' para que el motor de base de datos la genere automáticamente.
// También se omite idCliente, ya que no existe en tu tabla 'ventas' actual.
$sql_venta_encabezado = "INSERT INTO ventas (fecha, idTrab, subtotal, IVA, total) 
                         VALUES (?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conexion, $sql_venta_encabezado);

if ($stmt) {
    // Tipos de parámetros: s (fecha), i (idTrab), d (subtotal), d (IVA), d (total)
    mysqli_stmt_bind_param($stmt, "siddd", 
        $fecha_venta, 
        $id_trabajador, 
        $subtotal_final, 
        $iva_final, 
        $total_final
    );

    if (mysqli_stmt_execute($stmt)) {
        $id_venta_insertada = mysqli_insert_id($conexion);
        mysqli_stmt_close($stmt);

        // 6. INSERTAR en la tabla VENTASDET (Detalle de la venta)
        // Columnas esperadas en 'ventasdet': idVenta, idProd, cantidad, precio (precio unitario)
        $sql_venta_detalle = "INSERT INTO ventasdet (idVenta, idProd, cantidad, precio) 
                              VALUES (?, ?, ?, ?)";
        $stmt_detalle = mysqli_prepare($conexion, $sql_venta_detalle);

        if ($stmt_detalle) {
            foreach ($productos_vendidos as $producto) {
                $idProd = (int)($producto['id'] ?? 0);
                $cantidad = (int)($producto['cantidad'] ?? 0);
                $precio_unitario = filter_var($producto['precio'] ?? 0, FILTER_VALIDATE_FLOAT);
                
                if ($idProd > 0 && $cantidad > 0) {
                    // Tipos de parámetros: i (idVenta), i (idProd), i (cantidad), d (precio unitario)
                    mysqli_stmt_bind_param($stmt_detalle, "iiid", 
                        $id_venta_insertada, 
                        $idProd, 
                        $cantidad, 
                        $precio_unitario
                    );
                    
                    if (!mysqli_stmt_execute($stmt_detalle)) {
                        $todo_ok = false;
                        $error_msg = "Error al insertar el detalle. Producto ID: $idProd. Error: " . mysqli_stmt_error($stmt_detalle);
                        break; 
                    }
                }
            }
            mysqli_stmt_close($stmt_detalle);
        } else {
            $todo_ok = false;
            $error_msg = "Error al preparar la sentencia de detalle: " . mysqli_error($conexion);
        }
    } else {
        $todo_ok = false;
        $error_msg = "Error al ejecutar la sentencia de encabezado: " . mysqli_stmt_error($stmt);
    }
} else {
    $todo_ok = false;
    $error_msg = "Error al preparar la sentencia de encabezado: " . mysqli_error($conexion);
}

// 7. FINALIZAR TRANSACCIÓN
if ($todo_ok) {
    mysqli_commit($conexion);
    $_SESSION['mensaje_pos'] = "<p class='success-msg'>✅ Venta #$id_venta_insertada registrada! Total: $$total_final. (Haz clic para empezar nueva venta)</p>";
} else {
    mysqli_rollback($conexion);
    $_SESSION['mensaje_pos'] = "<p class='error-msg'>❌ ERROR al procesar la venta. La transacción fue revertida. Mensaje: $error_msg</p>";
}

mysqli_autocommit($conexion, TRUE); // Restaurar autocommit
mysqli_close($conexion);

header("Location: pos.php");
exit();
?>