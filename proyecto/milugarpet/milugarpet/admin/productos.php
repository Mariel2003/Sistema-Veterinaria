<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conexion = getMySQLConnection();
    
    if (isset($_POST['create_product'])) {
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        $precio = floatval($_POST['precio']);
        $categoria = trim($_POST['categoria']);
        $stock = intval($_POST['stock']);
        $imagen_url = trim($_POST['imagen_url']);
        
        $stmt = $conexion->prepare("INSERT INTO productos (nombre, descripcion, precio, categoria, stock, imagen_url, activo) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("ssdsis", $nombre, $descripcion, $precio, $categoria, $stock, $imagen_url);
        
        if ($stmt->execute()) {
            $message = "✅ Producto creado exitosamente.";
        } else {
            $error = "Error al crear producto: " . $conexion->error;
        }
        $stmt->close();
    }
    
    if (isset($_POST['update_product'])) {
        $id = $_POST['product_id'];
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        $precio = floatval($_POST['precio']);
        $categoria = trim($_POST['categoria']);
        $stock = intval($_POST['stock']);
        $imagen_url = trim($_POST['imagen_url']);
        
        $stmt = $conexion->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, categoria = ?, stock = ?, imagen_url = ? WHERE id_producto = ?");
        $stmt->bind_param("ssdsisi", $nombre, $descripcion, $precio, $categoria, $stock, $imagen_url, $id);
        
        if ($stmt->execute()) {
            $message = " Producto actualizado exitosamente.";
        } else {
            $error = "Error al actualizar producto: " . $conexion->error;
        }
        $stmt->close();
    }
    
    if (isset($_POST['delete_product'])) {
        $id = $_POST['product_id'];
        
        $stmt = $conexion->prepare("DELETE FROM productos WHERE id_producto = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = "Producto eliminado exitosamente.";
        } else {
            $error = "Error al eliminar producto: " . $conexion->error;
        }
        $stmt->close();
    }
    
    if (isset($_POST['toggle_status'])) {
        $id = $_POST['product_id'];
        $new_status = $_POST['new_status'];
        
        $stmt = $conexion->prepare("UPDATE productos SET activo = ? WHERE id_producto = ?");
        $stmt->bind_param("ii", $new_status, $id);
        
        if ($stmt->execute()) {
            $status_text = $new_status ? 'activado' : 'desactivado';
            $message = "Producto $status_text exitosamente.";
        } else {
            $error = "Error al cambiar estado: " . $conexion->error;
        }
        $stmt->close();
    }
    
    $conexion->close();
}

$conexion = getMySQLConnection();
$consulta = $conexion->query("SELECT * FROM productos ORDER BY id_producto DESC");
$productos = [];
if ($consulta) {
    while ($fila = $consulta->fetch_assoc()) {
        $productos[] = $fila;
    }
}

$consulta_cat = $conexion->query("SELECT DISTINCT categoria FROM productos WHERE categoria IS NOT NULL ORDER BY categoria");
$categorias = [];
if ($consulta_cat) {
    while ($fila = $consulta_cat->fetch_assoc()) {
        $categorias[] = $fila['categoria'];
    }
}

$conexion->close();

$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    foreach ($productos as $producto) {
        if ($producto['id_producto'] == $edit_id) {
            $edit_product = $producto;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - Mi Lugar Pet</title>
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
                <a href="productos.php" class="menu-item active">
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
                    <h1>Gestión de Productos</h1>
                    <p>Administra el catálogo de productos</p>
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
                        <h3><?php echo $edit_product ? 'Editar Producto' : 'Crear Nuevo Producto'; ?></h3>
                    </div>
                    <form method="POST" class="product-form">
                        <?php if ($edit_product): ?>
                            <input type="hidden" name="product_id" value="<?php echo $edit_product['id_producto']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre">Nombre del Producto</label>
                                <input type="text" id="nombre" name="nombre" required 
                                       value="<?php echo $edit_product ? htmlspecialchars($edit_product['nombre']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="categoria">Categoría</label>
                                <input type="text" id="categoria" name="categoria" required list="categorias"
                                       value="<?php echo $edit_product ? htmlspecialchars($edit_product['categoria']) : ''; ?>">
                                <datalist id="categorias">
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat); ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea id="descripcion" name="descripcion" rows="3" required><?php echo $edit_product ? htmlspecialchars($edit_product['descripcion']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="precio">Precio (Bs.)</label>
                                <input type="number" id="precio" name="precio" step="0.01" min="0" required
                                       value="<?php echo $edit_product ? $edit_product['precio'] : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="stock">Stock</label>
                                <input type="number" id="stock" name="stock" min="0" required
                                       value="<?php echo $edit_product ? $edit_product['stock'] : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="imagen_url">URL de Imagen (opcional)</label>
                            <input type="url" id="imagen_url" name="imagen_url"
                                   value="<?php echo $edit_product ? htmlspecialchars($edit_product['imagen_url']) : ''; ?>">
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="<?php echo $edit_product ? 'update_product' : 'create_product'; ?>" class="btn-primary">
                                <i class="fas fa-<?php echo $edit_product ? 'save' : 'plus'; ?>"></i>
                                <?php echo $edit_product ? 'Actualizar Producto' : 'Crear Producto'; ?>
                            </button>
                            <?php if ($edit_product): ?>
                                <a href="productos.php" class="btn-secondary">
                                    <i class="fas fa-times"></i>
                                    Cancelar
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="table-card">
                    <div class="card-header">
                        <h3>Productos Registrados (<?php echo count($productos); ?>)</h3>
                    </div>
                    
                    <?php if (empty($productos)): ?>
                        <div class="no-data">
                            <i class="fas fa-box"></i>
                            <h4>No hay productos registrados</h4>
                            <p>Crea el primer producto usando el formulario</p>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Producto</th>
                                        <th>Categoría</th>
                                        <th>Precio</th>
                                        <th>Stock</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($productos as $producto): ?>
                                        <tr>
                                            <td><?php echo $producto['id_producto']; ?></td>
                                            <td>
                                                <div class="product-info">
                                                    <div class="product-image">
                                                        <img src="<?php echo $producto['imagen_url'] ?: '/placeholder.svg?height=50&width=50&query=' . urlencode($producto['nombre']); ?>" 
                                                             alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                                    </div>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                                                        <small><?php echo htmlspecialchars(substr($producto['descripcion'], 0, 50)) . '...'; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="category-badge"><?php echo htmlspecialchars($producto['categoria']); ?></span>
                                            </td>
                                            <td>
                                                <span class="price">Bs.<?php echo number_format($producto['precio'], 2); ?></span>
                                            </td>
                                            <td>
                                                <span class="stock <?php echo $producto['stock'] <= 5 ? 'low' : ''; ?>">
                                                    <?php echo $producto['stock']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge <?php echo $producto['activo'] ? 'active' : 'inactive'; ?>">
                                                    <?php echo $producto['activo'] ? 'Activo' : 'Inactivo'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="productos.php?edit=<?php echo $producto['id_producto']; ?>" 
                                                       class="btn-action btn-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="product_id" value="<?php echo $producto['id_producto']; ?>">
                                                        <input type="hidden" name="new_status" value="<?php echo $producto['activo'] ? 0 : 1; ?>">
                                                        <button type="submit" name="toggle_status" 
                                                                class="btn-action <?php echo $producto['activo'] ? 'btn-warning' : 'btn-success'; ?>"
                                                                title="<?php echo $producto['activo'] ? 'Desactivar' : 'Activar'; ?>">
                                                            <i class="fas fa-<?php echo $producto['activo'] ? 'ban' : 'check'; ?>"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <form method="POST" style="display: inline;" 
                                                          onsubmit="return confirm('¿Estás seguro de eliminar este producto?')">
                                                        <input type="hidden" name="product_id" value="<?php echo $producto['id_producto']; ?>">
                                                        <button type="submit" name="delete_product" class="btn-action btn-danger" title="Eliminar">
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

        .form-card, .table-card {
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
        }

        .product-form {
            padding: 2rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark-gray);
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-teal);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn-primary {
            background: var(--primary-teal);
            color: var(--white);
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-primary:hover {
            background: var(--primary-orange);
        }

        .btn-secondary {
            background: var(--text-gray);
            color: var(--white);
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
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
        }

        .data-table th {
            background: var(--light-gray);
            color: var(--primary-teal);
            font-weight: 600;
        }

        .product-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .product-image img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }

        .product-info strong {
            display: block;
            color: var(--dark-gray);
        }

        .product-info small {
            color: var(--text-gray);
        }

        .category-badge {
            background: var(--primary-blue);
            color: var(--primary-teal);
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .price {
            font-weight: bold;
            color: var(--primary-orange);
            font-size: 1.1rem;
        }

        .stock {
            font-weight: bold;
            color: var(--primary-teal);
        }

        .stock.low {
            color: #dc3545;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
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
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-action.btn-primary {
            background: var(--primary-teal);
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
        }
    </style>
</body>
</html>