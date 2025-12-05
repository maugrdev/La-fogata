<?php
include 'conexion.php'; 
session_start();

// 1. SEGURIDAD
if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header("Location: login.php");
    exit();
}

// Variables iniciales
$trabajador_id = 0;
$nombre = '';
$puesto = '';
$telefono = '';
$psem = ''; 
$titulo = 'Registrar Nuevo Trabajador';
$mensaje = '';

// 2. Lógica para OBTENER datos para EDICIÓN
if (isset($_GET['id'])) {
    $trabajador_id = (int)$_GET['id'];
    $titulo = 'Editar Trabajador ID: ' . $trabajador_id;
    
    // Consultar TRABAJADORES
    $sql_fetch_trab = "SELECT nombre, puesto, tel FROM trabajadores WHERE id = $trabajador_id";
    $result_trab = mysqli_query($conexion, $sql_fetch_trab);
    
    if ($result_trab && mysqli_num_rows($result_trab) == 1) {
        $trabajador = mysqli_fetch_assoc($result_trab);
        $nombre = $trabajador['nombre'];
        $puesto = $trabajador['puesto'];
        $telefono = $trabajador['tel'];

        // Consultar USUARIOS (Credenciales)
        $sql_fetch_user = "SELECT psem FROM usuarios WHERE id = $trabajador_id";
        $result_user = mysqli_query($conexion, $sql_fetch_user);
        if ($result_user && mysqli_num_rows($result_user) == 1) {
            $usuario = mysqli_fetch_assoc($result_user);
            $psem = $usuario['psem'];
        }
    } else {
        $mensaje = "<div class='error-msg'>❌ Trabajador no encontrado.</div>";
        $trabajador_id = 0; 
    }
}

// 3. Lógica para PROCESAR el formulario (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $trabajador_id_post = (int)$_POST['trabajador_id'];
    $nombre_post = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $puesto_post = mysqli_real_escape_string($conexion, $_POST['puesto']);
    $telefono_post = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $psem_post = mysqli_real_escape_string($conexion, $_POST['psem']);
    
    // --- 3A. Guardar/Actualizar en TRABAJADORES ---
    if ($trabajador_id_post > 0) {
        // MODO EDICIÓN
        $sql_trabajador = "UPDATE trabajadores SET 
                            nombre='$nombre_post', 
                            puesto='$puesto_post', 
                            tel='$telefono_post'
                            WHERE id=$trabajador_id_post";
        mysqli_query($conexion, $sql_trabajador);
        $mensaje_trabajador = "datos personales actualizados.";
        $id_nuevo = $trabajador_id_post;
    } else {
        // MODO CREACIÓN
        $sql_trabajador = "INSERT INTO trabajadores (nombre, puesto, tel) 
                            VALUES ('$nombre_post', '$puesto_post', '$telefono_post')";
        
        if (mysqli_query($conexion, $sql_trabajador)) {
            $id_nuevo = mysqli_insert_id($conexion);
            $mensaje_trabajador = "Trabajador registrado (ID: $id_nuevo).";
        } else {
            $mensaje = "<div class='error-msg'>❌ Error al crear trabajador: " . mysqli_error($conexion) . "</div>";
            $id_nuevo = 0; 
        }
    }

    // --- 3B. Guardar/Actualizar en USUARIOS (Credenciales) ---
    if ($id_nuevo > 0 && !empty($psem_post)) {
        $sql_usuario_check = "SELECT id FROM usuarios WHERE id = $id_nuevo";
        $result_usuario = mysqli_query($conexion, $sql_usuario_check);
        
        if (mysqli_num_rows($result_usuario) > 0) {
            $sql_usuario = "UPDATE usuarios SET nombre='$nombre_post', psem='$psem_post' WHERE id=$id_nuevo";
            $mensaje_usuario = "credenciales de usuario actualizadas.";
        } else {
            $sql_usuario = "INSERT INTO usuarios (id, nombre, psem) VALUES ($id_nuevo, '$nombre_post', '$psem_post')";
            $mensaje_usuario = "credenciales de usuario creadas.";
        }

        if (mysqli_query($conexion, $sql_usuario)) {
            $mensaje = "<div class='success-msg'>✅ $mensaje_trabajador y $mensaje_usuario Redirigiendo...</div>";
            header("Location: trabajadoreslist.php");
            exit();
        } else {
            $mensaje = "<div class='error-msg'>❌ Error al gestionar usuario: " . mysqli_error($conexion) . "</div>";
        }
    } elseif ($id_nuevo > 0) {
        $mensaje = "<div class='success-msg'>✅ $mensaje_trabajador (Credenciales no modificadas/creadas).</div>";
        
        // Recargar datos actualizados en el formulario
        $trabajador_id = $id_nuevo;
        $nombre = $nombre_post;
        $puesto = $puesto_post;
        $telefono = $telefono_post;
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
            --color-verde-albahaca: #2A9D8F; /* Color principal Trabajadores/Botón Guardar */
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
            align-items: center; 
            padding: 40px 20px;
            background-image: linear-gradient(135deg, #101010 0%, #303030 100%); 
        }

        .container {
            width: 100%;
            max-width: 700px;
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
            color: var(--color-verde-albahaca); 
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
        
        /* Encabezado de Sección */
        .section-header { 
            margin-top: 30px; 
            border-bottom: 2px solid var(--color-amarillo-queso); 
            padding-bottom: 5px; 
            margin-bottom: 25px; 
            color: var(--color-amarillo-queso); 
            font-size: 1.4em; 
            font-weight: 800; 
            text-transform: uppercase;
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
            border-color: var(--color-verde-albahaca);
            background-color: rgba(255, 255, 255, 0.2); 
            outline: none;
            box-shadow: 0 0 10px rgba(42, 157, 143, 0.5);
        }
        
        /* Texto pequeño (hint) */
        small {
            color: var(--color-amarillo-queso) !important;
            display: block;
            margin-top: 5px;
            font-style: italic;
        }
        
        /* Botón de Guardar/Submit */
        .btn-submit {
            background-color: var(--color-verde-albahaca);
            color: white;
            padding: 15px 25px;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            font-size: 1.2em;
            transition: background-color 0.2s, transform 0.1s;
            width: 100%;
            margin-top: 20px;
            box-shadow: 0 5px 15px rgba(42, 157, 143, 0.5);
        }
        .btn-submit:hover {
            background-color: #228b7e;
            transform: translateY(-2px);
        }

        /* Enlace de Volver */
        .volver-listado {
            display: block;
            margin-top: 30px;
            text-align: center;
        }
        .volver-listado a {
            color: var(--color-marron-horno);
            text-decoration: none;
            font-weight: 600;
            font-size: 1em;
            padding: 8px 15px;
            border-radius: 5px;
            transition: color 0.2s;
        }
        .volver-listado a:hover {
            color: var(--color-beige-masa);
        }
    </style>
</head>
<body>

    <div class="container">
        <h1><i class="fas fa-user-tie"></i> <?php echo $titulo; ?></h1>
        
        <?php 
        // Reemplazamos las etiquetas <p> originales por las <div> estilizadas
        echo str_replace(['<p class=', '</p>'], ['<div class=', '</div>'], $mensaje); 
        ?>
        
        <form action="trabajadorescreate.php" method="POST">
            <input type="hidden" name="trabajador_id" value="<?php echo $trabajador_id; ?>">

            <div class="section-header">Datos Personales</div>
            
            <div class="form-group">
                <label for="nombre">Nombre Completo:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="puesto">Puesto/Rol:</label>
                <input type="text" id="puesto" name="puesto" value="<?php echo htmlspecialchars($puesto); ?>" required>
            </div>

            <div class="form-group">
                <label for="telefono">Teléfono:</label>
                <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($telefono); ?>">
            </div>

            <div class="section-header">Credenciales de Acceso (Usuario)</div>

            <div class="form-group">
                <label for="psem">Código de Acceso / Contraseña (psem):</label>
                <input type="text" id="psem" name="psem" value="<?php echo htmlspecialchars($psem); ?>" placeholder="Escribe un código o contraseña (ej: SC_001)">
                <small>Este campo se usa para la tabla `usuarios` y permite al empleado acceder al sistema POS.</small>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> 
                <?php echo ($trabajador_id > 0) ? 'Actualizar Trabajador' : 'Crear Trabajador'; ?>
            </button>
        </form>

        <div class="volver-listado">
            <a href="trabajadoreslist.php"><i class="fas fa-arrow-left"></i> Volver al Listado de Trabajadores</a>
        </div>
    </div>

</body>
</html>

<?php
mysqli_close($conexion);
?>