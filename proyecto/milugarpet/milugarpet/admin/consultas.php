<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conexion = getMySQLConnection();
    
    if (isset($_POST['update_status'])) {
        $consulta_id = $_POST['consulta_id'];
        $nuevo_estado = $_POST['nuevo_estado'];
        
        $stmt = $conexion->prepare("UPDATE consultas SET estado = ? WHERE id_consulta = ?");
        $stmt->bind_param("si", $nuevo_estado, $consulta_id);
        
        if ($stmt->execute()) {
            $message = "✅ Estado de consulta actualizado a: " . ucfirst($nuevo_estado);
        } else {
            $error = "Error al actualizar estado: " . $conexion->error;
        }
        $stmt->close();
    }
    
    if (isset($_POST['delete_consulta'])) {
        $consulta_id = $_POST['consulta_id'];
        
        // Obtener la foto antes de eliminar para borrarla del servidor
        $stmt = $conexion->prepare("SELECT foto FROM consultas WHERE id_consulta = ?");
        $stmt->bind_param("i", $consulta_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $consulta_data = $result->fetch_assoc();
        $stmt->close();
        
        $stmt = $conexion->prepare("DELETE FROM consultas WHERE id_consulta = ?");
        $stmt->bind_param("i", $consulta_id);
        
        if ($stmt->execute()) {
            // Eliminar la imagen del servidor si existe
            if (!empty($consulta_data['foto']) && file_exists('../uploads/' . $consulta_data['foto'])) {
                unlink('../uploads/' . $consulta_data['foto']);
            }
            $message = "✅ Consulta eliminada exitosamente.";
        } else {
            $error = "Error al eliminar consulta: " . $conexion->error;
        }
        $stmt->close();
    }
    
    $conexion->close();
}

$conexion = getMySQLConnection();
$consulta = $conexion->query("
    SELECT c.*, u.nombre as cliente_nombre, u.email as cliente_email, u.telefono as cliente_telefono
    FROM consultas c 
    JOIN usuarios u ON c.id_usuario = u.id_usuario 
    ORDER BY c.fecha DESC
");
$consultas = [];
if ($consulta) {
    while ($fila = $consulta->fetch_assoc()) {
        $consultas[] = $fila;
    }
}

// Obtener estadísticas
$stats = [];
$stats_query = $conexion->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
        SUM(CASE WHEN estado = 'finalizada' THEN 1 ELSE 0 END) as finalizadas,
        SUM(CASE WHEN estado = 'cancelada' THEN 1 ELSE 0 END) as canceladas
    FROM consultas
");
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
    <title>Gestión de Consultas - Mi Lugar Pet</title>
    <link rel="stylesheet" href="../styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Admin Header -->
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
        <!-- Sidebar -->
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
                <a href="consultas.php" class="menu-item active">
                    <i class="fas fa-calendar-alt"></i>
                    Consultas
                </a>
                <a href="ventas.php" class="menu-item">
                    <i class="fas fa-shopping-cart"></i>
                    Ventas
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-content">
                <div class="page-header">
                    <h1>Gestión de Consultas</h1>
                    <p>Administra las consultas veterinarias</p>
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

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total'] ?? 0; ?></h3>
                            <p>Total Consultas</p>
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
                            <h3><?php echo $stats['finalizadas'] ?? 0; ?></h3>
                            <p>Finalizadas</p>
                        </div>
                    </div>

                    <div class="stat-card cancelled">
                        <div class="stat-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $stats['canceladas'] ?? 0; ?></h3>
                            <p>Canceladas</p>
                        </div>
                    </div>
                </div>

                <!-- Consultations Table -->
                <div class="table-card">
                    <div class="card-header">
                        <h3>Consultas Registradas (<?php echo count($consultas); ?>)</h3>
                    </div>
                    
                    <?php if (empty($consultas)): ?>
                        <div class="no-data">
                            <i class="fas fa-calendar-alt"></i>
                            <h4>No hay consultas registradas</h4>
                            <p>Las consultas aparecerán aquí cuando los clientes las agenden</p>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Foto</th>
                                        <th>Motivo</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($consultas as $consulta): ?>
                                        <tr>
                                            <td><?php echo $consulta['id_consulta']; ?></td>
                                            <td>
                                                <div class="client-info">
                                                    <div class="client-details">
                                                        <strong><?php echo htmlspecialchars($consulta['cliente_nombre']); ?></strong>
                                                        <small><?php echo htmlspecialchars($consulta['cliente_email']); ?></small>
                                                        <?php if ($consulta['cliente_telefono']): ?>
                                                            <small><i class="fas fa-phone"></i> <?php echo htmlspecialchars($consulta['cliente_telefono']); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (!empty($consulta['foto'])): ?>
                                                    <div class="photo-container">
                                                        <img src="../uploads/<?php echo htmlspecialchars($consulta['foto']); ?>" 
                                                             alt="Foto de la mascota" 
                                                             class="pet-photo-thumb"
                                                             onclick="openModal('../uploads/<?php echo htmlspecialchars($consulta['foto']); ?>')">
                                                    </div>
                                                <?php else: ?>
                                                    <span class="no-photo">Sin foto</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="motivo-preview">
                                                    <?php echo nl2br(htmlspecialchars(substr($consulta['motivo'], 0, 100))); ?>
                                                    <?php if (strlen($consulta['motivo']) > 100): ?>
                                                        <span class="read-more" onclick="showFullMotivo(<?php echo $consulta['id_consulta']; ?>)">... Ver más</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div id="motivo-full-<?php echo $consulta['id_consulta']; ?>" class="motivo-full" style="display: none;">
                                                    <?php echo nl2br(htmlspecialchars($consulta['motivo'])); ?>
                                                    <span class="read-less" onclick="hideFullMotivo(<?php echo $consulta['id_consulta']; ?>)">Ver menos</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="date-info">
                                                    <strong><?php echo date('d/m/Y', strtotime($consulta['fecha'])); ?></strong>
                                                    <small><?php echo date('H:i', strtotime($consulta['fecha'])); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="status-badge <?php echo $consulta['estado']; ?>">
                                                    <?php echo ucfirst($consulta['estado']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <!-- Change Status -->
                                                    <div class="status-dropdown">
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="consulta_id" value="<?php echo $consulta['id_consulta']; ?>">
                                                            <select name="nuevo_estado" onchange="this.form.submit()" class="status-select">
                                                                <option value="">Cambiar estado</option>
                                                                <option value="pendiente" <?php echo $consulta['estado'] === 'pendiente' ? 'disabled' : ''; ?>>Pendiente</option>
                                                                <option value="finalizada" <?php echo $consulta['estado'] === 'finalizada' ? 'disabled' : ''; ?>>Finalizada</option>
                                                                <option value="cancelada" <?php echo $consulta['estado'] === 'cancelada' ? 'disabled' : ''; ?>>Cancelada</option>
                                                            </select>
                                                            <input type="hidden" name="update_status" value="1">
                                                        </form>
                                                    </div>
                                                    
                                                    <!-- Delete -->
                                                    <form method="POST" style="display: inline;" 
                                                          onsubmit="return confirm('¿Estás seguro de eliminar esta consulta?')">
                                                        <input type="hidden" name="consulta_id" value="<?php echo $consulta['id_consulta']; ?>">
                                                        <button type="submit" name="delete_consulta" class="btn-action btn-danger" title="Eliminar">
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

    <!-- Modal para mostrar imagen completa -->
    <div id="imageModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="modalImage">
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
        }

        .admin-nav {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .logout-btn {
            background: var(--primary-orange);
            color: var(--white);
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
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

        .stat-card.pending {
            border-left: 4px solid #ffc107;
        }

        .stat-card.completed {
            border-left: 4px solid #28a745;
        }

        .stat-card.cancelled {
            border-left: 4px solid #dc3545;
        }

        .stat-icon {
            font-size: 2.5rem;
            color: var(--primary-teal);
        }

        .stat-info h3 {
            font-size: 2rem;
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
        }

        .card-header h3 {
            color: var(--primary-teal);
            margin: 0;
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

        .photo-container {
            text-align: center;
        }

        .pet-photo-thumb {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .pet-photo-thumb:hover {
            transform: scale(1.1);
        }

        .no-photo {
            color: var(--text-gray);
            font-style: italic;
            font-size: 0.9rem;
        }

        .motivo-preview {
            max-width: 300px;
            line-height: 1.4;
        }

        .read-more, .read-less {
            color: var(--primary-teal);
            cursor: pointer;
            font-weight: 500;
        }

        .read-more:hover, .read-less:hover {
            color: var(--primary-orange);
        }

        .motivo-full {
            max-width: 300px;
            line-height: 1.4;
        }

        .date-info strong {
            display: block;
            color: var(--dark-gray);
        }

        .date-info small {
            color: var(--text-gray);
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

        .status-badge.finalizada {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.cancelada {
            background: #f8d7da;
            color: #721c24;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .status-select {
            padding: 0.5rem;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 0.8rem;
            background: var(--white);
            color: var(--dark-gray);
        }

        .status-select:focus {
            outline: none;
            border-color: var(--primary-teal);
        }

        .btn-action {
            border: none;
            padding: 0.5rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--white);
        }

        .btn-danger {
            background: #dc3545;
        }

        .btn-action:hover {
            transform: scale(1.1);
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

        /* Modal para imagen */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
        }

        .modal-content {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 700px;
            max-height: 80%;
            object-fit: contain;
        }

        .close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #bbb;
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
        }
    </style>

    <script>
        function showFullMotivo(id) {
            document.querySelector(`#motivo-full-${id}`).style.display = 'block';
            document.querySelector(`#motivo-full-${id}`).previousElementSibling.style.display = 'none';
        }

        function hideFullMotivo(id) {
            document.querySelector(`#motivo-full-${id}`).style.display = 'none';
            document.querySelector(`#motivo-full-${id}`).previousElementSibling.style.display = 'block';
        }

        // Funciones para el modal de imagen
        function openModal(src) {
            document.getElementById('imageModal').style.display = 'block';
            document.getElementById('modalImage').src = src;
        }

        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera de la imagen
        window.onclick = function(event) {
            const modal = document.getElementById('imageModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>