<?php
include 'conexion.php'; 
session_start();

if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header("Location: login.php");
    exit();
}

$id_trabajador_actual = $_SESSION['id_usuario'];

// --- LÓGICA PARA MOSTRAR MENSAJES DE PROCESAR_VENTA.PHP ---
$mensaje_pos = ''; 
if (isset($_SESSION['mensaje_pos'])) {
    // Almacenar el mensaje para mostrarlo en el HTML
    $mensaje_pos = $_SESSION['mensaje_pos'];
    
    // Eliminar el mensaje de la sesión para que no se muestre al recargar
    unset($_SESSION['mensaje_pos']); 
}
// -------------------------------------------------------------

// Obtener productos
$sql_productos = "SELECT id, nombre, precio FROM productos ORDER BY nombre ASC";
$resultado_productos = mysqli_query($conexion, $sql_productos);
$productos = [];
if ($resultado_productos) {
    $productos = mysqli_fetch_all($resultado_productos, MYSQLI_ASSOC);
} 

// Obtener clientes
$sql_clientes = "SELECT id, nombre FROM clientes ORDER BY nombre ASC";
$resultado_clientes = mysqli_query($conexion, $sql_clientes);
$clientes = [];
if ($resultado_clientes) {
    $clientes = mysqli_fetch_all($resultado_clientes, MYSQLI_ASSOC);
} 

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>🍕 Sistema POS - Nueva Venta</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* ========================================================= */
        /* --- 1. PALETA Y FUENTES (Coherencia de Marca) --- */
        /* ========================================================= */
        :root {
            --color-rojo-tomate: #D93043;     
            --color-verde-albahaca: #2A9D8F; 
            --color-amarillo-queso: #F4D35E; 
            --color-beige-masa: #F1E0C5;     
            --color-marron-horno: #8D6E63;   
            --color-negro-carbon: #2D2D2D;   
            --color-gris-claro-suave: #ECECEC; 
        }
        
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Bebas+Neue&display=swap');

        /* ========================================================= */
        /* --- 2. BASE DEL POS --- */
        /* ========================================================= */
        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            background-color: var(--color-negro-carbon); 
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px; 
        }
        .pos-wrapper {
            display: flex;
            width: 95vw; 
            max-width: 1600px; 
            height: 90vh; 
            background: white; 
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.7);
            overflow: hidden; 
        }

        /* --- Estilos para Mensajes de Sesión --- */
        .mensaje-pos-alerta {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 700;
            color: white;
            text-align: center;
            cursor: pointer;
            transition: opacity 0.3s;
        }
        .success-msg { background-color: var(--color-verde-albahaca); }
        .error-msg { background-color: var(--color-rojo-tomate); }
        /* -------------------------------------- */


        /* ========================================================= */
        /* --- 3. COLUMNA IZQUIERDA: MENÚ DE PRODUCTOS (Claro) --- */
        /* ========================================================= */
        .menu-productos-panel {
            flex: 2; 
            padding: 25px;
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--color-gris-claro-suave);
            background-color: var(--color-beige-masa); 
            color: var(--color-negro-carbon);
        }
        .menu-productos-panel h2 {
            font-family: 'Bebas Neue', sans-serif;
            color: var(--color-rojo-tomate); 
            margin-top: 0;
            margin-bottom: 25px;
            font-size: 3em; 
            font-weight: 800;
            letter-spacing: 2px;
        }
        .grid-productos {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); 
            gap: 15px;
            overflow-y: auto; 
            flex-grow: 1;
            padding-right: 10px; 
        }
        .producto-item-btn {
            background-color: white;
            border: 2px solid var(--color-amarillo-queso); 
            border-radius: 10px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 3px 8px rgba(0,0,0,0.2);
            text-align: center;
            height: 120px; 
        }
        .producto-item-btn:hover {
            background-color: var(--color-amarillo-queso); 
            border-color: var(--color-rojo-tomate);
            transform: scale(1.03);
            box-shadow: 0 6px 15px rgba(0,0,0,0.3);
        }
        .producto-item-btn .nombre {
            font-weight: 800;
            font-size: 1.1em;
            color: var(--color-negro-carbon);
            margin-bottom: 5px;
            line-height: 1.2;
        }
        .producto-item-btn .precio {
            font-size: 1.2em;
            color: var(--color-rojo-tomate); 
            font-weight: 900;
        }
        .pos-volver {
            margin-top: 20px;
            text-align: center;
            font-size: 1em;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px; /* Espacio entre los enlaces */
        }
        .pos-volver a {
            color: var(--color-marron-horno);
            text-decoration: none;
            font-weight: 700;
            padding: 10px 18px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: background-color 0.2s;
        }
        .pos-volver a:hover {
            background-color: var(--color-gris-claro-suave);
        }
        /* Estilos específicos para el botón de Corte de Caja */
        .pos-volver .btn-corte-caja {
            background-color: var(--color-rojo-tomate); /* Rojo tomate para una acción importante */
            color: white;
            box-shadow: 0 4px 10px rgba(217, 48, 67, 0.4);
        }
        .pos-volver .btn-corte-caja:hover {
            background-color: #a82534; /* Rojo más oscuro al pasar el ratón */
        }

        /* ========================================================= */
        /* --- 4. COLUMNA DERECHA: RESUMEN DE PEDIDO (Oscuro) --- */
        /* ========================================================= */
        .resumen-venta-panel {
            flex: 1; 
            background-color: var(--color-marron-horno); 
            color: var(--color-beige-masa); 
            padding: 25px;
            display: flex;
            flex-direction: column;
        }
        .resumen-venta-panel h2 {
            font-family: 'Bebas Neue', sans-serif;
            color: var(--color-amarillo-queso); 
            margin-top: 0;
            margin-bottom: 25px;
            font-size: 3em; 
            font-weight: 800;
            letter-spacing: 2px;
        }
        .pedido-header {
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(255,255,255,0.4);
            color: var(--color-amarillo-queso);
            font-size: 1.1em;
            text-transform: uppercase;
        }
        #carrito-items {
            flex-grow: 1; 
            overflow-y: auto; 
            margin-bottom: 20px;
            padding-right: 5px; 
        }
        .carrito-linea {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 1em;
            align-items: center;
            border-bottom: 1px dotted rgba(255,255,255,0.1);
            padding-bottom: 5px;
        }
        .carrito-linea .cantidad {
            background-color: var(--color-rojo-tomate); 
            color: white;
            padding: 5px 9px;
            border-radius: 50%; 
            margin-right: 10px;
            font-size: 0.9em;
            font-weight: 700;
            min-width: 25px;
            text-align: center;
        }
        .carrito-linea .nombre {
            flex-grow: 1;
            padding-right: 10px;
        }
        .carrito-linea .acciones button {
            background: none;
            border: none;
            color: var(--color-beige-masa);
            cursor: pointer;
            font-size: 1.1em;
            margin-left: 5px;
            padding: 3px;
            transition: color 0.2s;
        }
        .carrito-linea .acciones button:hover {
            color: var(--color-rojo-tomate); 
        }
        .carrito-linea .subtotal { font-weight: 800; min-width: 60px; text-align: right;}

        /* Totales */
        .totales-resumen {
            padding-top: 15px;
            border-top: 2px solid rgba(255,255,255,0.6);
            margin-top: 15px;
        }
        .total-linea {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 1.2em;
            font-weight: 600;
        }
        .total-linea.gran-total {
            font-size: 2.2em;
            font-weight: 900;
            color: var(--color-verde-albahaca); 
            margin-top: 15px;
            padding-top: 10px;
            border-top: 3px double var(--color-verde-albahaca); 
        }

        /* Controles Inferiores (Cliente y Botón Confirmar) */
        .controles-venta {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.3);
        }
        .controles-venta label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: var(--color-beige-masa);
        }
        .controles-venta select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: none;
            background-color: rgba(255,255,255,0.2); 
            color: white;
            font-size: 1.1em;
            margin-bottom: 15px;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.3);
            appearance: none; 
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23F1E0C5%22%20d%3D%22M287%2069.9L146.2%20207.2L5.4%2069.9h281.6z%22%2F%3E%3C%2Fsvg%3E');
            background-repeat: no-repeat;
            background-position: right 10px top 50%;
            background-size: 12px;
        }
        .controles-venta select option {
            background-color: var(--color-marron-horno); 
            color: white;
            padding: 10px;
        }
        #boton-confirmar {
            width: 100%;
            padding: 20px;
            background-color: var(--color-verde-albahaca); 
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1.5em;
            font-weight: 800;
            transition: background-color 0.2s ease, transform 0.1s;
            box-shadow: 0 6px 15px rgba(42, 157, 143, 0.6);
        }
        #boton-confirmar:hover {
            background-color: #228b7e; 
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(42, 157, 143, 0.8);
        }
    </style>
</head>
<body>

    <div class="pos-wrapper">
        <div class="menu-productos-panel">
            <h2>🍕 Menú de Productos</h2>
            
            <?php if (!empty($mensaje_pos)): ?>
                <div class="mensaje-pos-alerta <?php echo strpos($mensaje_pos, 'success-msg') !== false ? 'success-msg' : 'error-msg'; ?>" onclick="this.style.opacity='0'; setTimeout(() => this.style.display='none', 300);">
                    <?php echo strip_tags($mensaje_pos, '<b><i><em>'); ?>
                </div>
            <?php endif; ?>
            <div class="grid-productos">
                <?php foreach ($productos as $producto): ?>
                    <div class="producto-item-btn" 
                        data-id="<?php echo $producto['id']; ?>"
                        data-nombre="<?php echo htmlspecialchars($producto['nombre']); ?>"
                        data-precio="<?php echo $producto['precio']; ?>"
                        onclick="agregarACarrito(this)">
                        <span class="nombre"><?php echo htmlspecialchars($producto['nombre']); ?></span>
                        <span class="precio">$<?php echo number_format($producto['precio'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($productos)): ?>
                    <p>No se encontraron productos. Agrega algunos en Gestión de Productos.</p>
                <?php endif; ?>
            </div>
            
            <div class="pos-volver">
                <a href="index.php"><i class="fas fa-arrow-left"></i> Volver al Menú Principal</a>
                
                <a href="corte_caja.php" class="btn-corte-caja" title="Realizar el cierre y reporte diario">
                    <i class="fas fa-cash-register"></i> Corte de Caja
                </a>
            </div>
            
        </div>

        <div class="resumen-venta-panel">
            <h2>🛒 Pedido Actual</h2>
            <div class="pedido-header">
                <span>Artículo</span>
                <span>Subtotal</span>
            </div>
            
            <div id="carrito-items">
                <p style="text-align: center; opacity: 0.8; margin-top: 30px;">Haga clic en un producto para añadirlo.</p>
            </div>

            <div class="totales-resumen">
                <div class="total-linea">
                    <span>Subtotal:</span>
                    <span id="subtotal-display">$0.00</span>
                </div>
                <div class="total-linea">
                    <span>Impuestos (0%):</span> <span id="impuestos-display">$0.00</span>
                </div>
                <div class="total-linea gran-total">
                    <span>TOTAL:</span>
                    <span id="total-final">$0.00</span>
                </div>
            </div>

            <form id="form-venta" action="procesar_venta.php" method="POST">
                <input type="hidden" name="pedido_json" id="pedido-json">
                <input type="hidden" name="id_trabajador" value="<?php echo $id_trabajador_actual; ?>">
                
                <input type="hidden" name="subtotal_final" id="subtotal-input">
                <input type="hidden" name="iva_final" id="iva-input">
                <input type="hidden" name="total_final" id="total-input">

                <div class="controles-venta">
                    <label for="cliente_id">Cliente:</label>
                    <select name="cliente_id" id="cliente_id">
                        <option value="1">1 - Público General</option> 
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?php echo $cliente['id']; ?>">
                                <?php echo $cliente['id'] . ' - ' . htmlspecialchars($cliente['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="button" id="boton-confirmar" onclick="confirmarVenta()">PAGAR <i class="fas fa-money-bill-wave"></i></button>
            </form>
        </div>
    </div>

    <script>
        const carrito = []; 
        const subtotalDisplay = document.getElementById('subtotal-display');
        const impuestosDisplay = document.getElementById('impuestos-display');
        const totalFinalDisplay = document.getElementById('total-final');
        const itemsContainer = document.getElementById('carrito-items');
        const pedidoJsonInput = document.getElementById('pedido-json');

        // Referencias a los nuevos campos ocultos
        const subtotalInput = document.getElementById('subtotal-input');
        const ivaInput = document.getElementById('iva-input');
        const totalInput = document.getElementById('total-input');
        
        // Tasa de impuesto (debe coincidir con la de PHP)
        const TASA_IMPUESTO = 0.00; 
        
        function agregarACarrito(element) {
            const id = element.getAttribute('data-id');
            const nombre = element.getAttribute('data-nombre');
            const precio = parseFloat(element.getAttribute('data-precio'));

            const itemExistente = carrito.find(item => item.id === id);

            if (itemExistente) {
                itemExistente.cantidad++;
            } else {
                carrito.push({ id, nombre, precio, cantidad: 1 });
            }
            actualizarInterfaz();
        }

        function cambiarCantidad(id, delta) {
            const item = carrito.find(item => item.id === id);
            if (item) {
                item.cantidad += delta;
                if (item.cantidad <= 0) {
                    eliminarDelCarrito(id);
                }
            }
            actualizarInterfaz();
        }

        function eliminarDelCarrito(id) {
            const index = carrito.findIndex(item => item.id === id);
            if (index > -1) {
                carrito.splice(index, 1);
            }
            actualizarInterfaz();
        }

        function actualizarInterfaz() {
            itemsContainer.innerHTML = '';
            let subtotal = 0;
            
            if (carrito.length === 0) {
                itemsContainer.innerHTML = '<p style="text-align: center; opacity: 0.8; margin-top: 30px;">Haga clic en un producto para añadirlo.</p>';
            }

            carrito.forEach(item => {
                const itemSubtotal = item.precio * item.cantidad;
                subtotal += itemSubtotal;
                
                const linea = document.createElement('div');
                linea.className = 'carrito-linea';
                linea.innerHTML = `
                    <span class="cantidad">${item.cantidad}</span>
                    <span class="nombre">${item.nombre}</span>
                    <span class="acciones">
                        <button type="button" onclick="cambiarCantidad('${item.id}', 1)"><i class="fas fa-plus-circle"></i></button>
                        <button type="button" onclick="cambiarCantidad('${item.id}', -1)"><i class="fas fa-minus-circle"></i></button>
                        <button type="button" onclick="eliminarDelCarrito('${item.id}')"><i class="fas fa-trash-alt"></i></button>
                    </span>
                    <span class="subtotal">$${itemSubtotal.toFixed(2)}</span>
                `;
                itemsContainer.appendChild(linea);
            });

            const impuestos = subtotal * TASA_IMPUESTO;
            const total = subtotal + impuestos;

            // Mostrar en la interfaz
            subtotalDisplay.textContent = `$${subtotal.toFixed(2)}`;
            impuestosDisplay.textContent = `$${impuestos.toFixed(2)}`;
            totalFinalDisplay.textContent = `$${total.toFixed(2)}`;
            
            // Asignar a los campos ocultos para el POST
            subtotalInput.value = subtotal.toFixed(2);
            ivaInput.value = impuestos.toFixed(2);
            totalInput.value = total.toFixed(2);
        }

        function confirmarVenta() {
            if (carrito.length === 0) {
                alert('El carrito está vacío. Agrega productos para confirmar la venta.');
                return;
            }
            
            // 1. Convertir el carrito a JSON
            const pedidoJSON = JSON.stringify(carrito);
            pedidoJsonInput.value = pedidoJSON;
            
            // 2. Enviar el formulario
            document.getElementById('form-venta').submit();
        }

        actualizarInterfaz();
    </script>
</body>
</html>