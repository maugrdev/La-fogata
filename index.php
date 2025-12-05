<?php
// Incluye el archivo de conexión.
include 'conexion.php'; 

// Obtener la lista de PRODUCTOS de la base de datos
// Se ELIMINÓ 'GROUP BY nombre' porque causa errores en PostgreSQL y es innecesario
$sql_productos = "SELECT id, nombre, precio, descripcion, imagen_ruta FROM productos ORDER BY nombre ASC";
$resultado_productos = pg_query($conexion, $sql_productos);
$productos = [];

if ($resultado_productos) {
    // CORRECCIÓN: Se usa pg_fetch_assoc() en lugar de mysqli_fetch_assoc()
    while ($p = pg_fetch_assoc($resultado_productos)) { 
        // Aseguramos que el precio sea un número
        $p['precio_numerico'] = floatval($p['precio']); 
        $productos[] = $p;
    }
    // CORRECCIÓN: Se usa pg_free_result() en lugar de mysqli_free_result()
    pg_free_result($resultado_productos); 
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>🍕 Menú de LA FOGATA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        /* ========================================================= */
        /* --- 1. PALETA Y FUENTES --- */
        /* ========================================================= */
        :root {
            --color-rojo-tomate: #D93043;     /* El sabor intenso del tomate */
            --color-verde-albahaca: #2A9D8F; /* Frescura y naturalidad */
            --color-amarillo-queso: #F4D35E; /* Calidez y textura */
            --color-beige-masa: #F1E0C5;      /* Base cálida y neutra */
            --color-marron-horno: #8D6E63;    /* Profundidad y autenticidad */
            --color-negro-carbon: #2D2D2D;    /* Contraste fuerte */
            --color-gris-claro-suave: #ECECEC; 
            --color-marron: #6E4B3A;
            --color-blanco: #FFFFFF;
            --color-gris: #F0F0F0;
        }
        
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&display=swap');

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ========================================================= */
        /* --- 2. BASE DEL SITIO Y LAYOUT --- */
        /* ========================================================= */
        body { 
            font-family: 'Montserrat', sans-serif; 
            margin: 0; padding: 0; 
            background-color: var(--color-negro-carbon);
            color: var(--color-beige-masa);
            min-height: 100vh;
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        .content-container {
            width: 90%; max-width: 1200px; margin: 0 auto; padding-top: 40px; padding-bottom: 80px;
        }
        
        /* Botón Flotante (Acceso Personal) */
        .btn-login-flotante {
            background-color: var(--color-marron-horno); 
            color: white;
            padding: 10px 18px;
            border-radius: 5px;
            box-shadow: 0 4px 10px rgba(141, 110, 99, 0.5);
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            transition: all 0.3s;
            text-decoration: none;
            font-weight: 600;
        }
        .btn-login-flotante:hover {
             background-color: var(--color-rojo-tomate);
             transform: scale(1.08);
        }

        /* ========================================================= */
        /* --- 3. NAVBAR, BANNER, FOOTER (Estilos originales) --- */
        /* ========================================================= */
        .navbar {
            background-color: var(--color-negro-carbon);
            padding: 15px 5%; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.4);
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 3px solid var(--color-rojo-tomate);
        }
        .nav-right-group { display: flex; align-items: center; gap: 30px; }
        .navbar .brand { display: flex; align-items: center; color: var(--color-amarillo-queso); font-weight: 800; font-size: 1.8em; }
        .navbar .logo-img { height: 40px; width: auto; margin-right: 15px; object-fit: contain; }
        .navbar .nav-links a {
            color: var(--color-beige-masa); text-decoration: none; margin-left: 25px; 
            font-weight: 600; padding: 5px 0; transition: color 0.3s, border-bottom 0.3s;
        }
        .navbar .nav-links a:hover { color: var(--color-amarillo-queso); border-bottom: 2px solid var(--color-amarillo-queso); }
        .main-banner { 
            /* Esto asume que tienes una imagen 'images/sl.png' */
            background-image: url('images/sl.png'); 
            background-size: cover; /* Añadido para mejor visualización */
            background-position: center; /* Añadido para mejor visualización */
            padding: 80px 5%; /* Aumentado el padding para darle más cuerpo */
            text-align: center; 
            margin-bottom: 40px; 
        }
        .main-banner h2 { 
            font-size: 3.5em; 
            font-weight: 900; 
            color: var(--color-blanco); 
            margin: 0; 
            text-shadow: 0 4px 8px rgba(0,0,0,0.7); /* Sombra para resaltar el texto */
        }
        .main-banner p {
            font-size: 1.5em;
            font-weight: 600;
            color: var(--color-amarillo-queso);
            margin-top: 10px;
            text-shadow: 0 2px 5px rgba(0,0,0,0.7);
        }

        /* Estilos de la sección LO NUEVO */
        .lo-nuevo { 
            background-color: var(--color-gris-claro-suave); 
            padding: 30px 5%; 
            margin-bottom: 40px; 
            text-align: center; 
            color: var(--color-negro-carbon); 
            border-radius: 8px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .lo-nuevo h3 { 
            font-size: 2em; 
            color: var(--color-rojo-tomate); 
            margin-bottom: 20px; 
            border-bottom: 2px solid var(--color-amarillo-queso); 
            padding-bottom: 10px; 
            display: inline-block; 
        }
        .lo-nuevo .product-carousel { 
            display: flex; 
            justify-content: center; 
            gap: 20px; 
            flex-wrap: wrap; 
        }
        .lo-nuevo .carousel-item {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 280px;
            padding-bottom: 15px;
            transition: transform 0.3s;
        }
        .lo-nuevo .carousel-item:hover {
            transform: translateY(-5px);
        }
        .lo-nuevo .carousel-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            margin-bottom: 10px;
        }
        .lo-nuevo .carousel-item h4 {
            color: var(--color-negro-carbon);
            margin: 0 10px 5px;
        }
        .lo-nuevo .carousel-item p {
            color: var(--color-marron-horno);
            font-size: 0.9em;
            margin: 0 10px;
        }


        .menu-title { font-size: 2.8em; text-align: center; color: var(--color-beige-masa); margin-bottom: 40px; font-weight: 900; position: relative; text-transform: uppercase;}
        .menu-title::after { content: ''; position: absolute; bottom: -10px; left: 50%; transform: translateX(-50%); width: 100px; height: 4px; background: var(--color-rojo-tomate); border-radius: 2px; }
        .menu-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; justify-content: center; }
        .card { background-color: white; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); overflow: hidden; width: 100%; transition: transform 0.3s, box-shadow 0.3s; text-decoration: none; color: var(--color-negro-carbon); display: flex; flex-direction: column; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.2); }
        .card-image { width: 100%; height: 180px; object-fit: cover; border-bottom: 1px solid var(--color-gris-claro-suave); }
        .card-no-image { width: 100%; height: 180px; background: linear-gradient(135deg, var(--color-beige-masa) 0%, #F8EFE4 100%); display: flex; align-items: center; justify-content: center; font-size: 5em; color: var(--color-marron-horno); border-bottom: 1px solid var(--color-marron-horno); }
        .card-content { padding: 15px; text-align: left; flex-grow: 1; }
        .card-title { color: var(--color-negro-carbon); font-size: 1.4em; font-weight: 800; margin: 0 0 5px 0; line-height: 1.2; }
        .card-description { color: var(--color-marron-horno); font-size: 0.9em; margin: 0 0 10px 0; min-height: 35px; }
        .card-footer { display: flex; justify-content: space-between; align-items: center; padding: 15px; border-top: 1px solid var(--color-gris-claro-suave); }
        .card-price { color: var(--color-rojo-tomate); font-size: 1.8em; font-weight: 900; margin: 0; }
        .card-button {
            background-color: var(--color-verde-albahaca); color: white; border: none; padding: 8px 18px; 
            border-radius: 5px; font-weight: 700; transition: all 0.3s; text-transform: uppercase;
            box-shadow: 0 2px 5px rgba(42, 157, 143, 0.4); cursor: pointer;
        }
        .card-button:hover { background-color: #228b7e; transform: scale(1.03); box-shadow: 0 4px 10px rgba(42, 157, 143, 0.6); }
        .footer { background-color: var(--color-negro-carbon); color: var(--color-marron-horno); padding: 40px 0; text-align: center; border-top: 3px solid var(--color-rojo-tomate); margin-top: 60px; }
        .footer a { color: var(--color-amarillo-queso); margin: 0 5px; transition: color 0.3s; }
        .footer a:hover { color: white; }

        /* ========================================================= */
        /* --- 4. ESTILOS DEL CARRITO (Funcionalidad Flotante) --- */
        /* ========================================================= */
        #shopping-cart {
            position: fixed; top: 0; right: 0; width: 350px; height: 100%;
            background-color: white; color: var(--color-negro-carbon);
            box-shadow: -5px 0 15px rgba(0,0,0,0.3); z-index: 2000;
            transform: translateX(100%); transition: transform 0.4s ease-in-out;
            padding: 20px; display: flex; flex-direction: column;
        }
        #shopping-cart.open { transform: translateX(0); }
        #cart-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--color-rojo-tomate); padding-bottom: 10px; margin-bottom: 15px; }
        #cart-items-container { flex-grow: 1; overflow-y: auto; margin-bottom: 15px; padding-right: 10px; }
        .cart-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px dashed var(--color-gris-claro-suave); }
        .cart-item-info { flex-grow: 1; }
        .cart-item-name { font-weight: 700; font-size: 1.1em; color: var(--color-negro-carbon); }
        .cart-item-price { color: var(--color-verde-albahaca); font-weight: 600; margin-top: 3px; }
        .cart-item-actions button { background: none; border: 1px solid var(--color-rojo-tomate); color: var(--color-rojo-tomate); padding: 3px 6px; cursor: pointer; margin: 0 2px; border-radius: 3px; transition: background-color 0.2s; }
        .cart-item-actions button:hover { background-color: var(--color-rojo-tomate); color: white; }
        #cart-footer { border-top: 2px solid var(--color-rojo-tomate); padding-top: 15px; }
        #cart-total { font-size: 1.5em; font-weight: 800; display: flex; justify-content: space-between; margin-bottom: 15px; }
        #cart-buttons { display: flex; flex-direction: column; gap: 10px; }
        .cart-action-btn { padding: 12px; border: none; border-radius: 5px; font-weight: 700; cursor: pointer; transition: opacity 0.3s; }
        .cart-action-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        #btn-whatsapp { background-color: var(--color-verde-albahaca); color: white; }
        #btn-whatsapp:hover:not(:disabled) { background-color: #228b7e; }
        #btn-modificar { background-color: var(--color-amarillo-queso); color: var(--color-negro-carbon); }
        #btn-modificar:hover:not(:disabled) { background-color: #E2C24E; }
        #cart-float-button {
            position: fixed; bottom: 90px; right: 20px; background-color: var(--color-verde-albahaca);
            color: white; padding: 15px 20px; border-radius: 50%; cursor: pointer;
            box-shadow: 0 4px 10px rgba(42, 157, 143, 0.5); z-index: 1500;
            text-align: center; transition: all 0.3s;
        }
        #cart-float-button:hover { transform: scale(1.1); background-color: #228b7e; }
        #cart-count {
            position: absolute; top: -5px; right: -5px; background-color: var(--color-rojo-tomate);
            color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.8em; font-weight: 700;
        }
    </style>
</head>
<body>

    <a href="login.php" class="btn-login-flotante">
        <i class="fas fa-user-lock"></i> Acceso Personal
    </a>

    <div id="cart-float-button" onclick="toggleCart()">
        <i class="fas fa-shopping-cart fa-lg"></i>
        <span id="cart-count">0</span>
    </div>

    <div id="shopping-cart">
        <div id="cart-header">
            <h3>🛒 Tu Pedido</h3>
            <button onclick="toggleCart()" style="background: none; border: none; font-size: 1.5em; cursor: pointer; color: var(--color-rojo-tomate);">&times;</button>
        </div>
        
        <div id="cart-items-container">
            <p id="empty-cart-message">El carrito está vacío.</p>
        </div>
        
        <div id="cart-footer">
            <div id="cart-total">
                <span>Total:</span>
                <span id="cart-total-value">$0.00</span>
            </div>
            
            <div id="cart-buttons">
                <button id="btn-modificar" class="cart-action-btn" onclick="toggleCart()" disabled>
                    <i class="fas fa-edit"></i> Modificar Orden
                </button>
                
                <button id="btn-whatsapp" class="cart-action-btn" onclick="sendToWhatsApp()" disabled>
                    <i class="fab fa-whatsapp"></i> Enviar a WhatsApp
                </button>
            </div>
        </div>
    </div>

    <div class="navbar">
        <div class="brand">
            <img src="images/pizza.jpg" alt="La Fogata Logo" class="logo-img">
            <span class="logo-text">LA FOGATA</span>
        </div>
        
        <div class="nav-right-group">
            <div class="nav-links">
                <a href="#menu"><i class="fas fa-utensils"></i> Carta Completa</a>
                
                <a href="https://www.google.com/maps/search/Plaza+Centella+Cuautitlán" target="_blank"><i class="fas fa-map-marked-alt"></i> Ubicación</a>
                
                <a href="https://wa.me/525611662370?text=Pizzeria%20la%20fogata%3A%20ordena%20aqui" target="_blank"><i class="fas fa-phone-alt"></i> Contacto</a>
            </div>
        </div>
    </div>
    
    
    <div class="main-banner">
        <h2>¿Hambre?</h2>
        <p>La solución llega en 30 minutos</p>
    </div>

    <div class="lo-nuevo">
        <h3><i class="fas fa-fire-alt"></i> LO NUEVO EN LA FOGATA</h3>
        <div class="product-carousel">
            <div class="carousel-item">
                <img src="images/pcamp.png" alt="Nueva Pizza Especial">
                <h4>Pizza Campestre</h4>
                <p>La combinación perfecta de tomate natural, albahaca fresca y el mejor queso.</p>
            </div>
            <div class="carousel-item">
                <img src="images/drink.png" alt="Nueva Bebida Refrescante">
                <h4>Bebidas para aconpañar</h4>
                <p>Combina perfecto con tu pizza favorita.</p>
            </div>
            <div class="carousel-item">
                <img src="images/dd.png" alt="Nuevo Postre">
                <h4>Cheese Fingers, please!</h4>
                <p>Dedos con mucho cheese-tilo.</p>
            </div>
        </div>
    </div>
    <div class="content-container">
        
        <h2 class="menu-title" id="menu">Nuestra Carta Completa</h2>

        <div class="menu-grid">
            <?php if (!empty($productos)): ?>
                <?php foreach ($productos as $p): 
                    // Creamos un string JSON con los datos del producto
                    $product_data = json_encode([
                        'id' => $p['id'],
                        'nombre' => $p['nombre'],
                        'precio' => $p['precio_numerico']
                    ]);
                ?> 
                    <div class="card">
                        
                        <?php 
                        $ruta_img = $p['imagen_ruta'] ?? '';
                        // Usamos file_exists() para asegurar que el archivo realmente existe en la ruta guardada.
                        if (!empty($ruta_img) && file_exists($ruta_img)): 
                        ?>
                            <img src="<?php echo htmlspecialchars($ruta_img); ?>" alt="<?php echo htmlspecialchars($p['nombre']); ?>" class="card-image">
                        <?php else: ?>
                            <div class="card-no-image">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-content">
                            <div class="card-title"><?php echo htmlspecialchars($p['nombre']); ?></div>
                            <div class="card-description">
                                 <?php
    $desc = $p['descripcion'] ?? '';
    echo htmlspecialchars($desc ?: 'Descripción no disponible.');
    ?>
                            </div>
                        </div>
                        <div class="card-footer">
                            <span class="card-price">$<?php echo number_format($p['precio_numerico'], 2); ?></span>
                            <button class="card-button" onclick='addToCart(<?php echo $product_data; ?>)'>
                                <i class="fas fa-plus"></i> Añadir
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; color: var(--color-rojo-tomate); font-size: 1.2em; padding: 30px; background-color: white; border-radius: 8px; margin-top: 20px;">
                    <i class="fas fa-exclamation-triangle"></i> No hay productos registrados.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2025 LA FOGATA | Todos los derechos reservados.</p>
        <p>Aviso de privacidad | Términos y condiciones</p>
        <div>
            <a href="#" style="color: white; margin: 0 5px;"><i class="fab fa-facebook-square fa-lg"></i></a>
            <a href="#" style="color: white; margin: 0 5px;"><i class="fab fa-instagram fa-lg"></i></a>
            <a href="https://wa.me/525611662370?text=Pizzeria%20la%20fogata%3A%20ordena%20aqui" target="_blank" style="color: var(--color-verde-albahaca); margin: 0 5px;" title="Chatea con nosotros"><i class="fab fa-whatsapp fa-lg"></i></a>
        </div>
    </div>

    <script>
        // =========================================================
        // --- LÓGICA DEL CARRITO (JavaScript) ---
        // =========================================================
        
        let cart = []; 
        const WHATSAPP_NUMBER = "525611662370";
        const INITIAL_MESSAGE = "Pizzeria la fogata: ordena aqui";

        // Función para añadir un producto al carrito
        function addToCart(product) {
            const existingItem = cart.find(item => item.id === product.id);

            if (existingItem) {
                existingItem.quantity++;
            } else {
                cart.push({
                    id: product.id,
                    nombre: product.nombre,
                    precio: product.precio,
                    quantity: 1
                });
            }
            
            updateCartUI();
            
            // Abrir el carrito automáticamente al añadir algo
            if (!document.getElementById('shopping-cart').classList.contains('open')) {
                toggleCart();
            }
        }

        // Función para actualizar la cantidad de un producto
        function updateQuantity(id, change) {
            const item = cart.find(i => i.id === id);
            if (item) {
                item.quantity += change;
                if (item.quantity <= 0) {
                    cart = cart.filter(i => i.id !== id);
                }
                updateCartUI();
            }
        }
        
        // Función principal para renderizar el carrito y actualizar totales
        function updateCartUI() {
            const container = document.getElementById('cart-items-container');
            const totalValue = document.getElementById('cart-total-value');
            const cartCount = document.getElementById('cart-count');
            const btnWhatsapp = document.getElementById('btn-whatsapp');
            const btnModificar = document.getElementById('btn-modificar');

            let currentEmptyMessage = document.getElementById('empty-cart-message');
            if (!currentEmptyMessage) {
                 currentEmptyMessage = document.createElement('p');
                 currentEmptyMessage.id = 'empty-cart-message';
                 currentEmptyMessage.textContent = 'El carrito está vacío.';
                 container.appendChild(currentEmptyMessage);
            }

            container.innerHTML = ''; 
            let total = 0;

            if (cart.length === 0) {
                currentEmptyMessage.style.display = 'block';
                container.appendChild(currentEmptyMessage);
                btnWhatsapp.disabled = true;
                btnModificar.disabled = true;
                cartCount.textContent = '0';
                totalValue.textContent = '$0.00';
            } else {
                currentEmptyMessage.style.display = 'none';
                btnWhatsapp.disabled = false;
                btnModificar.disabled = false;

                cart.forEach(item => {
                    const itemTotal = item.precio * item.quantity;
                    total += itemTotal;

                    const itemElement = document.createElement('div');
                    itemElement.classList.add('cart-item');
                    itemElement.innerHTML = `
                        <div class="cart-item-info">
                            <span class="cart-item-name">${item.quantity}x ${item.nombre}</span>
                            <div class="cart-item-price">$${itemTotal.toFixed(2)}</div>
                        </div>
                        <div class="cart-item-actions">
                            <button onclick="updateQuantity(${item.id}, 1)"><i class="fas fa-plus"></i></button>
                            <button onclick="updateQuantity(${item.id}, -1)"><i class="fas fa-minus"></i></button>
                        </div>
                    `;
                    container.appendChild(itemElement);
                });

                // Reañadir el mensaje de vacío, oculto, para que la función lo encuentre la próxima vez
                currentEmptyMessage.style.display = 'none';
                container.appendChild(currentEmptyMessage); 
            }

            // Actualizar total y contador
            totalValue.textContent = `$${total.toFixed(2)}`;
            cartCount.textContent = cart.reduce((sum, item) => sum + item.quantity, 0);
        }

        // Función para alternar la visibilidad del carrito
        function toggleCart() {
            document.getElementById('shopping-cart').classList.toggle('open');
        }

        // Función para enviar el pedido a WhatsApp
        function sendToWhatsApp() {
            if (cart.length === 0) {
                alert("El carrito está vacío. Agrega productos antes de enviar la orden.");
                return;
            }

            // 1. Crear el cuerpo del mensaje
            let message = `*${INITIAL_MESSAGE}*\n\n`; 
            let total = 0;

            cart.forEach(item => {
                const itemTotal = item.precio * item.quantity;
                total += itemTotal;
                
                message += `▪️ ${item.quantity}x ${item.nombre} ($${itemTotal.toFixed(2)})\n`;
            });
            
            // 2. Agregar el total al mensaje
            message += `\n*TOTAL estimado: $${total.toFixed(2)}*\n\n`;
            message += `_Por favor, confirma mi orden._`;

            // 3. Codificar el mensaje para la URL
            const encodedMessage = encodeURIComponent(message);
            
            // 4. Crear el enlace de WhatsApp
            const whatsappURL = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodedMessage}`;
            
            // 5. Abrir la nueva ventana
            window.open(whatsappURL, '_blank');
        }

        // Inicializar la interfaz del carrito al cargar la página
        document.addEventListener('DOMContentLoaded', updateCartUI);

    </script>
</body>
</html>

<?php
pg_close($conexion);

?>


