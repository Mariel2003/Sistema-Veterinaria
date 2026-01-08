<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_user'])) {
    $nuevoUsuario = trim($_POST['username']);
    $nuevaClave = $_POST['password'];
    $role = $_POST['role'];
    $host = $_POST['host'] ?? 'localhost';
    
    if (empty($nuevoUsuario) || empty($nuevaClave)) {
        $error = 'Usuario y contraseña son obligatorios.';
    } else {
        $adminUser = getCurrentMySQLUser();
        $adminPassword = getCurrentMySQLPassword();
        
        $conexion = new mysqli("localhost", $adminUser, $adminPassword);
        
        if ($conexion->connect_error) {
            $error = "Error de conexión: " . $conexion->connect_error;
        } else {
            $crearUsuario = "CREATE USER IF NOT EXISTS '$nuevoUsuario'@'$host' IDENTIFIED BY '$nuevaClave'";
            
            if (!$conexion->query($crearUsuario)) {
                $error = "Error al crear usuario: " . $conexion->error;
            } else {
                if ($role === 'admin') {
                    $otorgarPermisos1 = "GRANT ALL PRIVILEGES ON veterinaria_db.* TO '$nuevoUsuario'@'$host'";
                    $otorgarPermisos2 = "GRANT CREATE USER ON . TO '$nuevoUsuario'@'$host'";
                    $otorgarPermisos3 = "GRANT RELOAD ON . TO '$nuevoUsuario'@'$host'";
                    
                    if (!$conexion->query($otorgarPermisos1)) {
                        $error = "Error al otorgar permisos de base de datos: " . $conexion->error;
                    } elseif (!$conexion->query($otorgarPermisos2)) {
                        $error = "Error al otorgar permisos de creación de usuarios: " . $conexion->error;
                    } elseif (!$conexion->query($otorgarPermisos3)) {
                        $error = "Error al otorgar permisos de recarga: " . $conexion->error;
                    } else {
                        $message = "✅ Usuario administrador '$nuevoUsuario' creado con todos los permisos sobre veterinaria_db y capacidad de crear usuarios.";
                    }
                } else {
                    $otorgarPermisos = "GRANT SELECT, INSERT, UPDATE ON veterinaria_db.* TO '$nuevoUsuario'@'$host'";
                    
                    if (!$conexion->query($otorgarPermisos)) {
                        $error = "Error al otorgar permisos: " . $conexion->error;
                    } else {
                        $message = "✅ Usuario empleado '$nuevoUsuario' creado con permisos SELECT, INSERT, UPDATE sobre veterinaria_db (sin permisos de eliminación).";
                    }
                }
                
                if (empty($error)) {
                    $conexion->query("FLUSH PRIVILEGES");
                }
            }
            
            $conexion->close();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $usuarioEliminar = $_POST['delete_username'];
    $host = $_POST['delete_host'] ?? 'localhost';
    
    if ($usuarioEliminar === 'root') {
        $error = 'No se puede eliminar el usuario root.';
    } else {
        $adminUser = getCurrentMySQLUser();
        $adminPassword = getCurrentMySQLPassword();
        
        $conexion = new mysqli("localhost", $adminUser, $adminPassword);
        
        if ($conexion->connect_error) {
            $error = "Error de conexión: " . $conexion->connect_error;
        } else {
            $eliminarUsuario = "DROP USER IF EXISTS '$usuarioEliminar'@'$host'";
            
            if (!$conexion->query($eliminarUsuario)) {
                $error = "Error al eliminar usuario: " . $conexion->error;
            } else {
                $conexion->query("FLUSH PRIVILEGES");
                $message = "✅ Usuario '$usuarioEliminar' eliminado exitosamente.";
            }
            
            $conexion->close();
        }
    }
}

$mysql_users = [];
try {
    $adminUser = getCurrentMySQLUser();
    $adminPassword = getCurrentMySQLPassword();
    
    $conexion = new mysqli("localhost", $adminUser, $adminPassword);
    
    if (!$conexion->connect_error) {
        $consulta = $conexion->query("
            SELECT DISTINCT u.User, u.Host,
                    CASE 
                        WHEN u.User = 'root' THEN 'admin'
                        WHEN u.Create_user_priv = 'Y' THEN 'admin'
                        ELSE 'empleado'
                    END as role
            FROM mysql.user u
            LEFT JOIN mysql.db db ON u.User = db.User AND u.Host = db.Host
            WHERE (db.Db = 'veterinaria_db' OR u.User = 'root' OR u.User IN (
                SELECT DISTINCT User FROM mysql.db WHERE Db = 'veterinaria_db'
            ))
            AND u.User != ''
            ORDER BY u.User
        ");
        
        if ($consulta) {
            while ($fila = $consulta->fetch_assoc()) {
                $mysql_users[] = $fila;
            }
        }
        
        $conexion->close();
    }
} catch (Exception $e) {
    $error = "Error al obtener usuarios: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión Usuarios MySQL - Mi Lugar Pet</title>
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
                <a href="usuarios-mysql.php" class="menu-item active">
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
                    <h1>Gestión de Usuarios MySQL</h1>
                    <p>Crear empleados y administradores con acceso a phpMyAdmin</p>
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

                <div class="form-card">
                    <div class="card-header">
                        <h3>Crear Nuevo Usuario MySQL</h3>
                        <p>Este usuario podrá acceder a phpMyAdmin con los permisos asignados</p>
                    </div>
                    <form method="POST" class="user-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Nombre de Usuario</label>
                                <input type="text" id="username" name="username" required
                                       placeholder="Ej: empleado1, veterinario1">
                                <small>Este será el usuario para acceder a phpMyAdmin</small>
                            </div>
                            <div class="form-group">
                                <label for="password">Contraseña</label>
                                <input type="password" id="password" name="password" required
                                       placeholder="Contraseña segura">
                                <small>Mínimo 8 caracteres recomendado</small>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="role">Tipo de Usuario</label>
                                <select id="role" name="role" required>
                                    <option value="empleado">Empleado</option>
                                    <option value="admin">Administrador</option>
                                </select>
                                <small>Define los permisos que tendrá el usuario</small>
                            </div>
                            <div class="form-group">
                                <label for="host">Host de Acceso</label>
                                <input type="text" id="host" name="host" value="localhost"
                                       placeholder="localhost">
                                <small>Desde dónde puede conectarse (localhost recomendado)</small>
                            </div>
                        </div>

                        <div class="permissions-info">
                            <h4><i class="fas fa-info-circle"></i> Permisos por Tipo de Usuario:</h4>
                            <div class="permission-grid">
                                <div class="permission-item empleado-perms">
                                    <h5><i class="fas fa-user-tie"></i> Empleado</h5>
                                    <ul>
                                        <li>SELECT (consultar datos)</li>
                                        <li>INSERT (agregar registros)</li>
                                        <li>UPDATE (modificar registros)</li>
                                        <li>Solo permisos de consulta, inserción y modificación</li>
                                        <li>Solo en base de datos: <strong>veterinaria_db</strong></li>
                                    </ul>
                                </div>
                                <div class="permission-item admin-perms">
                                    <h5><i class="fas fa-user-shield"></i> Administrador</h5>
                                    <ul>
                                        <li>Todos los permisos en <strong>veterinaria_db</strong></li>
                                        <li>Crear y eliminar usuarios</li>
                                        <li>Gestionar permisos</li>
                                        <li>Acceso completo a phpMyAdmin</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <button type="submit" name="create_user" class="btn-create">
                            <i class="fas fa-user-plus"></i>
                            Crear Usuario MySQL
                        </button>
                    </form>
                </div>

                <div class="users-card">
                    <div class="card-header">
                        <h3>Usuarios MySQL Existentes</h3>
                        <p>Usuarios con acceso a la base de datos veterinaria_db</p>
                    </div>
                    
                    <?php if (empty($mysql_users)): ?>
                        <div class="no-data">
                            <i class="fas fa-database"></i>
                            <h4>No se encontraron usuarios</h4>
                            <p>Crea el primer usuario empleado o verifica la conexión</p>
                        </div>
                    <?php else: ?>
                        <div class="users-table-container">
                            <table class="users-table">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-user"></i> Usuario</th>
                                        <th><i class="fas fa-server"></i> Host</th>
                                        <th><i class="fas fa-shield-alt"></i> Tipo</th>
                                        <th><i class="fas fa-key"></i> Acceso</th>
                                        <th><i class="fas fa-cogs"></i> Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mysql_users as $user): ?>
                                        <tr>
                                            <td>
                                                <div class="user-info">
                                                    <i class="fas fa-user-circle"></i>
                                                    <span class="username"><?php echo htmlspecialchars($user['User']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="host-badge"><?php echo htmlspecialchars($user['Host']); ?></span>
                                            </td>
                                            <td>
                                                <span class="role-badge <?php echo $user['role']; ?>">
                                                    <?php if ($user['role'] === 'admin'): ?>
                                                        <i class="fas fa-user-shield"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-user-tie"></i>
                                                    <?php endif; ?>
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="access-info">
                                                    <i class="fas fa-database"></i>
                                                    phpMyAdmin
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($user['User'] !== 'root'): ?>
                                                    <form method="POST" style="display: inline;"
                                                          onsubmit="return confirm('¿Estás seguro de eliminar el usuario <?php echo $user['User']; ?>? Esta acción no se puede deshacer.')">
                                                        <input type="hidden" name="delete_username" value="<?php echo $user['User']; ?>">
                                                        <input type="hidden" name="delete_host" value="<?php echo $user['Host']; ?>">
                                                        <button type="submit" name="delete_user" class="btn-danger" title="Eliminar usuario">
                                                            <i class="fas fa-trash-alt"></i>
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="protected-user">
                                                        <i class="fas fa-shield-alt"></i>
                                                        Usuario Protegido
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="info-card">
                    <div class="info-header">
                        <i class="fas fa-lightbulb"></i>
                        <h3>Información Importante</h3>
                    </div>
                    <div class="info-content">
                        <div class="info-item">
                            <i class="fas fa-key"></i>
                            <div>
                                <strong>Acceso a phpMyAdmin:</strong>
                                <p>Los usuarios creados podrán acceder a phpMyAdmin usando sus credenciales</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-database"></i>
                            <div>
                                <strong>Base de Datos:</strong>
                                <p>Solo tendrán acceso a la base de datos <code>veterinaria_db</code></p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-shield-alt"></i>
                            <div>
                                <strong>Seguridad:</strong>
                                <p>Los empleados NO pueden eliminar registros, solo consultar, agregar y modificar</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <strong>Permisos Restringidos:</strong>
                                <p>Los empleados no tienen permisos DELETE para mayor seguridad de los datos</p>
                            </div>
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
        }

        .admin-nav {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .welcome {
            font-weight: 500;
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
            color: var(--dark-gray);
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

        .page-header p {
            color: var(--text-gray);
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

        .form-card, .users-card, .info-card {
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
            margin: 0 0 0.5rem 0;
        }

        .card-header p {
            color: var(--text-gray);
            margin: 0;
            font-size: 0.9rem;
        }

        .user-form {
            padding: 2rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 0.5rem;
            color: var(--dark-gray);
            font-weight: 600;
        }

        .form-group input,
        .form-group select {
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-teal);
        }

        .form-group small {
            margin-top: 0.25rem;
            color: var(--text-gray);
            font-size: 0.8rem;
        }

        .permissions-info {
            background: linear-gradient(135deg, var(--primary-blue) 0%, #f8f9fa 100%);
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            border: 1px solid #e9ecef;
        }

        .permissions-info h4 {
            color: var(--primary-teal);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .permission-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .permission-item {
            background: var(--white);
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid var(--primary-teal);
        }

        .permission-item.empleado-perms {
            border-left-color: var(--primary-yellow);
        }

        .permission-item.admin-perms {
            border-left-color: var(--primary-orange);
        }

        .permission-item h5 {
            color: var(--primary-teal);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .permission-item ul {
            margin: 0;
            padding-left: 1rem;
        }

        .permission-item li {
            color: var(--text-gray);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .btn-create {
            background: linear-gradient(135deg, var(--primary-teal) 0%, var(--primary-orange) 100%);
            color: var(--white);
            border: none;
            padding: 1rem 2rem;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            width: 100%;
        }

        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .users-table-container {
            overflow-x: auto;
            padding: 1rem;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--white);
        }

        .users-table th,
        .users-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .users-table th {
            background: var(--light-gray);
            color: var(--primary-teal);
            font-weight: 600;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-info i {
            color: var(--primary-teal);
            font-size: 1.2rem;
        }

        .username {
            font-weight: 600;
            color: var(--dark-gray);
        }

        .host-badge {
            background: var(--primary-blue);
            color: var(--primary-teal);
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .role-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .role-badge.admin {
            background: var(--primary-orange);
            color: var(--white);
        }

        .role-badge.empleado {
            background: var(--primary-yellow);
            color: var(--dark-gray);
        }

        .access-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-teal);
            font-weight: 500;
        }

        .btn-danger {
            background: #dc3545;
            color: var(--white);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-1px);
        }

        .protected-user {
            color: var(--text-gray);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
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

        .info-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
        }

        .info-header i {
            color: var(--primary-orange);
            font-size: 1.5rem;
        }

        .info-header h3 {
            color: var(--primary-teal);
            margin: 0;
        }

        .info-content {
            padding: 1.5rem;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .info-item i {
            color: var(--primary-teal);
            font-size: 1.2rem;
            margin-top: 0.25rem;
        }

        .info-item strong {
            color: var(--primary-teal);
            display: block;
            margin-bottom: 0.25rem;
        }

        .info-item p {
            color: var(--text-gray);
            margin: 0;
            font-size: 0.9rem;
        }

        .info-item code {
            background: var(--primary-blue);
            color: var(--primary-teal);
            padding: 0.2rem 0.4rem;
            border-radius: 4px;
            font-weight: 600;
        }

        .generate-password-btn {
            background: var(--primary-blue);
            color: var(--primary-teal);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            margin-top: 0.5rem;
            transition: all 0.3s ease;
        }

        .generate-password-btn:hover {
            background: var(--primary-teal);
            color: var(--white);
        }

        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }
            .admin-main {
                margin-left: 0;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
            .permission-grid {
                grid-template-columns: 1fr;
            }
            .users-table-container {
                padding: 0.5rem;
            }
            .users-table th,
            .users-table td {
                padding: 0.5rem;
                font-size: 0.9rem;
            }
        }
    </style>

    <script>
        function generatePassword() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let password = '';
            for (let i = 0; i < 12; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.getElementById('password').value = password;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const generateBtn = document.createElement('button');
            generateBtn.type = 'button';
            generateBtn.innerHTML = '<i class="fas fa-random"></i> Generar';
            generateBtn.className = 'generate-password-btn';
            generateBtn.onclick = generatePassword;
            
            passwordInput.parentNode.appendChild(generateBtn);
        });
    </script>
</body>
</html>