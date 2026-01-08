<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conexion = getMySQLConnection();
    
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        
        $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $message = "✅ Usuario eliminado exitosamente.";
        } else {
            $error = "Error al eliminar usuario: " . $conexion->error;
        }
        $stmt->close();
    }
    
    if (isset($_POST['toggle_status'])) {
        $user_id = $_POST['user_id'];
        $new_status = $_POST['new_status'];
        
        $stmt = $conexion->prepare("UPDATE usuarios SET activo = ? WHERE id_usuario = ?");
        $stmt->bind_param("ii", $new_status, $user_id);
        
        if ($stmt->execute()) {
            $status_text = $new_status ? 'activado' : 'desactivado';
            $message = "✅ Usuario $status_text exitosamente.";
        } else {
            $error = "Error al cambiar estado: " . $conexion->error;
        }
        $stmt->close();
    }
    
    $conexion->close();
}

$conexion = getMySQLConnection();
$consulta = $conexion->query("SELECT * FROM usuarios WHERE rol = 'cliente' ORDER BY fecha_registro DESC");
$usuarios = [];
if ($consulta) {
    while ($fila = $consulta->fetch_assoc()) {
        $usuarios[] = $fila;
    }
}
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Mi Lugar Pet</title>
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
                <a href="usuarios.php" class="menu-item active">
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
                <a href="ventas.php" class="menu-item">
                    <i class="fas fa-shopping-cart"></i>
                    Ventas
                </a>
            </nav>
        </aside>

        <main class="admin-main">
            <div class="admin-content">
                <div class="page-header">
                    <h1>Gestión de Usuarios Clientes</h1>
                    <p>Administra los usuarios registrados en el sistema</p>
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

                <div class="table-card">
                    <div class="card-header">
                        <h3>Usuarios Registrados (<?php echo count($usuarios); ?>)</h3>
                    </div>
                    
                    <?php if (empty($usuarios)): ?>
                        <div class="no-data">
                            <i class="fas fa-users"></i>
                            <h4>No hay usuarios registrados</h4>
                            <p>Los usuarios aparecerán aquí cuando se registren</p>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Usuario</th>
                                        <th>Email</th>
                                        <th>Teléfono</th>
                                        <th>Registro</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): 
                                        $activo = $usuario['activo'] ?? 0;
                                    ?>
                                        <tr>
                                            <td><?php echo $usuario['id_usuario']; ?></td>
                                            <td>
                                                <div class="user-info">
                                                    <i class="fas fa-user-circle"></i>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($usuario['nombre']); ?></strong>
                                                        <small><?php echo ucfirst($usuario['rol']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                            <td><?php echo htmlspecialchars($usuario['telefono'] ?? 'No especificado'); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($usuario['fecha_registro'])); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $activo ? 'active' : 'inactive'; ?>">
                                                    <?php echo $activo ? 'Activo' : 'Inactivo'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="user_id" value="<?php echo $usuario['id_usuario']; ?>">
                                                        <input type="hidden" name="new_status" value="<?php echo $activo ? 0 : 1; ?>">
                                                        <button type="submit" name="toggle_status" 
                                                                class="btn-action <?php echo $activo ? 'btn-warning' : 'btn-success'; ?>"
                                                                title="<?php echo $activo ? 'Desactivar' : 'Activar'; ?>">
                                                            <i class="fas fa-<?php echo $activo ? 'ban' : 'check'; ?>"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <form method="POST" style="display: inline;" 
                                                            onsubmit="return confirm('¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer.')">
                                                        <input type="hidden" name="user_id" value="<?php echo $usuario['id_usuario']; ?>">
                                                        <button type="submit" name="delete_user" class="btn-action btn-danger" title="Eliminar">
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

    <style>
        :root {
            --primary-teal: #00796B;
            --primary-orange: #FF9800;
            --primary-blue: #E0F2F7;
            --white: #ffffff;
            --dark-gray: #333333;
            --light-gray: #f5f5f5;
            --text-gray: #666666;
        }

        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--light-gray);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

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
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .admin-nav {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .welcome {
            font-size: 1rem;
        }

        .logout-btn {
            background: var(--primary-orange);
            color: var(--white);
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            background: #e68a00;
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
            font-size: 2rem;
        }

        .page-header p {
            color: var(--text-gray);
            font-size: 1rem;
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
            font-weight: bold;
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
            font-weight: bold;
        }

        .table-card {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
        }

        .card-header h3 {
            color: var(--primary-teal);
            margin: 0;
            font-size: 1.25rem;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 0 0 15px 15px;
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
        }

        .data-table th {
            background: var(--light-gray);
            color: var(--primary-teal);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-info i {
            color: var(--primary-teal);
            font-size: 1.5rem;
        }

        .user-info strong {
            display: block;
            color: var(--dark-gray);
        }

        .user-info small {
            color: var(--text-gray);
            text-transform: uppercase;
            font-size: 0.7rem;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            display: inline-block;
        }

        .status-badge.active {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            border: none;
            padding: 0.5rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--white);
            display: inline-flex;
            justify-content: center;
            align-items: center;
            font-size: 1rem;
        }

        .btn-success {
            background: #28a745;
        }

        .btn-warning {
            background: #ffc107;
            color: var(--dark-gray);
        }

        .btn-danger {
            background: #dc3545;
        }

        .btn-action:hover {
            opacity: 0.9;
            transform: translateY(-2px);
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

        .no-data h4 {
            margin: 0.5rem 0;
            color: var(--dark-gray);
        }

        .no-data p {
            margin: 0;
        }

        @media (max-width: 768px) {
            .admin-sidebar {
                width: 100%;
                height: auto;
                position: relative;
                box-shadow: none;
                transform: translateX(0);
            }

            .admin-layout {
                flex-direction: column;
                margin-top: 0;
            }

            .admin-main {
                margin-left: 0;
                padding: 1rem;
            }

            .admin-header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .admin-nav {
                flex-direction: column;
                gap: 1rem;
            }

            .data-table {
                font-size: 0.9rem;
            }

            .data-table th,
            .data-table td {
                padding: 0.75rem;
            }

            .user-info {
                flex-direction: column;
                align-items: flex-start;
            }

            .action-buttons {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</body>
</html>