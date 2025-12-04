<?php
include 'conexion.php'; 
session_start();

$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = mysqli_real_escape_string($conexion, $_POST['usuario']);
    $password = mysqli_real_escape_string($conexion, $_POST['password']);
    
    // NOTA: EL CÓDIGO DE LOGIN NO USA HASHING DE CONTRASEÑAS. 
    // EN UN ENTORNO REAL, DEBES USAR password_hash() y password_verify().
    $sql = "SELECT id, nombre, psem FROM usuarios WHERE nombre = '$usuario'";
    $resultado = mysqli_query($conexion, $sql);

    if ($resultado && mysqli_num_rows($resultado) == 1) {
        $usuario_db = mysqli_fetch_assoc($resultado);
        // La comparación de contraseña es plana, según el código original:
        if ($password === $usuario_db['psem']) { 
            $_SESSION['id_usuario'] = $usuario_db['id'];
            $_SESSION['nombre_usuario'] = $usuario_db['nombre'];
            $_SESSION['autenticado'] = true;
            header("Location: index.php");
            exit();
        } else {
            // Mensaje de error usa color Rojo Tomate
            $mensaje = "<p class='error-message'>❌ Contraseña incorrecta.</p>";
        }
    } else {
        // Mensaje de error usa color Rojo Tomate
        $mensaje = "<p class='error-message'>❌ Usuario no encontrado.</p>";
    }
    mysqli_close($conexion);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso al Sistema POS - LA FOGATA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* ========================================================= */
        /* --- 1. PALETA Y FUENTES (Copiada del Menu Público) --- */
        /* ========================================================= */
        :root {
            --color-rojo-tomate: #D93043;      /* El sabor intenso del tomate */
            --color-verde-albahaca: #2A9D8F; /* Frescura y naturalidad */
            --color-amarillo-queso: #F4D35E; /* Calidez y textura */
            --color-beige-masa: #F1E0C5;      /* Base cálida y neutra */
            --color-marron-horno: #8D6E63;    /* Profundidad y autenticidad */
            --color-negro-carbon: #2D2D2D;    /* Contraste fuerte */
        }
        
        /* Usamos Montserrat para coherencia con el menú público */
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap');

        /* ========================================================= */
        /* --- 2. BASE DEL SITIO --- */
        /* ========================================================= */
        body {
            font-family: 'Montserrat', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            /* Fondo rústico oscuro (simulando madera o carbón) */
            background-color: var(--color-negro-carbon);
            color: var(--color-marron-horno);
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><defs><pattern id="p" width="100" height="100" patternUnits="userSpaceOnUse"><rect width="100" height="100" fill="%232D2D2D" /><path d="M 0 0 L 100 100 M 100 0 L 0 100" stroke="%233A3A3A" stroke-width="1" opacity="0.3" /></pattern></defs><rect width="100%" height="100%" fill="url(%23p)" /></svg>');
        }
        
        /* ========================================================= */
        /* --- 3. CAJA DE LOGIN --- */
        /* ========================================================= */
        .login-box {
            /* Fondo claro para la caja, similar a la masa de pizza */
            background: var(--color-beige-masa); 
            padding: 40px 50px;
            border-radius: 12px;
            /* Sombra sutil que evoca el horno */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            width: 380px;
            text-align: center;
            border-top: 5px solid var(--color-rojo-tomate); /* Detalle rojo en la parte superior */
        }
        
        h1 {
            /* Título en Rojo Tomate, lo más llamativo */
            color: var(--color-rojo-tomate); 
            margin-bottom: 25px;
            font-size: 2.2em;
            font-weight: 800;
        }
        
        /* Mensaje de error */
        .error-message {
            color: var(--color-rojo-tomate); 
            font-weight: 600;
            margin-bottom: 20px;
            background-color: #ffe0e0; /* Fondo muy claro para destacar el error */
            padding: 10px;
            border-radius: 5px;
        }

        /* ========================================================= */
        /* --- 4. CAMPOS Y BOTÓN PRINCIPAL (Login) --- */
        /* ========================================================= */
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid var(--color-marron-horno); /* Borde marrón para un look rústico */
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 1em;
            color: var(--color-negro-carbon);
            background-color: white; /* Interior blanco */
        }

        .btn-group {
            margin-top: 20px;
        }

        button[type="submit"] {
            /* Botón de acción principal en Verde Albahaca */
            background-color: var(--color-verde-albahaca); 
            color: white;
            padding: 14px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 1.1em;
            font-weight: 700;
            transition: background-color 0.3s ease, transform 0.2s;
            box-shadow: 0 4px 10px rgba(42, 157, 143, 0.4);
            margin-bottom: 10px; /* Espacio antes del botón de regreso */
        }
        
        button[type="submit"]:hover {
            background-color: #228b7e; /* Verde más oscuro al pasar el mouse */
            transform: translateY(-1px);
        }

        /* ========================================================= */
        /* --- 5. BOTÓN SECUNDARIO (Regreso) --- */
        /* ========================================================= */
        .btn-regresar {
            /* Botón secundario en Rojo Tomate, más sutil */
            display: inline-block;
            background-color: transparent;
            color: var(--color-rojo-tomate);
            border: 2px solid var(--color-rojo-tomate);
            padding: 10px 25px;
            border-radius: 6px;
            text-decoration: none; /* Quita el subrayado del enlace */
            font-weight: 600;
            font-size: 0.95em;
            transition: background-color 0.3s ease, color 0.3s, transform 0.2s;
            width: 100%;
            box-sizing: border-box;
        }

        .btn-regresar:hover {
            background-color: var(--color-rojo-tomate);
            color: white;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h1><i class="fas fa-lock"></i> Acceso POS</h1>
        <?php echo $mensaje; ?>
        <form action="login.php" method="POST">
            <input type="text" name="usuario" placeholder="Nombre de Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            
            <div class="btn-group">
                <button type="submit">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
                
                <a href="public_menu.php" class="btn-regresar">
                    <i class="fas fa-chevron-circle-left"></i> Volver al Menú Público
                </a>
            </div>
        </form>
    </div>
</body>
</html>