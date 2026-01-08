<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (!isClient()) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_quantity'])) {
        $carrito_id = $_POST['carrito_id'];
        $nueva_cantidad = max(1, (int)$_POST['cantidad']);
        
        $db->query('UPDATE carrito SET cantidad = :cantidad WHERE id_carrito = :id AND id_usuario = :user_id');
        $db->bind(':cantidad', $nueva_cantidad);
        $db->bind(':id', $carrito_id);
        $db->bind(':user_id', getCurrentUserId());
        $db->execute();
        
        $message = 'Cantidad actualizada correctamente.';
    }
    
    if (isset($_POST['remove_item'])) {
        $carrito_id = $_POST['carrito_id'];
        
        $db->query('DELETE FROM carrito WHERE id_carrito = :id AND id_usuario = :user_id');
        $db->bind(':id', $carrito_id);
        $db->bind(':user_id', getCurrentUserId());
        $db->execute();
        
        $message = 'Producto eliminado del carrito.';
    }
}

$db->query('SELECT c.*, p.nombre, p.precio, p.imagen_url, p.stock 
           FROM carrito c 
           JOIN productos p ON c.id_producto = p.id_producto 
           WHERE c.id_usuario = :user_id 
           ORDER BY c.fecha_agregado DESC');
$db->bind(':user_id', getCurrentUserId());
$carrito_items = $db->resultset();

$total = 0;
foreach ($carrito_items as $item) {
    $total += $item['precio'] * $item['cantidad'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - Mi Lugar Pet</title>
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
                        <a href="carrito.php" class="nav-link cart-link active">
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
    <section class="cart-hero">
        <div class="container">
            <h1>Carrito de Compras</h1>
            <p>Revisa tus productos antes de finalizar la compra</p>
        </div>
    </section>
    <section class="cart-section">
        <div class="container">
            <?php if ($message): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($carrito_items)): ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Tu carrito está vacío</h3>
                    <p>¡Agrega algunos productos para empezar!</p>
                    <a href="productos.php" class="btn-primary">Ver Productos</a>
                </div>
            <?php else: ?>
                <div class="cart-content">
                    <div class="cart-items">
                        <h2>Productos en tu carrito</h2>
                        <?php foreach ($carrito_items as $item): ?>
                            <div class="cart-item">
                                <div class="item-image">
                                    <img src="<?php echo $item['imagen_url'] ?: '/placeholder.svg?height=100&width=100&query=' . urlencode($item['nombre']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                                </div>
                                <div class="item-details">
                                    <h3><?php echo htmlspecialchars($item['nombre']); ?></h3>
                                    <p class="item-price">Bs.<?php echo number_format($item['precio'], 2); ?></p>
                                    <p class="item-stock">Stock disponible: <?php echo $item['stock']; ?></p>
                                </div>
                                <div class="item-quantity">
                                    <form method="POST" class="quantity-form">
                                        <input type="hidden" name="carrito_id" value="<?php echo $item['id_carrito']; ?>">
                                        <input type="number" name="cantidad" value="<?php echo $item['cantidad']; ?>" 
                                               min="1" max="<?php echo $item['stock']; ?>" class="quantity-input">
                                        <button type="submit" name="update_quantity" class="update-btn">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                    </form>
                                </div>
                                <div class="item-subtotal">
                                    <p>Bs.<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></p>
                                </div>
                                <div class="item-actions">
                                    <form method="POST">
                                        <input type="hidden" name="carrito_id" value="<?php echo $item['id_carrito']; ?>">
                                        <button type="submit" name="remove_item" class="remove-btn" 
                                                onclick="return confirm('¿Estás seguro de eliminar este producto?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="cart-summary">
                        <div class="summary-card">
                            <h3>Resumen del Pedido</h3>
                            <div class="summary-line">
                                <span>Subtotal:</span>
                                <span>Bs.<?php echo number_format($total, 2); ?></span>
                            </div>
                            <div class="summary-line">
                                <span>Envío:</span>
                                <span>Gratis</span>
                            </div>
                            <div class="summary-line total">
                                <span>Total:</span>
                                <span>Bs.<?php echo number_format($total, 2); ?></span>
                            </div>
                            
                            <form method="POST" action="checkout.php">
                                <button type="submit" class="checkout-btn">
                                    <i class="fas fa-credit-card"></i>
                                    Proceder al Pago
                                </button>
                            </form>
                            
                            <a href="productos.php" class="continue-shopping">
                                <i class="fas fa-arrow-left"></i>
                                Continuar Comprando
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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
                        <p><i class="fas fa-phone"></i> +591 62960477</p>
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

        .cart-link.active {
            background: var(--primary-orange);
            color: var(--white) !important;
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

        .cart-hero {
            background: linear-gradient(135deg, var(--primary-yellow) 0%, var(--primary-orange) 100%);
            padding: 120px 0 60px;
            text-align: center;
            color: var(--white);
        }

        .cart-hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .cart-section {
            padding: 80px 0;
            background: var(--light-gray);
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .empty-cart i {
            font-size: 4rem;
            color: var(--primary-teal);
            margin-bottom: 2rem;
        }

        .empty-cart h3 {
            color: var(--primary-teal);
            margin-bottom: 1rem;
        }

        .btn-primary {
            background: var(--primary-teal);
            color: var(--white);
            padding: 1rem 2rem;
            border-radius: 25px;
            text-decoration: none;
            display: inline-block;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--primary-orange);
        }

        .cart-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 3rem;
        }

        .cart-items {
            background: var(--white);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .cart-items h2 {
            color: var(--primary-teal);
            margin-bottom: 2rem;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto auto auto;
            gap: 1rem;
            align-items: center;
            padding: 1.5rem 0;
            border-bottom: 1px solid #eee;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
        }

        .item-details h3 {
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
        }

        .item-price {
            color: var(--primary-orange);
            font-weight: bold;
            font-size: 1.1rem;
        }

        .item-stock {
            color: var(--text-gray);
            font-size: 0.9rem;
        }

        .quantity-form {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quantity-input {
            width: 60px;
            padding: 0.5rem;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            text-align: center;
        }

        .update-btn {
            background: var(--primary-teal);
            color: var(--white);
            border: none;
            padding: 0.5rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .update-btn:hover {
            background: var(--primary-orange);
        }

        .item-subtotal p {
            font-weight: bold;
            color: var(--primary-teal);
            font-size: 1.1rem;
        }

        .remove-btn {
            background: #dc3545;
            color: var(--white);
            border: none;
            padding: 0.5rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .remove-btn:hover {
            background: #c82333;
        }

        .cart-summary {
            background: var(--white);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            height: fit-content;
        }

        .summary-card h3 {
            color: var(--primary-teal);
            margin-bottom: 1.5rem;
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
        }

        .summary-line.total {
            border-top: 2px solid #eee;
            padding-top: 1rem;
            font-weight: bold;
            font-size: 1.2rem;
            color: var(--primary-teal);
        }

        .checkout-btn {
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

        .checkout-btn:hover {
            background: var(--primary-teal);
            transform: translateY(-2px);
        }

        .continue-shopping {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: var(--primary-teal);
            text-decoration: none;
            padding: 0.5rem;
            transition: all 0.3s ease;
        }

        .continue-shopping:hover {
            color: var(--primary-orange);
        }

        @media (max-width: 768px) {
            .cart-content {
                grid-template-columns: 1fr;
            }

            .cart-item {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 1rem;
            }

            .quantity-form {
                justify-content: center;
            }
        }
    </style>
</body>
</html>