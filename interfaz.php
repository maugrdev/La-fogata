<?php
// 1. GESTIÓN DE SESIÓN
session_start();

// Verifica si el usuario está autenticado, si no, lo redirige al login.
if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header("Location: login.php");
    exit();
}

// Obtiene la información del usuario de la sesión
$nombre_usuario = $_SESSION['nombre_usuario'] ?? 'Usuario';
$rol_usuario = $_SESSION['rol_usuario'] ?? 'Trabajador';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control POS</title>
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
        
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;500;700;800;900&family=Bebas+Neue&display=swap');

        /* ========================================================= */
        /* --- 2. BASE Y ESTRUCTURA (Glassmorphism) --- */
        /* ========================================================= */
        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            background-color: var(--color-negro-carbon); 
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
            /* Fondo para que el blur se note */
            background-image: linear-gradient(135deg, #101010 0%, #303030 100%); 
        }

        .panel-container {
            width: 100%;
            max-width: 1100px;
            text-align: center;
            padding: 50px 40px;
            border-radius: 20px;
            /* Clave del Glassmorphism */
            background-color: rgba(255, 255, 255, 0.05); 
            backdrop-filter: blur(10px); 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.7);
            color: white;
        }

        /* ========================================================= */
        /* --- 3. CABECERA Y TÍTULOS --- */
        /* ========================================================= */
        .bienvenida {
            font-size: 1.1em;
            margin-bottom: 5px;
            color: rgba(255, 255, 255, 0.8); 
        }
        .bienvenida strong {
            font-weight: 800;
            color: var(--color-amarillo-queso); 
            letter-spacing: 1px;
        }

        .header-title {
            margin-bottom: 60px;
        }
        .header-title h1 {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 5.5em; 
            font-weight: 900;
            margin: 0;
            color: var(--color-rojo-tomate); 
            text-transform: uppercase;
            letter-spacing: 6px;
            line-height: 1em;
            text-shadow: 0 0 20px rgba(217, 48, 67, 0.5); 
        }
        .header-title hr {
            width: 70%;
            border: none;
            height: 4px;
            background-color: var(--color-amarillo-queso); 
            margin: 10px auto 0;
            border-radius: 5px;
        }

        /* ========================================================= */
        /* --- 4. GRID DE BOTONES (3x2) --- */
        /* ========================================================= */
        .grid-botones {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px; 
            margin-bottom: 50px;
        }

        .boton-panel {
            background-color: var(--color-beige-masa); 
            border-radius: 15px; 
            padding: 35px 20px;
            text-decoration: none;
            color: var(--color-negro-carbon);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 1em;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
            border: 2px solid transparent; 
        }

        .boton-panel:hover {
            transform: translateY(-8px) scale(1.03); 
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.7);
            background-color: white; 
        }
        
        .boton-panel i {
            font-size: 4em; 
            margin-bottom: 15px;
            transition: color 0.3s;
        }
        .boton-panel span {
            text-align: center;
            line-height: 1.2;
            text-transform: uppercase;
            font-weight: 800;
        }

        /* Colores Específicos */
        .boton-pos { --hover-color: var(--color-amarillo-queso); }
        .boton-corte { --hover-color: var(--color-rojo-tomate); }
        .boton-productos { --hover-color: var(--color-rojo-tomate); }
        .boton-clientes { --hover-color: var(--color-marron-horno); }
        .boton-trabajadores { --hover-color: var(--color-marron-horno); }
        .boton-reportes { --hover-color: var(--color-verde-albahaca); }

        /* Aplicar color a Iconos/Texto y Borde al Hover */
        .boton-panel i, .boton-panel span { color: var(--hover-color); }
        .boton-panel:hover { border: 2px solid var(--hover-color); }


        /* Botón Cerrar Sesión (Alto Contraste) */
        #cerrar-sesion {
            background-color: var(--color-rojo-tomate);
            color: white;
            border: none;
            padding: 18px 40px;
            border-radius: 10px;
            font-size: 1.2em;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.1s;
            margin-top: 30px;
            box-shadow: 0 4px 15px rgba(217, 48, 67, 0.5);
            letter-spacing: 1px;
        }
        #cerrar-sesion:hover {
            background-color: #A62432; 
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    
    <div class="panel-container">
        <p class="bienvenida">
            Bienvenid@, <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong> (<?php echo htmlspecialchars($rol_usuario); ?>)
        </p>

        <div class="header-title">
            <h1>Panel de Control</h1>
            <hr>
        </div>

        <div class="grid-botones">
            <a href="pos.php" class="boton-panel boton-pos">
                <i class="fas fa-cash-register"></i>
                <span>IR A VENTA (POS)</span>
            </a>
            
            <a href="corte_caja.php" class="boton-panel boton-corte">
                <i class="fas fa-money-check-alt"></i>
                <span>Ver mi Monto Diario (CORTE)</span>
            </a>
            
            <a href="productosl.php" class="boton-panel boton-productos">
                <i class="fas fa-pizza-slice"></i>
                <span>Gestión de Productos</span>
            </a>

            <a href="clientslist.php" class="boton-panel boton-clientes">
                <i class="fas fa-users"></i>
                <span>Gestión de Clientes</span>
            </a>
            
            <a href="trabajadoreslist.php" class="boton-panel boton-trabajadores">
                <i class="fas fa-user-tie"></i>
                <span>Gestión de Trabajadores</span>
            </a>
            
            <a href="reportes.php" class="boton-panel boton-reportes">
                <i class="fas fa-chart-line"></i>
                <span>Ver Reportes</span>
            </a>
        </div>

        <form action="logout.php" method="post">
            <button type="submit" id="cerrar-sesion">
                <i class="fas fa-sign-out-alt"></i> CERRAR SESIÓN
            </button>
        </form>
    </div>

</body>
</html>