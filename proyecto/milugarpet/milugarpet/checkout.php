<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (!isClient()) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$message = '';
$error = '';

$db->query('SELECT c.*, p.nombre, p.precio, p.imagen_url, p.stock
            FROM carrito c
            JOIN productos p ON c.id_producto = p.id_producto
            WHERE c.id_usuario = :user_id
            ORDER BY c.fecha_agregado DESC');
$db->bind(':user_id', getCurrentUserId());
$carrito_items = $db->resultset();

if (empty($carrito_items)) {
    header('Location: carrito.php');
    exit;
}

$subtotal = 0;
foreach ($carrito_items as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}
$envio = 0;
$total = $subtotal + $envio;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['procesar_pedido'])) {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $ciudad = trim($_POST['ciudad']);
    $codigo_postal = trim($_POST['codigo_postal']);
    $metodo_pago = $_POST['metodo_pago'];
    $notas = trim($_POST['notas']);
    
    if (empty($nombre) || empty($apellido) || empty($email) || empty($telefono) || 
        empty($direccion) || empty($ciudad) || empty($codigo_postal)) {
        $error = 'Por favor, completa todos los campos obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Por favor, ingresa un email válido.';
    } else {
        $stock_ok = true;
        foreach ($carrito_items as $item) {
            if ($item['cantidad'] > $item['stock']) {
                $stock_ok = false;
                $error = "No hay suficiente stock para {$item['nombre']}. Stock disponible: {$item['stock']}";
                break;
            }
        }
        
        if ($stock_ok) {
            $db->query('INSERT INTO pedidos (id_usuario, total, estado, metodo_pago, 
                       nombre_cliente, apellido_cliente, email_cliente, telefono_cliente,
                       direccion_envio, ciudad_envio, codigo_postal_envio, notas,
                       fecha_pedido) 
                       VALUES (:user_id, :total, :estado, :metodo_pago,
                       :nombre, :apellido, :email, :telefono,
                       :direccion, :ciudad, :codigo_postal, :notas, NOW())');
            
            $db->bind(':user_id', getCurrentUserId());
            $db->bind(':total', $total);
            $db->bind(':estado', 'pendiente');
            $db->bind(':metodo_pago', $metodo_pago);
            $db->bind(':nombre', $nombre);
            $db->bind(':apellido', $apellido);
            $db->bind(':email', $email);
            $db->bind(':telefono', $telefono);
            $db->bind(':direccion', $direccion);
            $db->bind(':ciudad', $ciudad);
            $db->bind(':codigo_postal', $codigo_postal);
            $db->bind(':notas', $notas);
            
            if ($db->execute()) {
                $db->query('SELECT LAST_INSERT_ID() as pedido_id');
                $result = $db->single();
                $pedido_id = $result['pedido_id'];
                
                $pedido_procesado = true;
                foreach ($carrito_items as $item) {
                    $db->query('INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario)
                               VALUES (:pedido_id, :producto_id, :cantidad, :precio)');
                    $db->bind(':pedido_id', $pedido_id);
                    $db->bind(':producto_id', $item['id_producto']);
                    $db->bind(':cantidad', $item['cantidad']);
                    $db->bind(':precio', $item['precio']);
                    
                    if (!$db->execute()) {
                        $pedido_procesado = false;
                        break;
                    }
                    
                    $db->query('UPDATE productos SET stock = stock - :cantidad WHERE id_producto = :producto_id');
                    $db->bind(':cantidad', $item['cantidad']);
                    $db->bind(':producto_id', $item['id_producto']);
                    
                    if (!$db->execute()) {
                        $pedido_procesado = false;
                        break;
                    }
                }
                
                if ($pedido_procesado) {
                    $db->query('DELETE FROM carrito WHERE id_usuario = :user_id');
                    $db->bind(':user_id', getCurrentUserId());
                    $db->execute();
                    
                    header("Location: pedido_confirmado.php?pedido_id=$pedido_id");
                    exit;
                } else {
                    $error = 'Error al procesar los detalles del pedido. Por favor, inténtalo de nuevo.';
                }
            } else {
                $error = 'Error al crear el pedido. Por favor, inténtalo de nuevo.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Mi Lugar Pet</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo-container">
                    <div class="logo">
                        <i class="fas fa-paw"></i>
                        <span>Mi Lugar Pet</span>
                    </div>
                </div>
                <nav class="nav">
                    <a href="index.php" class="nav-link">Inicio</a>
                    <a href="productos.php" class="nav-link">Productos</a>
                    <a href="consultas.php" class="nav-link">Consultas</a>
                    <div class="user-menu">
                        <span class="user-welcome">Hola, <?php echo getCurrentUserName(); ?></span>
                        <a href="carrito.php" class="nav-link cart-link">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count"><?php echo count($carrito_items); ?></span>
                        </a>
                        <a href="logout.php" class="nav-link logout-btn">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </div>
                </nav>
            </div>
        </div>
    </header>
    <section class="checkout-hero">
        <div class="container">
            <h1>Finalizar Compra</h1>
            <div class="checkout-steps">
                <div class="step active">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Carrito</span>
                </div>
                <div class="step active">
                    <i class="fas fa-credit-card"></i>
                    <span>Pago</span>
                </div>
                <div class="step">
                    <i class="fas fa-check"></i>
                    <span>Confirmación</span>
                </div>
            </div>
        </div>
    </section>
    <section class="checkout-section">
        <div class="container">
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="checkout-form">
                <div class="checkout-content">
                    <div class="checkout-details">
                        <div class="form-section">
                            <h2><i class="fas fa-truck"></i> Información de Envío</h2>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nombre">Nombre *</label>
                                    <input type="text" id="nombre" name="nombre" required 
                                           value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="apellido">Apellido *</label>
                                    <input type="text" id="apellido" name="apellido" required
                                           value="<?php echo isset($_POST['apellido']) ? htmlspecialchars($_POST['apellido']) : ''; ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email">Email *</label>
                                    <input type="email" id="email" name="email" required
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="telefono">Teléfono *</label>
                                    <input type="tel" id="telefono" name="telefono" required
                                           value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="direccion">Dirección *</label>
                                <input type="text" id="direccion" name="direccion" required
                                       value="<?php echo isset($_POST['direccion']) ? htmlspecialchars($_POST['direccion']) : ''; ?>">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="ciudad">Ciudad *</label>
                                    <input type="text" id="ciudad" name="ciudad" required
                                           value="<?php echo isset($_POST['ciudad']) ? htmlspecialchars($_POST['ciudad']) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="codigo_postal">Código Postal *</label>
                                    <input type="text" id="codigo_postal" name="codigo_postal" required
                                           value="<?php echo isset($_POST['codigo_postal']) ? htmlspecialchars($_POST['codigo_postal']) : ''; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="form-section">
                            <h2><i class="fas fa-credit-card"></i> Método de Pago</h2>
                            <div class="payment-methods">
                                <label class="payment-option">
                                    <input type="radio" name="metodo_pago" value="tarjeta_credito" 
                                           <?php echo (isset($_POST['metodo_pago']) && $_POST['metodo_pago'] == 'tarjeta_credito') ? 'checked' : 'checked'; ?>>
                                    <div class="payment-card">
                                        <i class="fas fa-credit-card"></i>
                                        <span>Tarjeta de Crédito</span>
                                    </div>
                                </label>
                                <label class="payment-option">
                                    <input type="radio" name="metodo_pago" value="tarjeta_debito"
                                           <?php echo (isset($_POST['metodo_pago']) && $_POST['metodo_pago'] == 'tarjeta_debito') ? 'checked' : ''; ?>>
                                    <div class="payment-card">
                                        <i class="fas fa-money-check-alt"></i>
                                        <span>Tarjeta de Débito</span>
                                    </div>
                                </label>
                                <label class="payment-option">
                                    <input type="radio" name="metodo_pago" value="transferencia"
                                           <?php echo (isset($_POST['metodo_pago']) && $_POST['metodo_pago'] == 'transferencia') ? 'checked' : ''; ?>>
                                    <div class="payment-card">
                                        <i class="fas fa-university"></i>
                                        <span>Transferencia Bancaria</span>
                                    </div>
                                </label>
                                <label class="payment-option">
                                    <input type="radio" name="metodo_pago" value="efectivo"
                                           <?php echo (isset($_POST['metodo_pago']) && $_POST['metodo_pago'] == 'efectivo') ? 'checked' : ''; ?>>
                                    <div class="payment-card">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <span>Pago Contra Entrega</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="form-section">
                            <h2><i class="fas fa-sticky-note"></i> Notas Adicionales</h2>
                            <div class="form-group">
                                <label for="notas">Comentarios o instrucciones especiales</label>
                                <textarea id="notas" name="notas" rows="4" placeholder="Ej: Entregar en horario de mañana, casa color azul, etc."><?php echo isset($_POST['notas']) ? htmlspecialchars($_POST['notas']) : ''; ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="order-summary">
                        <div class="summary-card">
                            <h2><i class="fas fa-receipt"></i> Resumen del Pedido</h2>
                            
                            <div class="order-items">
                                <?php foreach ($carrito_items as $item): ?>
                                    <div class="order-item">
                                        <img src="<?php echo $item['imagen_url'] ?: '/placeholder.svg?height=60&width=60&query=' . urlencode($item['nombre']); ?>"
                                             alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                                        <div class="item-info">
                                            <h4><?php echo htmlspecialchars($item['nombre']); ?></h4>
                                            <p>Cantidad: <?php echo $item['cantidad']; ?></p>
                                        </div>
                                        <div class="item-price">
                                            $<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="summary-totals">
                                <div class="summary-line">
                                    <span>Subtotal:</span>
                                    <span>$<?php echo number_format($subtotal, 2); ?></span>
                                </div>
                                <div class="summary-line">
                                    <span>Envío:</span>
                                    <span>Gratis</span>
                                </div>
                                <div class="summary-line total">
                                    <span>Total:</span>
                                    <span>$<?php echo number_format($total, 2); ?></span>
                                </div>
                            </div>

                            <button type="submit" name="procesar_pedido" class="place-order-btn">
                                <i class="fas fa-lock"></i>
                                Confirmar Pedido
                            </button>

                            <a href="carrito.php" class="back-to-cart">
                                <i class="fas fa-arrow-left"></i>
                                Volver al Carrito
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <i class="fas fa-paw"></i>
                        <span>Mi Lugar Pet</span>
                    </div>
                    <p>Cuidando a tus mascotas con amor y profesionalismo desde 2019.</p>
                </div>
                
                <div class="footer-section">
                    <h3>Contáctanos</h3>
                    <div class="contact-info">
                        <p><i class="fas fa-phone"></i> +1 234 567 8900</p>
                        <p><i class="fas fa-envelope"></i> info@milugarpet.com</p>
                        <p><i class="fas fa-map-marker-alt"></i> Av. Principal 123, Ciudad</p>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Acerca de</h3>
                    <ul class="footer-links">
                        <li><a href="#">Nuestra Historia</a></li>
                        <li><a href="#">Nuestro Equipo</a></li>
                        <li><a href="#">Misión y Visión</a></li>
                        <li><a href="#">Testimonios</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Redes Sociales</h3>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Mi Lugar Pet. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <style>
        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-welcome {
            color: var(--primary-teal);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .cart-link {
            position: relative;
            background: var(--primary-yellow);
            color: var(--dark-gray) !important;
            padding: 0.5rem;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--primary-orange);
            color: var(--white);
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .checkout-hero {
            background: linear-gradient(135deg, var(--primary-teal) 0%, var(--primary-orange) 100%);
            padding: 120px 0 60px;
            text-align: center;
            color: var(--white);
        }

        .checkout-hero h1 {
            font-size: 3rem;
            margin-bottom: 2rem;
        }

        .checkout-steps {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 2rem;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            opacity: 0.5;
            transition: all 0.3s ease;
        }

        .step.active {
            opacity: 1;
        }

        .step i {
            font-size: 2rem;
            background: rgba(255,255,255,0.2);
            padding: 1rem;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .step.active i {
            background: var(--white);
            color: var(--primary-teal);
        }

        .checkout-section {
            padding: 80px 0;
            background: var(--light-gray);
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkout-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 3rem;
        }

        .checkout-details {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .form-section {
            background: var(--white);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .form-section h2 {
            color: var(--primary-teal);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark-gray);
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-teal);
        }

        .payment-methods {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .payment-option {
            cursor: pointer;
        }

        .payment-option input[type="radio"] {
            display: none;
        }

        .payment-card {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
            background: var(--white);
        }

        .payment-option input[type="radio"]:checked + .payment-card {
            border-color: var(--primary-teal);
            background: rgba(var(--primary-teal-rgb), 0.1);
        }

        .payment-card i {
            font-size: 2rem;
            color: var(--primary-teal);
            margin-bottom: 0.5rem;
        }

        .payment-card span {
            display: block;
            color: var(--dark-gray);
            font-weight: 500;
        }

        .order-summary {
            background: var(--white);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        .summary-card h2 {
            color: var(--primary-teal);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .order-items {
            margin-bottom: 2rem;
        }

        .order-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
        }

        .item-info h4 {
            color: var(--dark-gray);
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }

        .item-info p {
            color: var(--text-gray);
            font-size: 0.8rem;
        }

        .item-price {
            margin-left: auto;
            font-weight: bold;
            color: var(--primary-teal);
        }

        .summary-totals {
            border-top: 2px solid #eee;
            padding-top: 1rem;
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .summary-line.total {
            font-weight: bold;
            font-size: 1.2rem;
            color: var(--primary-teal);
            border-top: 1px solid #eee;
            padding-top: 0.5rem;
            margin-top: 1rem;
        }

        .place-order-btn {
            width: 100%;
            background: var(--primary-orange);
            color: var(--white);
            border: none;
            padding: 1rem;
            border-radius: 10px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 2rem 0 1rem 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .place-order-btn:hover {
            background: var(--primary-teal);
            transform: translateY(-2px);
        }

        .back-to-cart {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: var(--primary-teal);
            text-decoration: none;
            padding: 0.5rem;
            transition: all 0.3s ease;
        }

        .back-to-cart:hover {
            color: var(--primary-orange);
        }

        @media (max-width: 768px) {
            .checkout-content {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .payment-methods {
                grid-template-columns: 1fr;
            }

            .checkout-steps {
                flex-direction: column;
                align-items: center;
            }

            .step {
                flex-direction: row;
                gap: 1rem;
            }
        }
    </style>
</body>
</html>
