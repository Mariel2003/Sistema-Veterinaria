<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (!isClient()) {
    header('Location: login.php');
    exit;
}

$pedido_id = isset($_GET['pedido_id']) ? (int)$_GET['pedido_id'] : 0;

if (!$pedido_id) {
    header('Location: index.php');
    exit;
}

$db = new Database();

$db->query('SELECT p.*, 
            COUNT(dp.id_detalle) as total_items
            FROM pedidos p
            LEFT JOIN detalle_pedidos dp ON p.id_pedido = dp.id_pedido
            WHERE p.id_pedido = :pedido_id AND p.id_usuario = :user_id
            GROUP BY p.id_pedido');
$db->bind(':pedido_id', $pedido_id);
$db->bind(':user_id', getCurrentUserId());
$pedido = $db->single();

if (!$pedido) {
    header('Location: index.php');
    exit;
}

$db->query('SELECT dp.*, p.nombre, p.imagen_url
            FROM detalle_pedidos dp
            JOIN productos p ON dp.id_producto = p.id_producto
            WHERE dp.id_pedido = :pedido_id');
$db->bind(':pedido_id', $pedido_id);
$items_pedido = $db->resultset();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado - Mi Lugar Pet</title>
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
                            <span class="cart-count">0</span>
                        </a>
                        <a href="logout.php" class="nav-link logout-btn">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <section class="confirmation-hero">
        <div class="container">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>¡Pedido Confirmado!</h1>
            <p>Tu pedido ha sido procesado exitosamente</p>
            <div class="order-number">
                Número de Pedido: <strong>#<?php echo str_pad($pedido['id_pedido'], 6, '0', STR_PAD_LEFT); ?></strong>
            </div>
        </div>
    </section>

    <section class="order-details-section">
        <div class="container">
            <div class="order-details-content">
                <div class="order-info">
                    <div class="info-card">
                        <h2><i class="fas fa-info-circle"></i> Información del Pedido</h2>
                        <div class="info-grid">
                            <div class="info-item">
                                <strong>Fecha:</strong>
                                <span><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></span>
                            </div>
                            <div class="info-item">
                                <strong>Estado:</strong>
                                <span class="status-badge status-<?php echo $pedido['estado']; ?>">
                                    <?php echo ucfirst($pedido['estado']); ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <strong>Método de Pago:</strong>
                                <span><?php echo ucwords(str_replace('_', ' ', $pedido['metodo_pago'])); ?></span>
                            </div>
                            <div class="info-item">
                                <strong>Total:</strong>
                                <span class="total-amount">$<?php echo number_format($pedido['total'], 2); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="info-card">
                        <h2><i class="fas fa-truck"></i> Información de Envío</h2>
                        <div class="shipping-info">
                            <p><strong><?php echo $pedido['nombre_cliente'] . ' ' . $pedido['apellido_cliente']; ?></strong></p>
                            <p><?php echo $pedido['direccion_envio']; ?></p>
                            <p><?php echo $pedido['ciudad_envio'] . ', ' . $pedido['codigo_postal_envio']; ?></p>
                            <p><i class="fas fa-phone"></i> <?php echo $pedido['telefono_cliente']; ?></p>
                            <p><i class="fas fa-envelope"></i> <?php echo $pedido['email_cliente']; ?></p>
                        </div>
                    </div>

                    <?php if ($pedido['notas']): ?>
                    <div class="info-card">
                        <h2><i class="fas fa-sticky-note"></i> Notas Adicionales</h2>
                        <p><?php echo nl2br(htmlspecialchars($pedido['notas'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="order-items">
                    <div class="items-card">
                        <h2><i class="fas fa-box"></i> Productos Pedidos</h2>
                        <div class="items-list">
                            <?php foreach ($items_pedido as $item): ?>
                                <div class="order-item">
                                    <img src="<?php echo $item['imagen_url'] ?: '/placeholder.svg?height=80&width=80&query=' . urlencode($item['nombre']); ?>"
                                         alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                                    <div class="item-details">
                                        <h3><?php echo htmlspecialchars($item['nombre']); ?></h3>
                                        <p>Cantidad: <?php echo $item['cantidad']; ?></p>
                                        <p>Precio unitario: $<?php echo number_format($item['precio_unitario'], 2); ?></p>
                                    </div>
                                    <div class="item-total">
                                        $<?php echo number_format($item['precio_unitario'] * $item['cantidad'], 2); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="order-total">
                            <strong>Total del Pedido: $<?php echo number_format($pedido['total'], 2); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="next-steps">
                <div class="steps-card">
                    <h2><i class="fas fa-list-ol"></i> Próximos Pasos</h2>
                    <div class="steps-list">
                        <div class="step-item">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h3>Confirmación por Email</h3>
                                <p>Recibirás un email de confirmación con todos los detalles de tu pedido.</p>
                            </div>
                        </div>
                        <div class="step-item">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h3>Preparación del Pedido</h3>
                                <p>Nuestro equipo preparará tu pedido con el mayor cuidado.</p>
                            </div>
                        </div>
                        <div class="step-item">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h3>Envío y Entrega</h3>
                                <p>Te notificaremos cuando tu pedido esté en camino.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <a href="productos.php" class="btn-secondary">
                    <i class="fas fa-shopping-bag"></i>
                    Seguir Comprando
                </a>
                <a href="index.php" class="btn-primary">
                    <i class="fas fa-home"></i>
                    Volver al Inicio
                </a>
            </div>
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

        .confirmation-hero {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            padding: 120px 0 80px;
            text-align: center;
            color: var(--white);
        }

        .success-icon {
            font-size: 5rem;
            margin-bottom: 2rem;
            animation: bounce 1s ease-in-out;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        .confirmation-hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .order-number {
            background: rgba(255,255,255,0.2);
            padding: 1rem 2rem;
            border-radius: 25px;
            display: inline-block;
            margin-top: 2rem;
            font-size: 1.2rem;
        }

        .order-details-section {
            padding: 80px 0;
            background: var(--light-gray);
        }

        .order-details-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .info-card, .items-card {
            background: var(--white);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .info-card h2, .items-card h2 {
            color: var(--primary-teal);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-grid {
            display: grid;
            gap: 1rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pendiente {
            background: #fff3cd;
            color: #856404;
        }

        .total-amount {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-orange);
        }

        .shipping-info p {
            margin-bottom: 0.5rem;
            color: var(--dark-gray);
        }

        .items-list {
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
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
        }

        .item-details {
            flex: 1;
        }

        .item-details h3 {
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
        }

        .item-details p {
            color: var(--text-gray);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .item-total {
            font-weight: bold;
            color: var(--primary-teal);
            font-size: 1.1rem;
        }

        .order-total {
            text-align: right;
            padding-top: 1rem;
            border-top: 2px solid #eee;
            font-size: 1.3rem;
            color: var(--primary-teal);
        }

        .next-steps {
            margin-bottom: 3rem;
        }

        .steps-card {
            background: var(--white);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .steps-card h2 {
            color: var(--primary-teal);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .steps-list {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .step-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .step-number {
            background: var(--primary-teal);
            color: var(--white);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }

        .step-content h3 {
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
        }

        .step-content p {
            color: var(--text-gray);
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 2rem;
        }

        .btn-primary, .btn-secondary {
            padding: 1rem 2rem;
            border-radius: 25px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary-teal);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-orange);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--primary-teal);
            border: 2px solid var(--primary-teal);
        }

        .btn-secondary:hover {
            background: var(--primary-teal);
            color: var(--white);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .order-details-content {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .confirmation-hero h1 {
                font-size: 2rem;
            }

            .success-icon {
                font-size: 3rem;
            }
        }
    </style>
</body>
</html>
