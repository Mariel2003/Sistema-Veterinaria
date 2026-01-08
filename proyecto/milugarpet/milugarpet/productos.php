<?php
require_once 'config/database.php';
require_once 'config/session.php';

$db = new Database();

$categoria_filtro = $_GET['categoria'] ?? '';
$busqueda = $_GET['busqueda'] ?? '';

$query = 'SELECT * FROM productos WHERE activo = 1';
$params = [];

if ($categoria_filtro) {
    $query .= ' AND categoria = :categoria';
    $params[':categoria'] = $categoria_filtro;
}

if ($busqueda) {
    $query .= ' AND (nombre LIKE :busqueda OR descripcion LIKE :busqueda)';
    $params[':busqueda'] = '%' . $busqueda . '%';
}

$query .= ' ORDER BY nombre ASC';

$db->query($query);
foreach ($params as $param => $value) {
    $db->bind($param, $value);
}
$productos = $db->resultset();

$db->query('SELECT DISTINCT categoria FROM productos WHERE activo = 1 AND categoria IS NOT NULL ORDER BY categoria');
$categorias = $db->resultset();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart']) && isClient()) {
    $producto_id = $_POST['producto_id'];
    $cantidad = $_POST['cantidad'] ?? 1;
    $user_id = getCurrentUserId();
    
    $db->query('SELECT * FROM carrito WHERE id_usuario = :user_id AND id_producto = :producto_id');
    $db->bind(':user_id', $user_id);
    $db->bind(':producto_id', $producto_id);
    $existing_item = $db->single();
    
    if ($existing_item) {
        $db->query('UPDATE carrito SET cantidad = cantidad + :cantidad WHERE id_usuario = :user_id AND id_producto = :producto_id');
        $db->bind(':cantidad', $cantidad);
        $db->bind(':user_id', $user_id);
        $db->bind(':producto_id', $producto_id);
    } else {
        $db->query('INSERT INTO carrito (id_usuario, id_producto, cantidad) VALUES (:user_id, :producto_id, :cantidad)');
        $db->bind(':user_id', $user_id);
        $db->bind(':producto_id', $producto_id);
        $db->bind(':cantidad', $cantidad);
    }
    
    if ($db->execute()) {
        $success_message = 'Producto agregado al carrito exitosamente.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Mi Lugar Pet</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
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
                    <a href="productos.php" class="nav-link active">Productos</a>
                    <a href="consultas.php" class="nav-link">Consultas</a>
                    <?php if (isLoggedIn()): ?>
                        <div class="user-menu">
                            <span class="user-welcome">Hola, <?php echo getCurrentUserName(); ?></span>
                            <?php if (isClient()): ?>
                                <a href="carrito.php" class="nav-link cart-link">
                                    <i class="fas fa-shopping-cart"></i>
                                    <span class="cart-count" id="cart-count">0</span>
                                </a>
                            <?php endif; ?>
                            <?php if (isAdmin()): ?>
                                <a href="admin/dashboard.php" class="nav-link admin-link">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                            <?php elseif (isEmployee()): ?>
                                <a href="empleado/dashboard.php" class="nav-link employee-link">
                                    <i class="fas fa-clipboard-list"></i> Panel
                                </a>
                            <?php endif; ?>
                            <a href="logout.php" class="nav-link logout-btn">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="nav-link login-btn">Iniciar Sesión</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <section class="products-hero">
        <div class="container">
            <h1>Nuestros Productos</h1>
            <p>Todo lo que tu mascota necesita para estar feliz y saludable</p>
        </div>
    </section>

    <section class="filters-section">
        <div class="container">
            <div class="filters-container">
                <form method="GET" class="filters-form">
                    <div class="search-box">
                        <input type="text" name="busqueda" placeholder="Buscar productos..." value="<?php echo htmlspecialchars($busqueda); ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </div>
                    
                    <div class="category-filter">
                        <select name="categoria" onchange="this.form.submit()">
                            <option value="">Todas las categorías</option>
                            <?php foreach($categorias as $categoria): ?>
                                <option value="<?php echo htmlspecialchars($categoria['categoria']); ?>" 
                                        <?php echo $categoria_filtro === $categoria['categoria'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($categoria['categoria']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <?php if (isset($success_message)): ?>
        <div class="success-banner">
            <div class="container">
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_message; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <section class="products-section">
        <div class="container">
            <?php if (empty($productos)): ?>
                <div class="no-products">
                    <i class="fas fa-box-open"></i>
                    <h3>No se encontraron productos</h3>
                    <p>Intenta con otros filtros de búsqueda</p>
                    <a href="productos.php" class="btn-primary">Ver todos los productos</a>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach($productos as $producto): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?php echo $producto['imagen_url'] ?: '/placeholder.svg?height=250&width=250&query=' . urlencode($producto['nombre']); ?>" 
                                     alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                <?php if ($producto['stock'] <= 5 && $producto['stock'] > 0): ?>
                                    <div class="stock-badge low-stock">¡Últimas unidades!</div>
                                <?php elseif ($producto['stock'] == 0): ?>
                                    <div class="stock-badge out-of-stock">Agotado</div>
                                <?php endif; ?>
                                
                                <?php if (isClient() && $producto['stock'] > 0): ?>
                                    <div class="product-overlay">
                                        <form method="POST" class="add-to-cart-form">
                                            <input type="hidden" name="producto_id" value="<?php echo $producto['id_producto']; ?>">
                                            <input type="hidden" name="add_to_cart" value="1">
                                            <button type="submit" class="add-to-cart">
                                                <i class="fas fa-shopping-cart"></i>
                                                Agregar al Carrito
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <span class="product-category"><?php echo htmlspecialchars($producto['categoria']); ?></span>
                                <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                                <p class="product-description"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                                <div class="product-footer">
                                    <div class="price-stock">
                                        <span class="product-price">Bs.<?php echo number_format($producto['precio'], 2); ?></span>
                                        <span class="product-stock">Stock: <?php echo $producto['stock']; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <i class="fas fa-paw"></i>
                        <span>Mi Lugar Pet</span>
                    </div>
                    <p>Cuidando a tus mascotas con amor y profesionalismo desde 2019.</p>
                </div>
                
                <div class="footer-section">
                    <h3>Contáctanos</h3>
                    <div class="contact-info">
                        <p><i class="fas fa-phone"></i> +591 62960477</p>
                        <p><i class="fas fa-envelope"></i> info@milugarpet.com</p>
                        <p><i class="fas fa-map-marker-alt"></i> Av. Principal 123, Ciudad</p>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Acerca de</h3>
                    <ul class="footer-links">
                        <li><a href="#">Nuestra Historia</a></li>
                        <li><a href="#">Nuestro Equipo</a></li>
                        <li><a href="#">Misión y Visión</a></li>
                        <li><a href="#">Testimonios</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Redes Sociales</h3>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Mi Lugar Pet. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <style>
        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-welcome {
            color: var(--primary-teal);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .cart-link {
            position: relative;
            background: var(--primary-yellow);
            color: var(--dark-gray) !important;
            padding: 0.5rem;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--primary-orange);
            color: var(--white);
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .admin-link {
            background: var(--primary-orange);
            color: var(--white) !important;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .employee-link {
            background: var(--primary-yellow);
            color: var(--dark-gray) !important;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .products-hero {
            background: linear-gradient(135deg, var(--primary-orange) 0%, var(--primary-yellow) 100%);
            padding: 120px 0 60px;
            text-align: center;
            color: var(--white);
        }

        .products-hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .filters-section {
            padding: 2rem 0;
            background: var(--white);
            border-bottom: 1px solid #eee;
        }

        .filters-container {
            display: flex;
            justify-content: center;
        }

        .filters-form {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 0.8rem 3rem 0.8rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            width: 300px;
            font-size: 1rem;
        }

        .search-box button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary-teal);
            color: var(--white);
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            cursor: pointer;
        }

        .category-filter select {
            padding: 0.8rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 1rem;
            background: var(--white);
        }

        .success-banner {
            background: var(--primary-teal);
            color: var(--white);
            padding: 1rem 0;
        }

        .success-message {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .products-section {
            padding: 80px 0;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .product-image {
            position: relative;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .low-stock {
            background: var(--primary-yellow);
            color: var(--dark-gray);
        }

        .out-of-stock {
            background: #dc3545;
            color: var(--white);
        }

        .product-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(70, 147, 135, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .product-card:hover .product-overlay {
            opacity: 1;
        }

        .add-to-cart {
            background: var(--primary-orange);
            color: var(--white);
            border: none;
            padding: 1rem 2rem;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .add-to-cart:hover {
            background: var(--primary-yellow);
            color: var(--dark-gray);
            transform: scale(1.05);
        }

        .product-info {
            padding: 1.5rem;
        }

        .product-category {
            color: var(--primary-teal);
            font-size: 0.9rem;
            font-weight: bold;
        }

        .product-info h3 {
            margin: 0.5rem 0;
            color: var(--dark-gray);
        }

        .product-description {
            color: var(--text-gray);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .price-stock {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .product-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-orange);
        }

        .product-stock {
            font-size: 0.8rem;
            color: var(--text-gray);
        }

        .no-products {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-gray);
        }

        .no-products i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--primary-teal);
        }

        .btn-primary {
            background: var(--primary-teal);
            color: var(--white);
            padding: 1rem 2rem;
            border-radius: 25px;
            text-decoration: none;
            display: inline-block;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--primary-orange);
        }

        @media (max-width: 768px) {
            .user-menu {
                flex-direction: column;
                gap: 0.5rem;
            }

            .filters-form {
                flex-direction: column;
                gap: 1rem;
            }

            .search-box input {
                width: 100%;
            }
        }
    </style>

    <script>
        function updateCartCount() {
            <?php if (isClient()): ?>
                fetch('get_cart_count.php')
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('cart-count').textContent = data.count;
                    });
            <?php endif; ?>
        }

        document.addEventListener('DOMContentLoaded', updateCartCount);

        document.querySelectorAll('.add-to-cart-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                setTimeout(updateCartCount, 500);
            });
        });
    </script>
</body>
</html>