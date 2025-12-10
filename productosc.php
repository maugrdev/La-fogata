<?php
include 'conexion.php'; 
session_start();

// 1. SEGURIDAD
if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header("Location: login.php");
    exit();
}

// Directorio donde se guardarán las imágenes
$upload_dir = 'uploads/';
// Asegúrate de que la carpeta exista y tenga permisos de escritura (chmod 777 temporalmente para prueba, 
// luego ajusta a un valor seguro como 755 o el que recomiende tu hosting).
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Variables iniciales
$producto_id = 0;
$nombre = '';
$precio = '';
$descripcion = '';
$imagen_ruta_actual = ''; // NUEVO: Para guardar la ruta existente
$titulo = 'Crear Nuevo Producto';
$mensaje = '';

// 2. Lógica para OBTENER datos para EDICIÓN
if (isset($_GET['id'])) {
    $producto_id = (int)$_GET['id'];
    $titulo = 'Editar Producto ID: ' . $producto_id;
    
    // Consulta: Incluye el NUEVO campo 'imagen_ruta'
    $sql_fetch = "SELECT nombre, precio, descripcion, imagen_ruta FROM productos WHERE id = $producto_id";
    $result_fetch = pg_query($conexion, $sql_fetch);
    
    if ($result_fetch && pg_num_rows($result_fetch) == 1) {
        $producto = pg_fetch_assoc($result_fetch);
        $nombre = $producto['nombre'];
        $precio = $producto['precio'];
        $descripcion = $producto['descripcion'];
        $imagen_ruta_actual = $producto['imagen_ruta']; // NUEVO: Obtener la ruta
    } else {
        $mensaje = "<div class='error-msg'>❌ Producto no encontrado.</div>";
        $producto_id = 0; 
    }
}

// 3. Lógica para PROCESAR el formulario (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $producto_id_post = (int)$_POST['producto_id'];
    // Sanear datos de entrada
    $nombre_post = pg_real_escape_string($conexion, $_POST['nombre']);
    $descripcion_post = pg_real_escape_string($conexion, $_POST['descripcion']);
    $precio_raw = str_replace(',', '.', $_POST['precio']); 
    $precio_post = (float)$precio_raw; 
    $imagen_ruta_post = isset($_POST['imagen_ruta_actual']) ? $_POST['imagen_ruta_actual'] : ''; // Usar la ruta existente por defecto

    // 4. Lógica de Subida de Archivos
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $file_tmp_name = $_FILES['imagen']['tmp_name'];
        $file_name = $_FILES['imagen']['name'];
        $file_size = $_FILES['imagen']['size'];
        $file_type = $_FILES['imagen']['type'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
        $max_size = 5 * 1024 * 1024; // 5 MB

        // Validaciones
        if (!in_array($file_ext, $allowed_ext)) {
            $mensaje = "<div class='error-msg'>❌ Error: Solo se permiten archivos JPG, JPEG, PNG y GIF.</div>";
        } elseif ($file_size > $max_size) {
            $mensaje = "<div class='error-msg'>❌ Error: El archivo es demasiado grande (máx 5MB).</div>";
        } else {
            // Generar un nombre único para evitar colisiones
            $new_file_name = uniqid('prod_', true) . '.' . $file_ext;
            $upload_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp_name, $upload_path)) {
                $imagen_ruta_post = $upload_path; // Guardar la nueva ruta para DB
                // Opcional: Eliminar la imagen anterior si es edición y se subió una nueva
                if ($producto_id_post > 0 && !empty($_POST['imagen_ruta_actual']) && file_exists($_POST['imagen_ruta_actual'])) {
                     // unlink($_POST['imagen_ruta_actual']); 
                }
            } else {
                $mensaje = "<div class='error-msg'>❌ Error al mover el archivo subido. Verifique permisos de la carpeta 'uploads/'.</div>";
            }
        }
    }

    // 5. Guardar/Actualizar en la Base de Datos
    if (empty($mensaje)) { // Solo procede si no hay errores de subida
        if ($producto_id_post > 0) {
            // MODO EDICIÓN
            $sql_guardar = "UPDATE productos SET 
                              nombre='$nombre_post', 
                              precio='$precio_post',
                              descripcion='$descripcion_post',
                              imagen_ruta='$imagen_ruta_post'  /* NUEVO CAMPO A ACTUALIZAR */
                              WHERE id=$producto_id_post";
            
            if (pg_query($conexion, $sql_guardar)) {
                $mensaje = "<div class='success-msg'>✅ Producto actualizado con éxito.</div>";
                // Recargar datos actualizados en el formulario
                $nombre = $nombre_post;
                $precio = $precio_post;
                $descripcion = $descripcion_post;
                $imagen_ruta_actual = $imagen_ruta_post;
            } else {
                $mensaje = "<div class='error-msg'>❌ Error al actualizar: " . pg_error($conexion) . "</div>";
            }
        } else {
            // MODO CREACIÓN
            $sql_guardar = "INSERT INTO productos (nombre, precio, descripcion, imagen_ruta) 
                              VALUES ('$nombre_post', '$precio_post', '$descripcion_post', '$imagen_ruta_post')";
            
            if (pg_query($conexion, $sql_guardar)) {
                $mensaje = "<div class='success-msg'>✅ Producto creado con éxito. Redirigiendo...</div>";
                // Redirigir al listado después de crear
                header("Location: productosl.php");
                exit();
            } else {
                $mensaje = "<div class='error-msg'>❌ Error al crear: " . pg_error($conexion) . "</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>🍕 <?php echo $titulo; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* [Se mantienen los estilos CSS que ya tenías] */
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

        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            background-color: var(--color-negro-carbon); 
            color: white;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center; 
            padding: 40px 20px;
            background-image: linear-gradient(135deg, #101010 0%, #303030 100%); 
        }

        .container {
            width: 100%;
            max-width: 600px;
            padding: 40px;
            border-radius: 20px;
            background-color: rgba(255, 255, 255, 0.05); 
            backdrop-filter: blur(10px); 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.7);
            color: white;
        }

        h1 {
            font-family: 'Bebas Neue', sans-serif;
            color: var(--color-rojo-tomate); 
            margin-bottom: 30px;
            font-size: 3em;
            text-align: center;
            letter-spacing: 2px;
        }
        
        .success-msg, .error-msg {
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

        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--color-beige-masa); 
            font-size: 1.1em;
        }
        /* Estilos para Input, Textarea y File Input */
        .form-group input[type="text"], 
        .form-group textarea,
        .form-group input[type="file"] {
            width: 100%;
            padding: 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 1em;
            background-color: rgba(255, 255, 255, 0.1); 
            color: white;
            transition: border-color 0.2s, background-color 0.2s;
            resize: vertical; 
            font-family: 'Montserrat', sans-serif; 
        }
        /* Estilo específico para el input de archivo para que se vea bien */
        .form-group input[type="file"] {
             padding: 10px; /* Un poco menos de padding para el input file */
             height: auto;
        }
        .form-group input[type="text"]:focus,
        .form-group textarea:focus,
        .form-group input[type="file"]:focus {
            border-color: var(--color-rojo-tomate);
            background-color: rgba(255, 255, 255, 0.2); 
            outline: none;
            box-shadow: 0 0 10px rgba(217, 48, 67, 0.5);
        }
        
        .imagen-preview {
            display: block;
            margin-top: 10px;
            max-width: 100px;
            height: auto;
            border-radius: 5px;
            border: 2px solid var(--color-rojo-tomate);
        }

        .btn-submit {
            background-color: var(--color-rojo-tomate);
            color: white;
            padding: 15px 25px;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            font-size: 1.2em;
            transition: background-color 0.2s, transform 0.1s;
            width: 100%;
            margin-top: 15px;
            box-shadow: 0 5px 15px rgba(217, 48, 67, 0.5);
        }
        .btn-submit:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }

        .volver-listado {
            display: block;
            margin-top: 30px;
            text-align: center;
        }
        .volver-listado a {
            color: var(--color-amarillo-queso);
            text-decoration: none;
            font-weight: 600;
            font-size: 1em;
            padding: 8px 15px;
            border-radius: 5px;
            transition: color 0.2s;
        }
        .volver-listado a:hover {
            color: white;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1><i class="fas fa-box-open"></i> <?php echo $titulo; ?></h1>
        
        <?php echo $mensaje; ?>
        
        <form action="productosc.php" method="POST" enctype="multipart/form-data"> 
            <input type="hidden" name="producto_id" value="<?php echo $producto_id; ?>">
            <input type="hidden" name="imagen_ruta_actual" value="<?php echo htmlspecialchars($imagen_ruta_actual); ?>">

            <div class="form-group">
                <label for="nombre">Nombre del Producto:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="precio">Precio ($):</label>
                <input type="text" id="precio" name="precio" value="<?php echo htmlspecialchars($precio); ?>" required pattern="[0-9]+([\.,][0-9]{1,2})?" title="Solo números y hasta dos decimales (use punto o coma)">
            </div>
            
            <div class="form-group">
                <label for="descripcion">Descripción del Producto:</label>
                <textarea id="descripcion" name="descripcion" rows="4" placeholder="Ingresa una descripción detallada del producto o ingrediente."><?php echo htmlspecialchars($descripcion); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="imagen">Imagen del Producto (JPG, PNG, GIF, máx 5MB):</label>
                <input type="file" id="imagen" name="imagen" accept=".jpg, .jpeg, .png, .gif">
                
                <?php if ($producto_id > 0 && !empty($imagen_ruta_actual)): ?>
                    <small style="color: var(--color-amarillo-queso); display: block; margin-top: 5px;">Imagen actual:</small>
                    <img src="<?php echo htmlspecialchars($imagen_ruta_actual); ?>" alt="Preview" class="imagen-preview">
                <?php endif; ?>
            </div>
            <button type="submit" class="btn-submit">
                <?php if ($producto_id > 0): ?>
                    <i class="fas fa-edit"></i> Actualizar Producto
                <?php else: ?>
                    <i class="fas fa-save"></i> Crear Producto
                <?php endif; ?>
            </button>
        </form>

        <div class="volver-listado">
            <a href="productosl.php"><i class="fas fa-arrow-left"></i> Volver al Listado de Productos</a>
        </div>
    </div>

</body>
</html>

<?php
pg_close($conexion);

?>
