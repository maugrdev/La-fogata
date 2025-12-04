<?php
include 'conexion.php'; 
session_start();

// 1. SEGURIDAD: Redirigir si no está logueado
if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header("Location: login.php");
    exit();
}

$mensaje = '';

// 2. Lógica para ELIMINAR Producto (USANDO PREPARED STATEMENT)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_producto = (int)$_GET['id'];
    
    // Consulta SQL con Placeholder (?)
    $sql_delete = "DELETE FROM productos WHERE id = ?";
    
    // Preparar la declaración
    $stmt = mysqli_prepare($conexion, $sql_delete);
    
    if ($stmt) {
        // Vincular el parámetro (i = integer)
        mysqli_stmt_bind_param($stmt, "i", $id_producto);
        
        // Ejecutar la declaración
        if (mysqli_stmt_execute($stmt)) {
            $mensaje = "<div class='success-msg'><i class='fas fa-check-circle'></i> Producto eliminado correctamente.</div>";
        } else {
            // Verificar si la eliminación falló por una restricción de clave foránea
            if (mysqli_errno($conexion) == 1451) {
                $mensaje = "<div class='error-msg'><i class='fas fa-exclamation-triangle'></i> ERROR: No se puede eliminar el producto porque ya está registrado en ventasdet (clave foránea).</div>";
            } else {
                $mensaje = "<div class='error-msg'><i class='fas fa-times-circle'></i> ERROR al eliminar: " . mysqli_error($conexion) . "</div>";
            }
        }
        mysqli_stmt_close($stmt);
    } else {
         $mensaje = "<div class='error-msg'><i class='fas fa-times-circle'></i> ERROR al preparar la eliminación: " . mysqli_error($conexion) . "</div>";
    }
}

// 3. CONSULTAR y LISTAR Productos (INCLUYENDO DESCRIPCION E IMAGEN_RUTA)
$sql_productos = "SELECT id, nombre, precio, descripcion, imagen_ruta FROM productos ORDER BY nombre ASC";
$resultado_productos = mysqli_query($conexion, $sql_productos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>🍕 Gestión de Productos</title>
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
            background-color: var(--color-negro-carbon); 
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
            /* Aumentamos el ancho máximo del contenedor para dar espacio a las columnas */
            max-width: 1300px; 
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
            color: var(--color-rojo-tomate); 
            margin-bottom: 30px;
            font-size: 3.5em;
            text-align: center;
            letter-spacing: 3px;
        }

        /* --- 3. MENSAJES Y ACCIONES DE CABECERA --- */
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 0 10px;
        }
        
        .message-area {
            min-width: 350px;
        }

        /* Botón de Creación */
        .btn-crear {
            background-color: var(--color-verde-albahaca);
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1.1em;
            transition: background-color 0.2s, transform 0.1s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            border: 1px solid var(--color-verde-albahaca);
        }
        .btn-crear:hover {
            background-color: #228b7e;
            transform: translateY(-2px);
        }

        /* Mensajes de Alerta */
        .success-msg, .error-msg {
            padding: 15px;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            margin: 0;
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
        
        /* --- 4. TABLA DE PRODUCTOS --- */
        .tabla-productos {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            margin-top: 20px;
            color: var(--color-negro-carbon);
            text-align: left;
        }
        .tabla-productos th {
            background-color: var(--color-rojo-tomate); 
            color: white;
            padding: 15px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.9em;
        }
        .tabla-productos tr:first-child th:first-child { border-top-left-radius: 10px; }
        .tabla-productos tr:first-child th:last-child { border-top-right-radius: 10px; }
        
        .tabla-productos td {
            background-color: var(--color-gris-claro-suave); 
            padding: 15px;
            font-size: 0.95em;
            vertical-align: top;
        }
        .tabla-productos tbody tr {
            transition: all 0.2s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .tabla-productos tbody tr:hover {
            background-color: white;
            transform: scale(1.005);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .precio-col {
            font-weight: 700;
            color: var(--color-verde-albahaca); 
            font-size: 1.1em;
        }
        
        /* Estilos para la Imagen */
        .imagen-col {
            text-align: center; /* Centrar la imagen dentro de la celda */
            width: 8%; /* Ajustar el ancho para la imagen */
            vertical-align: middle;
        }
        .imagen-col img {
            width: 60px; /* Tamaño pequeño y consistente */
            height: 60px;
            object-fit: cover; /* Asegura que la imagen cubra el área sin deformarse */
            border-radius: 5px;
            border: 1px solid var(--color-marron-horno);
            display: inline-block;
        }
        
        /* Estilos de Descripción (Fluida) */
        .descripcion-col {
            width: 40%;
            max-width: 450px;
            white-space: normal;
            word-wrap: break-word; 
            vertical-align: top;
            line-height: 1.4; 
            display: table-cell;
        }

        /* Estilos de Acciones */
        .acciones {
            white-space: nowrap;
        }
        .acciones button, .acciones a {
            background: none;
            border: none;
            cursor: pointer;
            margin: 0 5px;
            padding: 8px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1.1em;
            transition: background-color 0.2s;
        }
        .acciones .btn-edit { color: var(--color-marron-horno); }
        .acciones .btn-delete { color: var(--color-rojo-tomate); }
        .acciones button:hover, .acciones a:hover {
            background-color: rgba(0,0,0,0.1);
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
    <script>
        function confirmarEliminar(id) {
            if (confirm("¿Estás seguro de que deseas eliminar este producto? Esto podría fallar si el producto ya está en el historial de ventas.")) {
                window.location.href = 'productosl.php?action=delete&id=' + id;
            }
        }
    </script>
</head>
<body>

    <div class="container">
        <h1><i class="fas fa-pizza-slice"></i> Gestión de Productos</h1>
        
        <div class="header-actions">
            <div class="message-area"><?php echo $mensaje; ?></div>
            <a href="productosc.php" class="btn-crear"><i class="fas fa-plus-circle"></i> Agregar Nuevo Producto</a>
        </div>
        
        <table class="tabla-productos">
            <thead>
                <tr>
                    <th style="width: 5%;">ID</th>
                    <th style="width: 15%;">Nombre</th>
                    <th style="width: 10%;">Imagen</th> <th style="width: 40%;">Descripción</th> 
                    <th style="width: 15%;">Precio</th>
                    <th style="width: 15%;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($resultado_productos) > 0): ?>
                    <?php while($producto = mysqli_fetch_assoc($resultado_productos)): ?>
                        <tr>
                            <td><?php echo $producto['id']; ?></td>
                            <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                            
                            <td class="imagen-col">
                                <?php if (!empty($producto['imagen_ruta']) && file_exists($producto['imagen_ruta'])): ?>
                                    <img src="<?php echo htmlspecialchars($producto['imagen_ruta']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                <?php else: ?>
                                    <i class="fas fa-image" title="Sin imagen" style="color: var(--color-marron-horno); font-size: 24px;"></i>
                                <?php endif; ?>
                            </td>
                            <td class="descripcion-col"><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                            <td class="precio-col">$<?php echo number_format($producto['precio'], 2); ?></td>
                            <td class="acciones">
                                <a href="productosc.php?id=<?php echo $producto['id']; ?>" class="btn-edit" title="Editar"><i class="fas fa-edit"></i></a>
                                
                                <button onclick="confirmarEliminar(<?php echo $producto['id']; ?>)" class="btn-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; background-color: var(--color-gris-claro-suave); border-radius: 0 0 10px 10px;">No hay productos registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="volver-menu">
            <a href="index.php"><i class="fas fa-arrow-left"></i> Volver al Menú Principal</a>
        </div>
    </div>

</body>
</html>

<?php
// Cerrar la conexión
mysqli_close($conexion);
?>