<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireEmployee();

$conexion = getMySQLConnection();
$db = new Database();

$db->query('SELECT c.*, u.nombre as cliente_nombre, u.email as cliente_email 
           FROM consultas c
           JOIN usuarios u ON c.id_usuario = u.id_usuario
           ORDER BY c.fecha DESC');
$consultas = $db->resultset();

$db->query('SELECT COUNT(*) as total FROM consultas WHERE estado = "pendiente"');
$consultas_pendientes = $db->single()['total'];

$db->query('SELECT COUNT(*) as total FROM consultas WHERE estado = "finalizada"');
$consultas_finalizadas = $db->single()['total'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultas - Mi Lugar Pet</title>
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
                <a href="consultas.php" class="menu-item active">
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
                    <h1>Gestión de Consultas</h1>
                    <p>Administra las consultas de los clientes</p>
                </div>

                <div class="stats-grid-consultas">
                    <div class="stat-card pending">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $consultas_pendientes; ?></h3>
                            <p>Consultas Pendientes</p>
                        </div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $consultas_finalizadas; ?></h3>
                            <p>Consultas Finalizadas</p>
                        </div>
                    </div>
                    <div class="stat-card total">
                        <div class="stat-icon">
                            <i class="fas fa-list"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo count($consultas); ?></h3>
                            <p>Total de Consultas</p>
                        </div>
                    </div>
                </div>

                <div class="table-card">
                    <div class="card-header">
                        <h3>Lista de Consultas</h3>
                        <div class="header-actions">
                            <button class="filter-btn active" onclick="filterConsultas('all')">
                                <i class="fas fa-list"></i>
                                Todas
                            </button>
                            <button class="filter-btn" onclick="filterConsultas('pendiente')">
                                <i class="fas fa-clock"></i>
                                Pendientes
                            </button>
                            <button class="filter-btn" onclick="filterConsultas('finalizada')">
                                <i class="fas fa-check"></i>
                                Finalizadas
                            </button>
                        </div>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Contacto</th>
                                    <th>Foto</th>
                                    <th>Motivo</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($consultas)): ?>
                                    <tr>
                                        <td colspan="7" class="no-data">
                                            <div class="no-activity">
                                                <i class="fas fa-inbox"></i>
                                                <p>No hay consultas registradas</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($consultas as $index => $consulta): ?>
                                        <tr class="consulta-row" data-estado="<?php echo $consulta['estado']; ?>">
                                            <td>
                                                <div class="client-info">
                                                    <strong><?php echo htmlspecialchars($consulta['cliente_nombre']); ?></strong>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="contact-info">
                                                    <small><?php echo htmlspecialchars($consulta['cliente_email']); ?></small>
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
                                                    <?php 
                                                    $motivo = htmlspecialchars($consulta['motivo']);
                                                    $preview = strlen($motivo) > 50 ? substr($motivo, 0, 50) . '...' : $motivo;
                                                    ?>
                                                    <span id="preview-<?php echo $index; ?>"><?php echo $preview; ?>
                                                        <?php if (strlen($motivo) > 50): ?>
                                                            <span class="read-more" onclick="showFullMotivo(<?php echo $index; ?>)">Leer más</span>
                                                        <?php endif; ?>
                                                    </span>
                                                    <?php if (strlen($motivo) > 50): ?>
                                                        <div class="motivo-full" id="motivo-full-<?php echo $index; ?>" style="display: none;">
                                                            <?php echo $motivo; ?>
                                                            <span class="read-less" onclick="hideFullMotivo(<?php echo $index; ?>)">Leer menos</span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="date-info">
                                                    <?php echo date('d/m/Y', strtotime($consulta['fecha'])); ?>
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
                                                    <button class="btn-action view" onclick="viewConsulta(<?php echo $consulta['id_consulta']; ?>)" title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if ($consulta['estado'] == 'pendiente'): ?>
                                                        <button class="btn-action complete" onclick="completeConsulta(<?php echo $consulta['id_consulta']; ?>)" title="Marcar como finalizada">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
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

        .stats-grid-consultas {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .stat-card.pending {
            border-left: 4px solid #ffc107;
        }

        .stat-card.success {
            border-left: 4px solid #28a745;
        }

        .stat-card.total {
            border-left: 4px solid var(--primary-teal);
        }

        .stat-icon {
            font-size: 3rem;
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
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 0.5rem;
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

        .client-info strong {
            color: var(--dark-gray);
        }

        .contact-info small {
            color: var(--text-gray);
        }

        .photo-container {
            text-align: center;
        }

        .pet-photo-thumb {
            width: 50px;
            height: 50px;
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
            font-size: 0.8rem;
        }

        .motivo-preview {
            max-width: 300px;
            line-height: 1.4;
        }

        .read-more,
        .read-less {
            color: var(--primary-teal);
            cursor: pointer;
            font-weight: 500;
            margin-left: 0.5rem;
        }

        .read-more:hover,
        .read-less:hover {
            color: var(--primary-orange);
        }

        .motivo-full {
            max-width: 300px;
            line-height: 1.4;
        }

        .date-info {
            display: flex;
            flex-direction: column;
        }

        .date-info small {
            color: var(--text-gray);
            font-size: 0.8rem;
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

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            background: var(--primary-teal);
            color: var(--white);
            border: none;
            padding: 0.5rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-action.view {
            background: var(--primary-blue);
        }

        .btn-action.complete {
            background: #28a745;
        }

        .btn-action:hover {
            transform: scale(1.1);
        }

        .no-data {
            text-align: center;
            padding: 3rem;
        }

        .no-activity {
            text-align: center;
            color: var(--text-gray);
        }

        .no-activity i {
            font-size: 2rem;
            color: var(--text-gray);
            margin-bottom: 0.5rem;
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
            .employee-sidebar {
                transform: translateX(-100%);
            }

            .employee-main {
                margin-left: 0;
            }

            .card-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .stats-grid-consultas {
                grid-template-columns: 1fr;
            }

            .header-actions {
                width: 100%;
                justify-content: center;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>

    <script>
        function showFullMotivo(id) {
            document.querySelector(`#motivo-full-${id}`).style.display = 'block';
            document.querySelector(`#preview-${id}`).style.display = 'none';
        }

        function hideFullMotivo(id) {
            document.querySelector(`#motivo-full-${id}`).style.display = 'none';
            document.querySelector(`#preview-${id}`).style.display = 'block';
        }

        function filterConsultas(estado) {
            const rows = document.querySelectorAll('.consulta-row');
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

        function viewConsulta(id) {
            alert(`Ver detalles de la consulta ${id}`);
        }

        function completeConsulta(id) {
            if (confirm('¿Estás seguro de marcar esta consulta como finalizada?')) {
                alert(`Consulta ${id} marcada como finalizada`);
                location.reload();
            }
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