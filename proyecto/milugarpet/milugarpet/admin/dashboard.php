<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

$db = new Database();

$db->query('SELECT COUNT(*) as total FROM usuarios WHERE rol = "cliente"');
$total_clientes = $db->single()['total'];

$db->query('SELECT COUNT(*) as total FROM productos WHERE activo = 1');
$total_productos = $db->single()['total'];

$db->query('SELECT COUNT(*) as total FROM consultas');
$total_consultas = $db->single()['total'];

$db->query('SELECT COUNT(*) as total FROM pedidos');
$total_pedidos = $db->single()['total'];

$db->query('SELECT c.*, u.nombre as cliente_nombre FROM consultas c
            JOIN usuarios u ON c.id_usuario = u.id_usuario
            ORDER BY c.fecha DESC LIMIT 5');
$consultas_recientes = $db->resultset();

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
    <title>Dashboard Administrador - Mi Lugar Pet</title>
    <link rel="stylesheet" href="../styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <div class="admin-header-content">
                <div class="logo">
                    <i class="fas fa-paw"></i>
                    <span>Mi Lugar Pet - Admin</span>
                </div>
                <div class="admin-nav">
                    <span class="welcome">Bienvenido, <?php echo getCurrentUserName(); ?></span>
                    <a href="../logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </header>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <nav class="admin-menu">
                <a href="dashboard.php" class="menu-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="usuarios.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    Usuarios
                </a>
                <a href="productos.php" class="menu-item">
                    <i class="fas fa-box"></i>
                    Productos
                </a>
                <a href="consultas.php" class="menu-item">
                    <i class="fas fa-calendar-alt"></i>
                    Consultas
                </a>
                <a href="ventas.php" class="menu-item">
                    <i class="fas fa-shopping-cart"></i>
                    Pedidos
                </a>
            </nav>
        </aside>
        <main class="admin-main">
            <div class="admin-content">
                <div class="page-header">
                    <h1>Dashboard</h1>
                    <p>Resumen general del sistema</p>
                </div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_clientes; ?></h3>
                            <p>Clientes Registrados</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_productos; ?></h3>
                            <p>Productos Activos</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_consultas; ?></h3>
                            <p>Consultas Totales</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_pedidos; ?></h3>
                            <p>Total Pedidos</p>
                        </div>
                    </div>
                </div>
                <div class="activity-grid">
                    <div class="activity-card">
                        <div class="card-header">
                            <h3>Consultas Recientes</h3>
                            <a href="consultas.php" class="view-all">Ver todas</a>
                        </div>
                        <div class="activity-list">
                            <?php foreach($consultas_recientes as $consulta): ?>
                                <div class="activity-item">
                                    <div class="activity-info">
                                        <h4><?php echo htmlspecialchars($consulta['cliente_nombre']); ?></h4>
                                        <p><?php echo htmlspecialchars(substr($consulta['motivo'], 0, 50)) . '...'; ?></p>
                                        <small><?php echo date('d/m/Y H:i', strtotime($consulta['fecha'])); ?></small>
                                    </div>
                                    <div class="activity-status">
                                        <span class="status <?php echo $consulta['estado']; ?>">
                                            <?php echo ucfirst($consulta['estado']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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
                                                <span class="status <?php echo $pedido['estado']; ?>">
                                                    <?php echo ucfirst($pedido['estado']); ?>
                                                </span>
                                                - <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?>
                                            </small>
                                        </div>
                                        <div class="activity-status">
                                            <span class="status <?php echo $pedido['estado']; ?>">
                                                <?php echo ucfirst($pedido['estado']); ?>
                                            </span>
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
        .admin-header {
            background: var(--primary-teal);
            color: var(--white);
            padding: 1rem 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        .admin-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-header .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--white);
        }
        .admin-nav {
            display: flex;
            align-items: center;
            gap: 2rem;
        }
        .welcome {
            font-weight: 500;
            color: var(--white);
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
            background: var(--primary-yellow);
        }
        .admin-layout {
            display: flex;
            margin-top: 70px;
            min-height: calc(100vh - 70px);
        }
        .admin-sidebar {
            width: 250px;
            background: var(--white);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            height: calc(100vh - 70px);
            overflow-y: auto;
        }
        .admin-menu {
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
            border-right: 3px solid var(--primary-teal);
        }
        .admin-main {
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
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        .stat-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        .stat-icon {
            font-size: 3rem;
            color: var(--primary-orange);
        }
        .stat-info h3 {
            font-size: 2rem;
            color: var(--primary-teal);
            margin-bottom: 0.5rem;
        }
        .stat-info p {
            color: var(--text-gray);
        }
        .activity-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        .activity-card {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-header h3 {
            color: var(--primary-teal);
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
        .status {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .status.pendiente {
            background: #fff3cd;
            color: #856404;
        }
        .status.procesando {
            background: #cce5ff;
            color: #004085;
        }
        .status.enviado {
            background: #d1ecf1;
            color: #0c5460;
        }
        .status.entregado,
        .status.finalizada {
            background: #d4edda;
            color: #155724;
        }
        .status.cancelado {
            background: #f8d7da;
            color: #721c24;
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
            .admin-sidebar {
                transform: translateX(-100%);
            }
            .admin-main {
                margin-left: 0;
            }
            .activity-grid {
                grid-template-columns: 1fr;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
