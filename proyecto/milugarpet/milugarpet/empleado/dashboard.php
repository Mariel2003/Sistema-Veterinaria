<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireEmployee();

$conexion = getMySQLConnection();

$db = new Database();

$db->query('SELECT COUNT(*) as total FROM consultas WHERE estado = "pendiente"');
$consultas_pendientes = $db->single()['total'];

$db->query('SELECT COUNT(*) as total FROM productos WHERE stock <= 5 AND activo = 1');
$productos_stock_bajo = $db->single()['total'];

$db->query('SELECT COUNT(*) as total FROM consultas WHERE DATE(fecha) = CURDATE()');
$consultas_hoy = $db->single()['total'];

$db->query('SELECT COUNT(*) as total FROM pedidos WHERE estado = "pendiente"');
$pedidos_pendientes = $db->single()['total'];

$db->query('SELECT COUNT(*) as total FROM pedidos WHERE DATE(fecha_pedido) = CURDATE()');
$pedidos_hoy = $db->single()['total'];

$db->query('SELECT SUM(total) as ingresos FROM pedidos WHERE estado IN ("procesando", "enviado", "entregado") AND DATE(fecha_pedido) = CURDATE()');
$ingresos_hoy = $db->single()['ingresos'] ?? 0;

$db->query('SELECT c.*, u.nombre as cliente_nombre FROM consultas c
        JOIN usuarios u ON c.id_usuario = u.id_usuario
        WHERE c.estado = "pendiente"
       ORDER BY c.fecha DESC LIMIT 5');
$consultas_recientes = $db->resultset();

$db->query('SELECT * FROM productos WHERE stock <= 5 AND activo = 1 ORDER BY stock ASC LIMIT 5');
$productos_bajo_stock = $db->resultset();

$db->query('SELECT p.*, u.nombre as cliente_nombre FROM pedidos p
        JOIN usuarios u ON p.id_usuario = u.id_usuario
        ORDER BY p.fecha_pedido DESC LIMIT 5');
$pedidos_recientes = $db->resultset();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Empleado - Mi Lugar Pet</title>
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
                <a href="dashboard.php" class="menu-item active">
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
                <a href="ventas.php" class="menu-item">
                    <i class="fas fa-shopping-cart"></i>
                    Ventas
                </a>
            </nav>
        </aside>

        <main class="employee-main">
            <div class="employee-content">
                <div class="page-header">
                    <h1>Dashboard Empleado</h1>
                    <p>Panel de control para empleados</p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card pending">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $consultas_pendientes; ?></h3>
                            <p>Consultas Pendientes</p>
                        </div>
                        <div class="stat-action">
                            <a href="consultas.php">Ver todas</a>
                        </div>
                    </div>

                    <div class="stat-card warning">
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $productos_stock_bajo; ?></h3>
                            <p>Productos Stock Bajo</p>
                        </div>
                        <div class="stat-action">
                            <a href="productos.php">Revisar</a>
                        </div>
                    </div>

                    <div class="stat-card sales">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $pedidos_pendientes; ?></h3>
                            <p>Pedidos Pendientes</p>
                        </div>
                        <div class="stat-action">
                            <a href="ventas.php">Procesar</a>
                        </div>
                    </div>

                    <div class="stat-card today">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $pedidos_hoy; ?></h3>
                            <p>Pedidos Hoy</p>
                        </div>
                        <div class="stat-action">
                            <a href="ventas.php">Ver hoy</a>
                        </div>
                    </div>

                    <div class="stat-card revenue">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Bs.<?php echo number_format($ingresos_hoy, 2); ?></h3>
                            <p>Ingresos Hoy</p>
                        </div>
                        <div class="stat-action">
                            <a href="ventas.php">Ver ventas</a>
                        </div>
                    </div>

                    <div class="stat-card info">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $consultas_hoy; ?></h3>
                            <p>Consultas Hoy</p>
                        </div>
                        <div class="stat-action">
                            <a href="consultas.php">Ver hoy</a>
                        </div>
                    </div>
                </div>

                <div class="quick-actions">
                    <div class="actions-card">
                        <div class="card-header">
                            <h3>Acciones Rápidas</h3>
                        </div>
                        <div class="actions-grid">
                            <a href="consultas.php" class="action-btn">
                                <i class="fas fa-calendar-plus"></i>
                                <span>Gestionar Consultas</span>
                            </a>
                            <a href="productos.php" class="action-btn">
                                <i class="fas fa-plus-circle"></i>
                                <span>Agregar Producto</span>
                            </a>
                            <a href="ventas.php" class="action-btn">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Ver Pedidos</span>
                            </a>
                            <a href="productos.php?stock_bajo=1" class="action-btn">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Stock Bajo</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="activity-grid">
                    <div class="activity-card">
                        <div class="card-header">
                            <h3>Consultas Pendientes</h3>
                            <a href="consultas.php" class="view-all">Ver todas</a>
                        </div>
                        <div class="activity-list">
                            <?php if (empty($consultas_recientes)): ?>
                                <div class="no-activity">
                                    <i class="fas fa-check-circle"></i>
                                    <p>¡No hay consultas pendientes!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach($consultas_recientes as $consulta): ?>
                                    <div class="activity-item">
                                        <div class="activity-info">
                                            <h4><?php echo htmlspecialchars($consulta['cliente_nombre']); ?></h4>
                                            <p><?php echo htmlspecialchars(substr($consulta['motivo'], 0, 50)) . '...'; ?></p>
                                            <small><?php echo date('d/m/Y H:i', strtotime($consulta['fecha'])); ?></small>
                                        </div>
                                        <div class="activity-action">
                                            <a href="consultas.php?id=<?php echo $consulta['id_consulta']; ?>" class="btn-action">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="activity-card">
                        <div class="card-header">
                            <h3>Pedidos Recientes</h3>
                            <a href="ventas.php" class="view-all">Ver todos</a>
                        </div>
                        <div class="activity-list">
                            <?php if (empty($pedidos_recientes)): ?>
                                <div class="no-activity">
                                    <i class="fas fa-shopping-cart"></i>
                                    <p>¡No hay pedidos recientes!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach($pedidos_recientes as $pedido): ?>
                                    <div class="activity-item">
                                        <div class="activity-info">
                                            <h4><?php echo htmlspecialchars($pedido['cliente_nombre']); ?></h4>
                                            <p>Total: Bs.<?php echo number_format($pedido['total'], 2); ?></p>
                                            <small>
                                                <span class="status-badge <?php echo $pedido['estado']; ?>">
                                                    <?php echo ucfirst($pedido['estado']); ?>
                                                </span>
                                                - <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?>
                                            </small>
                                        </div>
                                        <div class="activity-action">
                                            <a href="ventas.php" class="btn-action">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="activity-card">
                        <div class="card-header">
                            <h3>Productos Stock Bajo</h3>
                            <a href="productos.php" class="view-all">Ver todos</a>
                        </div>
                        <div class="activity-list">
                            <?php if (empty($productos_bajo_stock)): ?>
                                <div class="no-activity">
                                    <i class="fas fa-check-circle"></i>
                                    <p>¡Stock en buen estado!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach($productos_bajo_stock as $producto): ?>
                                    <div class="activity-item">
                                        <div class="activity-info">
                                            <h4><?php echo htmlspecialchars($producto['nombre']); ?></h4>
                                            <p>Categoría: <?php echo htmlspecialchars($producto['categoria']); ?></p>
                                            <small class="stock-warning">Stock: <?php echo $producto['stock']; ?> unidades</small>
                                        </div>
                                        <div class="activity-action">
                                            <a href="productos.php?edit=<?php echo $producto['id_producto']; ?>" class="btn-action warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
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
            margin-bottom: 2rem;
        }

        .page-header h1 {
            color: var(--primary-teal);
            margin-bottom: 0.5rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .stat-card.pending {
            border-left: 4px solid #ffc107;
        }

        .stat-card.warning {
            border-left: 4px solid #dc3545;
        }

        .stat-card.today {
            border-left: 4px solid var(--primary-teal);
        }

        .stat-card.sales {
            border-left: 4px solid var(--primary-orange);
        }

        .stat-card.revenue {
            border-left: 4px solid #28a745;
        }

        .stat-card.info {
            border-left: 4px solid #17a2b8;
        }

        .stat-icon {
            font-size: 2.5rem;
            color: var(--primary-teal);
        }

        .stat-info h3 {
            font-size: 1.8rem;
            color: var(--primary-teal);
            margin: 0;
        }

        .stat-info p {
            color: var(--text-gray);
            margin: 0;
        }

        .stat-action a {
            color: var(--primary-orange);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .stat-action a:hover {
            color: var(--primary-teal);
        }

        .quick-actions {
            margin-bottom: 3rem;
        }

        .actions-card {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
        }

        .card-header h3 {
            color: var(--primary-teal);
            margin: 0;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            padding: 1.5rem;
        }

        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            padding: 2rem;
            background: var(--primary-blue);
            border-radius: 10px;
            text-decoration: none;
            color: var(--primary-teal);
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background: var(--primary-yellow);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .action-btn i {
            font-size: 2rem;
        }

        .action-btn span {
            font-weight: 500;
        }

        .activity-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }

        .activity-card {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .view-all {
            color: var(--primary-orange);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .activity-list {
            padding: 1rem;
        }

        .activity-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-info h4 {
            margin-bottom: 0.5rem;
            color: var(--dark-gray);
        }

        .activity-info p {
            color: var(--text-gray);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .activity-info small {
            color: var(--text-gray);
            font-size: 0.8rem;
        }

        .stock-warning {
            color: #dc3545 !important;
            font-weight: bold;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 10px;
            font-size: 0.7rem;
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

        .btn-action {
            background: var(--primary-teal);
            color: var(--white);
            border: none;
            padding: 0.5rem;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-action.warning {
            background: #ffc107;
            color: var(--dark-gray);
        }

        .btn-action:hover {
            transform: scale(1.1);
        }

        .no-activity {
            text-align: center;
            padding: 2rem;
            color: var(--text-gray);
        }

        .no-activity i {
            font-size: 2rem;
            color: #28a745;
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
            .employee-sidebar {
                transform: translateX(-100%);
            }
            .employee-main {
                margin-left: 0;
            }
            .activity-grid {
                grid-template-columns: 1fr;
            }
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
            .actions-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</body>
</html>
