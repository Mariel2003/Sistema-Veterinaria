<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

$message = '';
$error = '';

// Manejar petición AJAX para detalles del pedido
if (isset($_GET['ajax']) && $_GET['ajax'] === 'pedido_details' && isset($_GET['pedido_id'])) {
    header('Content-Type: application/json');
    
    $pedido_id = intval($_GET['pedido_id']);
    
    try {
        $conexion = getMySQLConnection();
        
        // Obtener información del pedido
        $stmt = $conexion->prepare("
            SELECT p.*, u.nombre as cliente_nombre, u.email as cliente_email, u.telefono as cliente_telefono
            FROM pedidos p 
            JOIN usuarios u ON p.id_usuario = u.id_usuario 
            WHERE p.id_pedido = ?
        ");
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
            exit;
        }
        
        $pedido = $result->fetch_assoc();
        $stmt->close();
        
        // Obtener detalles del pedido
        $stmt = $conexion->prepare("
            SELECT dp.*, pr.nombre as nombre_producto, pr.categoria, pr.descripcion
            FROM detalle_pedidos dp
            JOIN productos pr ON dp.id_producto = pr.id_producto
            WHERE dp.id_pedido = ?
            ORDER BY dp.id_detalle
        ");
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $detalles = [];
        while ($row = $result->fetch_assoc()) {
            $detalles[] = $row;
        }
        
        $stmt->close();
        $conexion->close();
        
        echo json_encode([
            'success' => true,
            'pedido' => $pedido,
            'detalles' => $detalles
        ]);
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conexion = getMySQLConnection();
        
    if (isset($_POST['update_status'])) {
        $pedido_id = $_POST['pedido_id'];
        $nuevo_estado = $_POST['nuevo_estado'];
                
        $stmt = $conexion->prepare("UPDATE pedidos SET estado = ? WHERE id_pedido = ?");
        $stmt->bind_param("si", $nuevo_estado, $pedido_id);
                
        if ($stmt->execute()) {
            $message = "Estado del pedido actualizado a: " . ucfirst($nuevo_estado);
        } else {
            $error = "Error al actualizar estado: " . $conexion->error;
        }
        $stmt->close();
    }
        
    if (isset($_POST['delete_pedido'])) {
        $pedido_id = $_POST['pedido_id'];
                
        $stmt = $conexion->prepare("DELETE FROM detalle_pedidos WHERE id_pedido = ?");
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();
        $stmt->close();
                
        $stmt = $conexion->prepare("DELETE FROM pedidos WHERE id_pedido = ?");
        $stmt->bind_param("i", $pedido_id);
                
        if ($stmt->execute()) {
            $message = "Pedido eliminado exitosamente.";
        } else {
            $error = "Error al eliminar pedido: " . $conexion->error;
        }
        $stmt->close();
    }
        
    $conexion->close();
}

$conexion = getMySQLConnection();
$consulta = $conexion->query("
    SELECT p.*, u.nombre as cliente_nombre, u.email as cliente_email, u.telefono as cliente_telefono
    FROM pedidos p 
    JOIN usuarios u ON p.id_usuario = u.id_usuario 
    ORDER BY p.fecha_pedido DESC");

$pedidos = [];
if ($consulta) {
    while ($fila = $consulta->fetch_assoc()) {
        $pedidos[] = $fila;
    }
}

// Obtener estadísticas
$stats = [];
$stats_query = $conexion->query("
    SELECT 
        COUNT(*) as total_pedidos,
        SUM(total) as ingresos_totales,
        SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
        SUM(CASE WHEN estado = 'procesando' THEN 1 ELSE 0 END) as procesando,
        SUM(CASE WHEN estado = 'enviado' THEN 1 ELSE 0 END) as enviadas,
        SUM(CASE WHEN estado = 'entregado' THEN 1 ELSE 0 END) as entregadas
    FROM pedidos");

if ($stats_query) {
    $stats = $stats_query->fetch_assoc();
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos - Mi Lugar Pet</title>
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
                <a href="dashboard.php" class="menu-item">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="usuarios.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    Usuarios Clientes
                </a>
                <a href="usuarios-mysql.php" class="menu-item">
                    <i class="fas fa-database"></i>
                    Usuarios MySQL
                </a>
                <a href="productos.php" class="menu-item">
                    <i class="fas fa-box"></i>
                    Productos
                </a>
                <a href="consultas.php" class="menu-item">
                    <i class="fas fa-calendar-alt"></i>
                    Consultas
                </a>
                <a href="ventas.php" class="menu-item active">
                    <i class="fas fa-shopping-cart"></i>
                    Pedidos
                </a>
            </nav>
        </aside>
        <main class="admin-main">
            <div class="admin-content">
                <div class="page-header">
                    <h1>Gestión de Pedidos</h1>
                    <p>Administra los pedidos de los clientes</p>
                </div>

                <?php if ($message): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_pedidos'] ?? 0; ?></h3>
                            <p>Total Pedidos</p>
                        </div>
                    </div>
                    <div class="stat-card revenue">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Bs.<?php echo number_format($stats['ingresos_totales'] ?? 0, 2); ?></h3>
                            <p>Ingresos Totales</p>
                        </div>
                    </div>
                    <div class="stat-card pending">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['pendientes'] ?? 0; ?></h3>
                            <p>Pendientes</p>
                        </div>
                    </div>
                    <div class="stat-card completed">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['entregadas'] ?? 0; ?></h3>
                            <p>Entregadas</p>
                        </div>
                    </div>
                </div>

                <div class="table-card">
                    <div class="card-header">
                        <h3>Pedidos Registrados (<?php echo count($pedidos); ?>)</h3>
                        <div class="header-actions">
                            <button onclick="filterPedidos('pendiente')" class="filter-btn">
                                <i class="fas fa-clock"></i> Pendientes
                            </button>
                            <button onclick="filterPedidos('procesando')" class="filter-btn">
                                <i class="fas fa-cogs"></i> Procesando
                            </button>
                            <button onclick="filterPedidos('enviado')" class="filter-btn">
                                <i class="fas fa-truck"></i> Enviados
                            </button>
                            <button onclick="filterPedidos('entregado')" class="filter-btn">
                                <i class="fas fa-check-circle"></i> Entregados
                            </button>
                            <button onclick="filterPedidos('all')" class="filter-btn active">
                                <i class="fas fa-list"></i> Todos
                            </button>
                        </div>
                    </div>
                                        
                    <?php if (empty($pedidos)): ?>
                        <div class="no-data">
                            <i class="fas fa-shopping-cart"></i>
                            <h4>No hay pedidos registrados</h4>
                            <p>Los pedidos aparecerán aquí cuando los clientes realicen compras</p>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Total</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th>Contacto</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pedidos as $pedido): ?>
                                        <tr class="pedido-row" data-estado="<?php echo $pedido['estado']; ?>">
                                            <td>#<?php echo str_pad($pedido['id_pedido'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td>
                                                <div class="client-info">
                                                    <div class="client-details">
                                                        <strong><?php echo htmlspecialchars($pedido['nombre_cliente']); ?></strong>
                                                        <small><?php echo htmlspecialchars($pedido['email_cliente']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="total-amount">Bs.<?php echo number_format($pedido['total'], 2); ?></span>
                                            </td>
                                            <td>
                                                <div class="date-info">
                                                    <strong><?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?></strong>
                                                    <small><?php echo date('H:i', strtotime($pedido['fecha_pedido'])); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="status-badge <?php echo $pedido['estado']; ?>">
                                                    <?php echo ucfirst($pedido['estado']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="contact-info">
                                                    <?php if ($pedido['telefono_cliente']): ?>
                                                        <small><i class="fas fa-phone"></i> <?php echo htmlspecialchars($pedido['telefono_cliente']); ?></small>
                                                    <?php endif; ?>
                                                    <?php if ($pedido['direccion_envio']): ?>
                                                        <small><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars(substr($pedido['direccion_envio'], 0, 30)) . '...'; ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button onclick="showPedidoDetails(<?php echo $pedido['id_pedido']; ?>)"
                                                             class="btn-action btn-info" title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                                                                        
                                                    <div class="status-dropdown">
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="pedido_id" value="<?php echo $pedido['id_pedido']; ?>">
                                                            <select name="nuevo_estado" onchange="this.form.submit()" class="status-select">
                                                                <option value="">Cambiar estado</option>
                                                                <option value="pendiente" <?php echo $pedido['estado'] === 'pendiente' ? 'disabled' : ''; ?>>Pendiente</option>
                                                                <option value="procesando" <?php echo $pedido['estado'] === 'procesando' ? 'disabled' : ''; ?>>Procesando</option>
                                                                <option value="enviado" <?php echo $pedido['estado'] === 'enviado' ? 'disabled' : ''; ?>>Enviado</option>
                                                                <option value="entregado" <?php echo $pedido['estado'] === 'entregado' ? 'disabled' : ''; ?>>Entregado</option>
                                                                <option value="cancelado" <?php echo $pedido['estado'] === 'cancelado' ? 'disabled' : ''; ?>>Cancelado</option>
                                                            </select>
                                                            <input type="hidden" name="update_status" value="1">
                                                        </form>
                                                    </div>
                                                                                                        
                                                    <form method="POST" style="display: inline;" 
                                                          onsubmit="return confirm('¿Estás seguro de eliminar este pedido?')">
                                                        <input type="hidden" name="pedido_id" value="<?php echo $pedido['id_pedido']; ?>">
                                                        <button type="submit" name="delete_pedido" class="btn-action btn-danger" title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <div id="pedidoModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Detalles del Pedido</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body" id="modalBody">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Cargando detalles...</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Todos los estilos existentes se mantienen igual... */
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
        
        .page-header h1 {
            color: var(--primary-teal);
            margin-bottom: 0.5rem;
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .stat-card.revenue {
            border-left: 4px solid #28a745;
        }
        
        .stat-card.pending {
            border-left: 4px solid #ffc107;
        }
        
        .stat-card.completed {
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
        
        .table-card {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .card-header h3 {
            color: var(--primary-teal);
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            background: var(--light-gray);
            color: var(--text-gray);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .filter-btn.active,
        .filter-btn:hover {
            background: var(--primary-yellow);
            color: var(--primary-teal);
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th,
        .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        
        .data-table th {
            background: var(--light-gray);
            color: var(--primary-teal);
            font-weight: 600;
        }
        
        .client-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .client-details strong {
            display: block;
            color: var(--dark-gray);
            margin-bottom: 0.25rem;
        }
        
        .client-details small {
            display: block;
            color: var(--text-gray);
            font-size: 0.8rem;
        }
        
        .total-amount {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-orange);
        }
        
        .date-info strong {
            display: block;
            color: var(--dark-gray);
        }
        
        .date-info small {
            color: var(--text-gray);
        }
        
        .contact-info small {
            display: block;
            color: var(--text-gray);
            font-size: 0.8rem;
            margin-bottom: 0.25rem;
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
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
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .btn-action {
            border: none;
            padding: 0.5rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--white);
        }
        
        .btn-info {
            background: #17a2b8;
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-action:hover {
            transform: scale(1.1);
        }
        
        .status-select {
            padding: 0.5rem;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 0.8rem;
            background: var(--white);
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
        }
        
        .status-select:focus {
            outline: none;
            border-color: var(--primary-teal);
        }
        
        .modal {
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: var(--white);
            margin: 2% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            overflow-y: auto;
        }
        
        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background: var(--white);
            z-index: 1;
        }
        
        .modal-header h3 {
            color: var(--primary-teal);
            margin: 0;
        }
        
        .close {
            color: var(--text-gray);
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: var(--primary-orange);
        }
        
        .modal-body {
            padding: 1.5rem;
        }

        .loading-spinner {
            text-align: center;
            padding: 2rem;
            color: var(--text-gray);
        }

        .loading-spinner i {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--primary-teal);
        }

        .pedido-info {
            background: var(--light-gray);
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .pedido-info h4 {
            color: var(--primary-teal);
            margin-bottom: 1rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-weight: bold;
            color: var(--dark-gray);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .info-value {
            color: var(--text-gray);
        }

        .detalles-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .detalles-table th,
        .detalles-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .detalles-table th {
            background: var(--primary-teal);
            color: var(--white);
            font-weight: 600;
        }

        .producto-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .producto-detalles h5 {
            margin: 0 0 0.25rem 0;
            color: var(--dark-gray);
        }

        .producto-categoria {
            font-size: 0.8rem;
            color: var(--text-gray);
            text-transform: uppercase;
        }

        .cantidad-badge {
            background: var(--primary-blue);
            color: var(--white);
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-weight: bold;
            text-align: center;
        }

        .precio-unitario {
            font-weight: bold;
            color: var(--primary-orange);
        }

        .subtotal {
            font-weight: bold;
            color: var(--primary-teal);
            font-size: 1.1rem;
        }

        .total-pedido {
            background: var(--primary-teal);
            color: var(--white);
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            margin-top: 1rem;
        }

        .total-pedido h4 {
            margin: 0;
            font-size: 1.5rem;
        }

        .error-message-modal {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
        }
        
        .no-data {
            text-align: center;
            padding: 3rem;
            color: var(--text-gray);
        }
        
        .no-data i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--primary-teal);
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }
            .admin-main {
                margin-left: 0;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .action-buttons {
                flex-direction: row;
            }
            .modal-content {
                width: 95%;
                margin: 5% auto;
            }
            .card-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
            .detalles-table {
                font-size: 0.9rem;
            }
        }
    </style>

    <script>
        function showPedidoDetails(pedidoId) {
            // Mostrar modal con loading
            document.getElementById('modalTitle').textContent = `Detalles del Pedido #${String(pedidoId).padStart(6, '0')}`;
            document.getElementById('modalBody').innerHTML = `
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Cargando detalles del pedido #${pedidoId}...</p>
                </div>
            `;
            document.getElementById('pedidoModal').style.display = 'block';

            // Realizar petición AJAX al mismo archivo
            fetch(`?ajax=pedido_details&pedido_id=${pedidoId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayPedidoDetails(data.pedido, data.detalles);
                    } else {
                        document.getElementById('modalBody').innerHTML = `
                            <div class="error-message-modal">
                                <i class="fas fa-exclamation-triangle"></i>
                                <p>Error: ${data.message}</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('modalBody').innerHTML = `
                        <div class="error-message-modal">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>Error al cargar los detalles del pedido. Por favor, intenta nuevamente.</p>
                        </div>
                    `;
                });
        }

        function displayPedidoDetails(pedido, detalles) {
            let totalPedido = 0;
            
            let detallesHtml = '';
            if (detalles.length > 0) {
                detalles.forEach(detalle => {
                    const subtotal = parseFloat(detalle.cantidad) * parseFloat(detalle.precio_unitario);
                    totalPedido += subtotal;
                    
                    detallesHtml += `
                        <tr>
                            <td>
                                <div class="producto-info">
                                    <div class="producto-detalles">
                                        <h5>${detalle.nombre_producto}</h5>
                                        <span class="producto-categoria">${detalle.categoria || 'Sin categoría'}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="cantidad-badge">${detalle.cantidad}</span>
                            </td>
                            <td>
                                <span class="precio-unitario">Bs.${parseFloat(detalle.precio_unitario).toFixed(2)}</span>
                            </td>
                            <td>
                                <span class="subtotal">Bs.${subtotal.toFixed(2)}</span>
                            </td>
                        </tr>
                    `;
                });
            } else {
                detallesHtml = `
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-gray);">
                            <i class="fas fa-box-open" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                            No hay productos en este pedido
                        </td>
                    </tr>
                `;
            }

            const modalContent = `
                <div class="pedido-info">
                    <h4><i class="fas fa-info-circle"></i> Información del Pedido</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Cliente:</span>
                            <span class="info-value">${pedido.cliente_nombre}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value">${pedido.cliente_email}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Teléfono:</span>
                            <span class="info-value">${pedido.cliente_telefono || 'No especificado'}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Fecha del Pedido:</span>
                            <span class="info-value">${new Date(pedido.fecha_pedido).toLocaleString('es-ES')}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Estado:</span>
                            <span class="info-value">
                                <span class="status-badge ${pedido.estado}">${pedido.estado.charAt(0).toUpperCase() + pedido.estado.slice(1)}</span>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Dirección de Envío:</span>
                            <span class="info-value">${pedido.direccion_envio || 'No especificada'}</span>
                        </div>
                    </div>
                </div>

                <h4><i class="fas fa-shopping-cart"></i> Productos del Pedido</h4>
                <table class="detalles-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${detallesHtml}
                    </tbody>
                </table>

                <div class="total-pedido">
                    <h4>Total del Pedido: Bs.${parseFloat(pedido.total).toFixed(2)}</h4>
                </div>
            `;

            document.getElementById('modalBody').innerHTML = modalContent;
        }

        function closeModal() {
            document.getElementById('pedidoModal').style.display = 'none';
        }

        function filterPedidos(estado) {
            const rows = document.querySelectorAll('.pedido-row');
            const buttons = document.querySelectorAll('.filter-btn');
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            rows.forEach(row => {
                if (estado === 'all' || row.dataset.estado === estado) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        window.onclick = function(event) {
            const modal = document.getElementById('pedidoModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>