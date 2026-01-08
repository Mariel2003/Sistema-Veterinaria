<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireEmployee();

$pedido_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$pedido_id) {
    header('Location: ventas.php');
    exit;
}

$conexion = getMySQLConnection();

$stmt = $conexion->prepare("
    SELECT p.*, u.nombre as cliente_nombre, u.email as cliente_email, u.telefono as cliente_telefono
    FROM pedidos p 
    JOIN usuarios u ON p.id_usuario = u.id_usuario 
    WHERE p.id_pedido = ?
");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$result = $stmt->get_result();
$pedido = $result->fetch_assoc();
$stmt->close();

if (!$pedido) {
    header('Location: ventas.php');
    exit;
}

$stmt = $conexion->prepare("
    SELECT dp.*, pr.nombre, pr.imagen_url
    FROM detalle_pedidos dp
    JOIN productos pr ON dp.id_producto = pr.id_producto
    WHERE dp.id_pedido = ?
");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$result = $stmt->get_result();
$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}
$stmt->close();
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Pedido #<?php echo str_pad($pedido['id_pedido'], 6, '0', STR_PAD_LEFT); ?> - Mi Lugar Pet</title>
    <link rel="stylesheet" href="../styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="employee-header">
        <div class="container">
            <div class="employee-header-content">
                <div class="logo">
                    <i class="fas fa-paw"></i>
                    <span>Mi Lugar Pet - Empleado</span>
                </div>
                <div class="employee-nav">
                    <span class="welcome">Bienvenido, <?php echo getCurrentUserName(); ?></span>
                    <a href="../logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="employee-layout">
        <aside class="employee-sidebar">
            <nav class="employee-menu">
                <a href="dashboard.php" class="menu-item">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="consultas.php" class="menu-item">
                    <i class="fas fa-calendar-alt"></i>
                    Consultas
                </a>
                <a href="productos.php" class="menu-item">
                    <i class="fas fa-box"></i>
                    Productos
                </a>
                <a href="ventas.php" class="menu-item active">
                    <i class="fas fa-shopping-cart"></i>
                    Ventas
                </a>
            </nav>
        </aside>

        <main class="employee-main">
            <div class="employee-content">
                <div class="page-header">
                    <div class="header-left">
                        <a href="ventas.php" class="back-btn">
                            <i class="fas fa-arrow-left"></i>
                            Volver a Ventas
                        </a>
                        <h1>Pedido #<?php echo str_pad($pedido['id_pedido'], 6, '0', STR_PAD_LEFT); ?></h1>
                        <p>Detalles completos del pedido</p>
                    </div>
                    <div class="header-right">
                        <span class="status-badge <?php echo $pedido['estado']; ?>">
                            <?php echo ucfirst($pedido['estado']); ?>
                        </span>
                    </div>
                </div>

                <div class="order-detail-grid">
                    <div class="detail-card">
                        <div class="card-header">
                            <h3><i class="fas fa-user"></i> Información del Cliente</h3>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <strong>Nombre:</strong>
                                <span><?php echo htmlspecialchars($pedido['nombre_cliente'] . ' ' . $pedido['apellido_cliente']); ?></span>
                            </div>
                            <div class="info-row">
                                <strong>Email:</strong>
                                <span><?php echo htmlspecialchars($pedido['email_cliente']); ?></span>
                            </div>
                            <div class="info-row">
                                <strong>Teléfono:</strong>
                                <span><?php echo htmlspecialchars($pedido['telefono_cliente']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="detail-card">
                        <div class="card-header">
                            <h3><i class="fas fa-truck"></i> Información de Envío</h3>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <strong>Dirección:</strong>
                                <span><?php echo htmlspecialchars($pedido['direccion_envio']); ?></span>
                            </div>
                            <div class="info-row">
                                <strong>Ciudad:</strong>
                                <span><?php echo htmlspecialchars($pedido['ciudad_envio']); ?></span>
                            </div>
                            <div class="info-row">
                                <strong>Código Postal:</strong>
                                <span><?php echo htmlspecialchars($pedido['codigo_postal_envio']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="detail-card">
                        <div class="card-header">
                            <h3><i class="fas fa-info-circle"></i> Información del Pedido</h3>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <strong>Fecha:</strong>
                                <span><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></span>
                            </div>
                            <div class="info-row">
                                <strong>Método de Pago:</strong>
                                <span><?php echo ucwords(str_replace('_', ' ', $pedido['metodo_pago'])); ?></span>
                            </div>
                            <div class="info-row">
                                <strong>Total:</strong>
                                <span class="total-amount">$<?php echo number_format($pedido['total'], 2); ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if ($pedido['notas']): ?>
                    <div class="detail-card full-width">
                        <div class="card-header">
                            <h3><i class="fas fa-sticky-note"></i> Notas del Cliente</h3>
                        </div>
                        <div class="card-body">
                            <p><?php echo nl2br(htmlspecialchars($pedido['notas'])); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="detail-card">
                    <div class="card-header">
                        <h3><i class="fas fa-box"></i> Productos del Pedido</h3>
                    </div>
                    <div class="card-body">
                        <div class="products-list">
                            <?php foreach ($items as $item): ?>
                                <div class="product-item">
                                    <div class="product-image">
                                        <img src="<?php echo $item['imagen_url'] ?: '/placeholder.svg?height=80&width=80&query=' . urlencode($item['nombre']); ?>"
                                             alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                                    </div>
                                    <div class="product-details">
                                        <h4><?php echo htmlspecialchars($item['nombre']); ?></h4>
                                        <p>Cantidad: <?php echo $item['cantidad']; ?></p>
                                        <p>Precio unitario: Bs.<?php echo number_format($item['precio_unitario'], 2); ?></p>
                                    </div>
                                    <div class="product-total">
                                        <strong>Bs.<?php echo number_format($item['precio_unitario'] * $item['cantidad'], 2); ?></strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="order-summary">
                            <div class="summary-line">
                                <span>Total del Pedido:</span>
                                <strong>Bs.<?php echo number_format($pedido['total'], 2); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        .employee-header {
            background: var(--primary-yellow);
            color: var(--dark-gray);
            padding: 1rem 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .employee-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .employee-header .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-teal);
        }

        .employee-nav {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .welcome {
            font-weight: 500;
            color: var(--primary-teal);
        }

        .logout-btn {
            background: var(--primary-orange);
            color: var(--white);
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: var(--primary-teal);
        }

        .employee-layout {
            display: flex;
            margin-top: 70px;
            min-height: calc(100vh - 70px);
        }

        .employee-sidebar {
            width: 250px;
            background: var(--white);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            height: calc(100vh - 70px);
            overflow-y: auto;
        }

        .employee-menu {
            padding: 2rem 0;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 2rem;
            color: var(--dark-gray);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .menu-item:hover,
        .menu-item.active {
            background: var(--primary-blue);
            color: var(--primary-teal);
            border-right: 3px solid var(--primary-yellow);
        }

        .employee-main {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
            background: var(--light-gray);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-teal);
            text-decoration: none;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            color: var(--primary-orange);
        }

        .page-header h1 {
            color: var(--primary-teal);
            margin: 0;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-badge.pendiente {
            background: #fff3cd;
            color: #856404;
        }

        .status-badge.procesando {
            background: #cce5ff;
            color: #004085;
        }

        .status-badge.enviado {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-badge.entregado {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.cancelado {
            background: #f8d7da;
            color: #721c24;
        }

        .order-detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .detail-card {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .detail-card.full-width {
            grid-column: 1 / -1;
        }

        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
        }

        .card-header h3 {
            color: var(--primary-teal);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-row:last-child {
            margin-bottom: 0;
            border-bottom: none;
        }

        .info-row strong {
            color: var(--dark-gray);
        }

        .total-amount {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-orange);
        }

        .products-list {
            margin-bottom: 2rem;
        }

        .product-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .product-item:last-child {
            border-bottom: none;
        }

        .product-image img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
        }

        .product-details {
            flex: 1;
        }

        .product-details h4 {
            color: var(--dark-gray);
            margin: 0 0 0.5rem 0;
        }

        .product-details p {
            color: var(--text-gray);
            margin: 0.25rem 0;
            font-size: 0.9rem;
        }

        .product-total {
            font-size: 1.1rem;
            color: var(--primary-teal);
        }

        .order-summary {
            border-top: 2px solid #eee;
            padding-top: 1rem;
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 1.2rem;
            color: var(--primary-teal);
        }

        @media (max-width: 768px) {
            .employee-sidebar {
                transform: translateX(-100%);
            }

            .employee-main {
                margin-left: 0;
            }

            .page-header {
                flex-direction: column;
                gap: 1rem;
            }

            .order-detail-grid {
                grid-template-columns: 1fr;
            }

            .product-item {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</body>
</html>
