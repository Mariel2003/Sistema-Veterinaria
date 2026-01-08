<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Verificar que el usuario esté logueado y sea cliente
requireClient();

$message = '';
$error = '';

// Obtener las consultas del usuario actual
$consultas = [];
try {
    $db = new Database();
    
    $db->query('SELECT * FROM consultas WHERE id_usuario = :id_usuario ORDER BY fecha DESC');
    $db->bind(':id_usuario', getCurrentUserId());
    $consultas = $db->resultset();
    
} catch (Exception $e) {
    $error = "Error al obtener las consultas: " . $e->getMessage();
}

// Procesar cancelación de consulta
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancelar_consulta'])) {
    $consulta_id = $_POST['consulta_id'];
    
    try {
        $db = new Database();
        
        // Verificar que la consulta pertenezca al usuario actual y esté pendiente
        $db->query('SELECT * FROM consultas WHERE id_consulta = :id AND id_usuario = :id_usuario AND estado = "pendiente"');
        $db->bind(':id', $consulta_id);
        $db->bind(':id_usuario', getCurrentUserId());
        $consulta = $db->single();
        
        if ($consulta) {
            // Actualizar el estado a cancelada
            $db->query('UPDATE consultas SET estado = "cancelada" WHERE id_consulta = :id');
            $db->bind(':id', $consulta_id);
            
            if ($db->execute()) {
                $message = "Consulta cancelada exitosamente.";
                // Recargar las consultas
                $db->query('SELECT * FROM consultas WHERE id_usuario = :id_usuario ORDER BY fecha DESC');
                $db->bind(':id_usuario', getCurrentUserId());
                $consultas = $db->resultset();
            } else {
                $error = "Error al cancelar la consulta.";
            }
        } else {
            $error = "No se puede cancelar esta consulta.";
        }
        
    } catch (Exception $e) {
        $error = "Error al cancelar la consulta: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Consultas - Mi Lugar Pet</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .consultas-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            min-height: calc(100vh - 200px);
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem 0;
            background: linear-gradient(135deg, var(--primary-teal) 0%, var(--primary-blue) 100%);
            color: var(--white);
            border-radius: 15px;
            margin-top: 2rem;
        }

        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
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
            border-left: 4px solid #28a745;
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
            border-left: 4px solid #dc3545;
        }

        .consultas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .consulta-card {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            border-left: 5px solid var(--primary-teal);
        }

        .consulta-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .consulta-card.pendiente {
            border-left-color: #ffc107;
        }

        .consulta-card.finalizada {
            border-left-color: #28a745;
        }

        .consulta-card.cancelada {
            border-left-color: #dc3545;
            opacity: 0.8;
        }

        .consulta-header {
            padding: 1.5rem;
            background: var(--light-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .consulta-id {
            font-weight: bold;
            color: var(--primary-teal);
            font-size: 1.1rem;
        }

        .consulta-estado {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .consulta-estado.pendiente {
            background: #fff3cd;
            color: #856404;
        }

        .consulta-estado.finalizada {
            background: #d4edda;
            color: #155724;
        }

        .consulta-estado.cancelada {
            background: #f8d7da;
            color: #721c24;
        }

        .consulta-content {
            padding: 1.5rem;
        }

        .consulta-info {
            margin-bottom: 1rem;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.75rem;
            padding: 0.5rem;
            background: var(--light-gray);
            border-radius: 8px;
        }

        .info-icon {
            color: var(--primary-teal);
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .info-label {
            font-weight: 600;
            color: var(--dark-gray);
            min-width: 80px;
        }

        .info-value {
            color: var(--text-gray);
            flex: 1;
        }

        .consulta-image {
            margin-bottom: 1rem;
            text-align: center;
        }

        .pet-photo {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            object-fit: cover;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .pet-photo:hover {
            transform: scale(1.05);
        }

        .consulta-motivo {
            background: var(--white);
            border: 2px solid var(--light-gray);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .consulta-motivo h4 {
            color: var(--primary-teal);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .consulta-motivo p {
            color: var(--text-gray);
            line-height: 1.6;
            margin: 0;
        }

        .consulta-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            padding-top: 1rem;
            border-top: 1px solid var(--light-gray);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary-teal);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-blue);
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #dc3545;
            color: var(--white);
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--text-gray);
            color: var(--white);
        }

        .btn-secondary:hover {
            background: var(--dark-gray);
        }

        .no-consultas {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .no-consultas i {
            font-size: 4rem;
            color: var(--primary-teal);
            margin-bottom: 1rem;
        }

        .no-consultas h3 {
            color: var(--dark-gray);
            margin-bottom: 1rem;
        }

        .no-consultas p {
            color: var(--text-gray);
            margin-bottom: 2rem;
        }

        .filter-tabs {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .filter-tab {
            padding: 0.75rem 1.5rem;
            background: var(--white);
            border: 2px solid var(--light-gray);
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-tab.active,
        .filter-tab:hover {
            background: var(--primary-teal);
            color: var(--white);
            border-color: var(--primary-teal);
        }

        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-teal);
            display: block;
        }

        .stat-label {
            color: var(--text-gray);
            font-size: 0.9rem;
            text-transform: uppercase;
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

        /* Dropdown Styles */
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-btn {
            background: none;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .dropdown-btn:hover {
            color: var(--primary-teal);
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: var(--white);
            min-width: 200px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            border-radius: 8px;
            z-index: 1000;
            top: 100%;
            left: 0;
            overflow: hidden;
        }

        .dropdown-content.show {
            display: block;
        }

        .dropdown-item {
            color: var(--dark-gray);
            padding: 12px 16px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: background-color 0.3s ease;
        }

        .dropdown-item:hover {
            background-color: var(--light-gray);
            color: var(--primary-teal);
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        @media (max-width: 768px) {
            .consultas-container {
                padding: 1rem;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .consultas-grid {
                grid-template-columns: 1fr;
            }

            .consulta-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .consulta-actions {
                flex-direction: column;
            }

            .filter-tabs {
                flex-direction: column;
                align-items: center;
            }

            .stats-summary {
                grid-template-columns: 1fr;
            }

            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .info-label {
                min-width: auto;
            }

            .dropdown-content {
                position: fixed;
                left: 50%;
                transform: translateX(-50%);
                width: 90%;
                max-width: 300px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
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
                    
                    <!-- Dropdown de Consultas -->
                    <div class="dropdown">
                        <button class="nav-link dropdown-btn">
                            Consultas <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-content">
                            <a href="consultas.php" class="dropdown-item">
                                <i class="fas fa-plus-circle"></i>
                                Registrar Consulta
                            </a>
                            <a href="ver_consultas.php" class="dropdown-item">
                                <i class="fas fa-list-alt"></i>
                                Ver Mis Consultas
                            </a>
                        </div>
                    </div>
                    
                    <div class="user-menu">
                        <span class="user-welcome">Hola, <?php echo getCurrentUserName(); ?></span>
                        <a href="logout.php" class="nav-link logout-btn">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <div class="consultas-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-list-alt"></i> Mis Consultas</h1>
            <p>Revisa el estado de todas tus consultas veterinarias</p>
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

        <?php if (empty($consultas)): ?>
            <!-- No hay consultas -->
            <div class="no-consultas">
                <i class="fas fa-calendar-times"></i>
                <h3>No tienes consultas registradas</h3>
                <p>¡Agenda tu primera consulta veterinaria para el cuidado de tu mascota!</p>
                <a href="consultas.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i>
                    Agendar Primera Consulta
                </a>
            </div>
        <?php else: ?>
            <!-- Estadísticas -->
            <?php
            $stats = [
                'total' => count($consultas),
                'pendientes' => 0,
                'finalizadas' => 0,
                'canceladas' => 0
            ];
            
            foreach ($consultas as $consulta) {
                switch ($consulta['estado']) {
                    case 'pendiente':
                        $stats['pendientes']++;
                        break;
                    case 'finalizada':
                        $stats['finalizadas']++;
                        break;
                    case 'cancelada':
                        $stats['canceladas']++;
                        break;
                }
            }
            ?>
            
            <div class="stats-summary">
                <div class="stat-card">
                    <span class="stat-number"><?php echo $stats['total']; ?></span>
                    <span class="stat-label">Total</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $stats['pendientes']; ?></span>
                    <span class="stat-label">Pendientes</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $stats['finalizadas']; ?></span>
                    <span class="stat-label">Finalizadas</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $stats['canceladas']; ?></span>
                    <span class="stat-label">Canceladas</span>
                </div>
            </div>

            <!-- Filtros -->
            <div class="filter-tabs">
                <div class="filter-tab active" onclick="filterConsultas('all')">
                    <i class="fas fa-list"></i>
                    Todas
                </div>
                <div class="filter-tab" onclick="filterConsultas('pendiente')">
                    <i class="fas fa-clock"></i>
                    Pendientes
                </div>
                <div class="filter-tab" onclick="filterConsultas('finalizada')">
                    <i class="fas fa-check-circle"></i>
                    Finalizadas
                </div>
                <div class="filter-tab" onclick="filterConsultas('cancelada')">
                    <i class="fas fa-times-circle"></i>
                    Canceladas
                </div>
            </div>

            <!-- Lista de Consultas -->
            <div class="consultas-grid">
                <?php foreach ($consultas as $consulta): ?>
                    <div class="consulta-card <?php echo $consulta['estado']; ?>" data-estado="<?php echo $consulta['estado']; ?>">
                        <div class="consulta-header">
                            <div class="consulta-id">
                                Consulta #<?php echo str_pad($consulta['id_consulta'], 4, '0', STR_PAD_LEFT); ?>
                            </div>
                            <div class="consulta-estado <?php echo $consulta['estado']; ?>">
                                <?php echo ucfirst($consulta['estado']); ?>
                            </div>
                        </div>
                        
                        <div class="consulta-content">
                            <div class="consulta-info">
                                <div class="info-row">
                                    <i class="fas fa-calendar info-icon"></i>
                                    <span class="info-label">Fecha:</span>
                                    <span class="info-value">
                                        <?php 
                                        // Formatear la fecha dependiendo del formato almacenado
                                        $fecha = $consulta['fecha'];
                                        if (strpos($fecha, ' ') !== false) {
                                            // Si tiene fecha y hora juntas
                                            echo date('d/m/Y H:i', strtotime($fecha));
                                        } else {
                                            // Si solo tiene fecha
                                            echo date('d/m/Y', strtotime($fecha));
                                        }
                                        ?>
                                    </span>
                                </div>
                                
                                <div class="info-row">
                                    <i class="fas fa-user info-icon"></i>
                                    <span class="info-label">Usuario:</span>
                                    <span class="info-value"><?php echo getCurrentUserName(); ?></span>
                                </div>
                            </div>
                            
                            <?php if (!empty($consulta['foto'])): ?>
                                <div class="consulta-image">
                                    <img src="uploads/<?php echo htmlspecialchars($consulta['foto']); ?>" 
                                         alt="Foto de la mascota" 
                                         class="pet-photo"
                                         onclick="openModal(this.src)">
                                </div>
                            <?php endif; ?>
                            
                            <div class="consulta-motivo">
                                <h4><i class="fas fa-comment-medical"></i> Motivo de la Consulta</h4>
                                <p><?php echo nl2br(htmlspecialchars($consulta['motivo'])); ?></p>
                            </div>
                            
                            <div class="consulta-actions">
                                <?php if ($consulta['estado'] === 'pendiente'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="consulta_id" value="<?php echo $consulta['id_consulta']; ?>">
                                        <button type="submit" name="cancelar_consulta" class="btn btn-danger"
                                                onclick="return confirm('¿Estás seguro de que deseas cancelar esta consulta?')">
                                            <i class="fas fa-times"></i>
                                            Cancelar
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <a href="consultas.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i>
                                    Nueva Consulta
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal para mostrar imagen completa -->
    <div id="imageModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <i class="fas fa-paw"></i>
                        <span>Mi Lugar Pet</span>
                    </div>
                    <p>Cuidando a tus mascotas con amor y profesionalismo.</p>
                </div>
                <div class="footer-section">
                    <h3>Contáctanos</h3>
                    <div class="contact-info">
                        <p><i class="fas fa-phone"></i> +1 234 567 8900</p>
                        <p><i class="fas fa-envelope"></i> info@milugarpet.com</p>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Mi Lugar Pet. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        // Función para filtrar consultas
        function filterConsultas(estado) {
            const consultas = document.querySelectorAll('.consulta-card');
            const tabs = document.querySelectorAll('.filter-tab');
            
            // Actualizar tabs activos
            tabs.forEach(tab => tab.classList.remove('active'));
            event.target.classList.add('active');
            
            // Filtrar consultas
            consultas.forEach(consulta => {
                if (estado === 'all' || consulta.dataset.estado === estado) {
                    consulta.style.display = 'block';
                } else {
                    consulta.style.display = 'none';
                }
            });
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

        // JavaScript para el dropdown
        document.addEventListener('DOMContentLoaded', function() {
            const dropdown = document.querySelector('.dropdown');
            const dropdownBtn = document.querySelector('.dropdown-btn');
            const dropdownContent = document.querySelector('.dropdown-content');

            if (window.innerWidth <= 768) {
                dropdownBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    dropdownContent.classList.toggle('show');
                });

                document.addEventListener('click', function(e) {
                    if (!dropdown.contains(e.target)) {
                        dropdownContent.classList.remove('show');
                    }
                });
            }
        });
    </script>
</body>
</html>