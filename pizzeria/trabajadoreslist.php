<?php
include 'conexion.php'; 
session_start();

// 1. SEGURIDAD: Redirigir si no está logueado
if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header("Location: login.php");
    exit();
}

$mensaje = '';

// 2. Lógica para ELIMINAR Trabajador
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_trabajador = (int)$_GET['id'];
    
    // 1. Intentar eliminar de la tabla de usuarios (credenciales de acceso)
    $sql_delete_usuario = "DELETE FROM usuarios WHERE id = $id_trabajador";
    mysqli_query($conexion, $sql_delete_usuario);

    // 2. Intentar eliminar de la tabla de trabajadores
    $sql_delete = "DELETE FROM trabajadores WHERE id = $id_trabajador";
    if (mysqli_query($conexion, $sql_delete)) {
        $mensaje = "<div class='success-msg'><i class='fas fa-check-circle'></i> Trabajador y sus credenciales eliminados correctamente.</div>";
    } else {
        // Verificar error de clave foránea (si el trabajador tiene ventas asociadas)
        if (mysqli_errno($conexion) == 1451) {
            $mensaje = "<div class='error-msg'><i class='fas fa-exclamation-triangle'></i> ERROR: No se puede eliminar el trabajador porque tiene ventas registradas (Error FK 1451).</div>";
        } else {
            $mensaje = "<div class='error-msg'><i class='fas fa-times-circle'></i> ERROR al eliminar: " . mysqli_error($conexion) . "</div>";
        }
    }
}

// 3. CONSULTAR y LISTAR Trabajadores
$sql_trabajadores = "SELECT id, nombre, puesto, tel FROM trabajadores ORDER BY nombre ASC";
$resultado_trabajadores = mysqli_query($conexion, $sql_trabajadores);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>👷 Gestión de Trabajadores</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* ========================================================= */
        /* --- 1. PALETA Y FUENTES (La Fogata) --- */
        /* ========================================================= */
        :root {
            --color-rojo-tomate: #D93043;    /* Eliminar */
            --color-verde-albahaca: #2A9D8F; /* Crear/Guardar */
            --color-amarillo-queso: #F4D35E; /* Títulos/Énfasis */
            --color-beige-masa: #F1E0C5;     
            --color-marron-horno: #8D6E63;   /* Editar/Fondo rústico */
            --color-negro-carbon: #101010;   /* Fondo principal (Oscuro) */
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

        /* --- 3. MENSAJES Y ACCIONES DE CABECERA --- */
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 0 10px;
        }
        
        .message-area {
            min-width: 450px;
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
        
        /* --- 4. TABLA DE TRABAJADORES --- */
        .tabla-trabajadores {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            margin-top: 20px;
            color: var(--color-negro-carbon);
            text-align: left;
        }
        .tabla-trabajadores th {
            background-color: var(--color-marron-horno); 
            color: white;
            padding: 15px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.9em;
        }
        .tabla-trabajadores tr:first-child th:first-child { border-top-left-radius: 10px; }
        .tabla-trabajadores tr:first-child th:last-child { border-top-right-radius: 10px; }
        
        .tabla-trabajadores td {
            background-color: var(--color-gris-claro-suave); 
            padding: 15px;
            font-size: 0.95em;
            vertical-align: top;
        }
        .tabla-trabajadores tbody tr {
            transition: all 0.2s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .tabla-trabajadores tbody tr:hover {
            background-color: white;
            transform: scale(1.005);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
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
            if (confirm("¿Estás seguro de que deseas eliminar a este trabajador? Sus credenciales de usuario también serán eliminadas.")) {
                // Asegúrate de usar el nombre de archivo correcto si es diferente a 'trabajadoreslist.php'
                window.location.href = 'trabajadoreslist.php?action=delete&id=' + id;
            }
        }
    </script>
</head>
<body>

    <div class="container">
        <h1><i class="fas fa-user-tie"></i> Gestión de Trabajadores</h1>
        
        <div class="header-actions">
            <a href="trabajadorescreate.php" class="btn-crear"><i class="fas fa-plus-circle"></i> Agregar Nuevo Trabajador</a>
            
            <div class="message-area"><?php echo $mensaje; ?></div>
        </div>
        
        <table class="tabla-trabajadores">
            <thead>
                <tr>
                    <th style="width: 5%;">ID</th>
                    <th style="width: 30%;">Nombre Completo</th>
                    <th style="width: 25%;">Puesto</th>
                    <th style="width: 20%;">Teléfono</th>
                    <th style="width: 10%;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($resultado_trabajadores) > 0): ?>
                    <?php while($trabajador = mysqli_fetch_assoc($resultado_trabajadores)): ?>
                        <tr>
                            <td><?php echo $trabajador['id']; ?></td>
                            <td><?php echo htmlspecialchars($trabajador['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($trabajador['puesto']); ?></td>
                            <td><?php echo htmlspecialchars($trabajador['tel']); ?></td>
                            <td class="acciones">
                                <a href="trabajadorescreate.php?id=<?php echo $trabajador['id']; ?>" class="btn-edit" title="Editar"><i class="fas fa-edit"></i></a>
                                
                                <button onclick="confirmarEliminar(<?php echo $trabajador['id']; ?>)" class="btn-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; background-color: var(--color-gris-claro-suave); border-radius: 0 0 10px 10px;">No hay trabajadores registrados.</td>
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
mysqli_close($conexion);
?>