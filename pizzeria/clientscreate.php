<?php
include 'conexion.php'; 
session_start();

// 1. SEGURIDAD
if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header("Location: login.php");
    exit();
}

// Variables iniciales
$cliente_id = 0;
$nombre = '';
$telefono = '';
$direccion = '';
$titulo = 'Crear Nuevo Cliente';
$mensaje = '';

// 2. Lógica para OBTENER datos para EDICIÓN
if (isset($_GET['id'])) {
    $cliente_id = (int)$_GET['id'];
    $titulo = 'Editar Cliente ID: ' . $cliente_id;
    
    $sql_fetch = "SELECT nombre, telefono, direccion FROM clientes WHERE id = $cliente_id";
    $result_fetch = mysqli_query($conexion, $sql_fetch);
    
    if ($result_fetch && mysqli_num_rows($result_fetch) == 1) {
        $cliente = mysqli_fetch_assoc($result_fetch);
        $nombre = $cliente['nombre'];
        $telefono = $cliente['telefono'];
        $direccion = $cliente['direccion'];
    } else {
        $mensaje = "<div class='error-msg'>❌ Cliente no encontrado.</div>";
        $cliente_id = 0; // Forzar a modo creación si el ID no existe
    }
}

// 3. Lógica para PROCESAR el formulario (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cliente_id_post = (int)$_POST['cliente_id'];
    $nombre_post = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $telefono_post = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $direccion_post = mysqli_real_escape_string($conexion, $_POST['direccion']);
    
    if ($cliente_id_post > 0) {
        // MODO EDICIÓN
        $sql_guardar = "UPDATE clientes SET 
                        nombre='$nombre_post', 
                        telefono='$telefono_post', 
                        direccion='$direccion_post' 
                        WHERE id=$cliente_id_post";
        
        if (mysqli_query($conexion, $sql_guardar)) {
            $mensaje = "<div class='success-msg'>✅ Cliente actualizado con éxito.</div>";
            // Recargar datos actualizados en el formulario
            $nombre = $nombre_post;
            $telefono = $telefono_post;
            $direccion = $direccion_post;
        } else {
            $mensaje = "<div class='error-msg'>❌ Error al actualizar: " . mysqli_error($conexion) . "</div>";
        }
    } else {
        // MODO CREACIÓN
        $sql_guardar = "INSERT INTO clientes (nombre, telefono, direccion) 
                         VALUES ('$nombre_post', '$telefono_post', '$direccion_post')";
        
        if (mysqli_query($conexion, $sql_guardar)) {
            $id_nuevo = mysqli_insert_id($conexion);
            $mensaje = "<div class='success-msg'>✅ Cliente creado con éxito (ID: $id_nuevo). Redirigiendo...</div>";
            // Redirigir al listado después de crear
            header("Location: clientslist.php");
            exit();
        } else {
            $mensaje = "<div class='error-msg'>❌ Error al crear: " . mysqli_error($conexion) . "</div>";
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
        /* ========================================================= */
        /* --- 1. PALETA Y FUENTES (La Fogata) --- */
        /* ========================================================= */
        :root {
            --color-rojo-tomate: #D93043;    
            --color-verde-albahaca: #2A9D8F; 
            --color-amarillo-queso: #F4D35E; 
            --color-beige-masa: #F1E0C5;     
            --color-marron-horno: #8D6E63;   /* Color principal Clientes/Botón Guardar */
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
            align-items: center; 
            padding: 40px 20px;
            background-image: linear-gradient(135deg, #101010 0%, #303030 100%); 
        }

        .container {
            width: 100%;
            max-width: 600px;
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
            color: var(--color-marron-horno); 
            margin-bottom: 30px;
            font-size: 3em;
            text-align: center;
            letter-spacing: 2px;
        }
        
        /* --- 3. MENSAJES DE FEEDBACK --- */
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

        /* --- 4. ESTILOS DEL FORMULARIO --- */
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
        .form-group input[type="text"] {
            width: 100%;
            padding: 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 1em;
            /* Estilo Input Glassmorphism */
            background-color: rgba(255, 255, 255, 0.1); 
            color: white;
            transition: border-color 0.2s, background-color 0.2s;
        }
        .form-group input[type="text"]:focus {
            border-color: var(--color-marron-horno);
            background-color: rgba(255, 255, 255, 0.2); 
            outline: none;
            box-shadow: 0 0 10px rgba(141, 110, 99, 0.5);
        }
        
        /* Botón de Guardar/Submit */
        .btn-submit {
            background-color: var(--color-marron-horno);
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
            box-shadow: 0 5px 15px rgba(141, 110, 99, 0.5);
        }
        .btn-submit:hover {
            background-color: #6d554a;
            transform: translateY(-2px);
        }

        /* Enlace de Volver */
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
        <h1><i class="fas fa-user-plus"></i> <?php echo $titulo; ?></h1>
        
        <?php 
        // Reemplazamos las etiquetas <p> originales por las <div> estilizadas
        echo str_replace(['<p class=', '</p>'], ['<div class=', '</div>'], $mensaje); 
        ?>
        
        <form action="clientscreate.php" method="POST">
            <input type="hidden" name="cliente_id" value="<?php echo $cliente_id; ?>">

            <div class="form-group">
                <label for="nombre">Nombre Completo:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono:</label>
                <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($telefono); ?>" required>
            </div>

            <div class="form-group">
                <label for="direccion">Dirección:</label>
                <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($direccion); ?>" required>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> 
                <?php echo ($cliente_id > 0) ? 'Actualizar Cliente' : 'Crear Cliente'; ?>
            </button>
        </form>

        <div class="volver-listado">
            <a href="clientslist.php"><i class="fas fa-arrow-left"></i> Volver al Listado de Clientes</a>
        </div>
    </div>

</body>
</html>

<?php
mysqli_close($conexion);
?>