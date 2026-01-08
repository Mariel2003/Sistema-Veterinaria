<?php
require_once 'config/database.php';
require_once 'config/session.php';

$db = new Database();

$db->query('SELECT * FROM productos WHERE activo = 1 ORDER BY id_producto DESC LIMIT 6');
$productos_destacados = $db->resultset();

$db->query('SELECT COUNT(*) as total FROM usuarios WHERE rol = "cliente"');
$total_clientes = $db->single()['total'];

$db->query('SELECT COUNT(*) as total FROM consultas WHERE estado = "finalizada"');
$consultas_completadas = $db->single()['total'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Lugar Pet - Cuidado Veterinario con Amor</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo-container">
                    <div class="logo">
                        <div class="logo-icon">
                            <i class="fas fa-paw"></i>
                        </div>
                        <span class="logo-text">Mi Lugar Pet</span>
                    </div>
                </div>
                <nav class="nav">
                    <a href="index.php" class="nav-link active">Inicio</a>
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
                            <?php if (isLoggedIn() && isClient()): ?>
                                <a href="ver_consultas.php" class="dropdown-item">
                                    <i class="fas fa-list-alt"></i>
                                    Ver Mis Consultas
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (isLoggedIn()): ?>
                        <div class="user-menu">
                            <span class="user-welcome">Hola, <?php echo getCurrentUserName(); ?></span>
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
                <div class="mobile-menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-background">
            <div class="hero-video-overlay"></div>
            <div class="hero-patterns">
                <div class="pattern-circle pattern-1"></div>
                <div class="pattern-circle pattern-2"></div>
                <div class="pattern-circle pattern-3"></div>
            </div>
        </div>
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <div class="hero-badge fade-in-up">
                        <i class="fas fa-heart-pulse"></i>
                        <span>Cuidado Veterinario Premium</span>
                    </div>
                    <h1 class="hero-title">
                        <span class="title-main fade-in-up delay-1">Cuidado Veterinario</span>
                        <span class="title-accent fade-in-up delay-2">con Amor</span>
                    </h1>
                    <p class="hero-subtitle fade-in-up delay-3">
                        Todo lo que tu mascota necesita en un solo lugar.
                        Profesionales dedicados al bienestar y felicidad de tu compañero fiel.
                    </p>
                    <div class="hero-buttons fade-in-up delay-4">
                        <?php if (isLoggedIn()): ?>
                            <a href="consultas.php" class="btn btn-primary">
                                <i class="fas fa-calendar-check"></i>
                                <span>Agendar Cita</span>
                            </a>
                        <?php else: ?>
                            <a href="register.php" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i>
                                <span>Únete Ahora</span>
                            </a>
                        <?php endif; ?>
                        <a href="#services" class="btn btn-outline">
                            <i class="fas fa-play-circle"></i>
                            <span>Ver Servicios</span>
                        </a>
                    </div>
                </div>
                <div class="hero-visual fade-in-right">
                    <div class="hero-image-container">
                        <div class="hero-main-image">
                            <img src="https://www.barakaldotiendaveterinaria.es/blog/wp-content/uploads/2017/01/selfie-animal.jpg" alt="Veterinario con mascotas" />
                            <div class="image-overlay"></div>
                        </div>
                        <div class="floating-cards">
                            <div class="floating-card card-1">
                                <div class="card-icon">
                                    <i class="fas fa-stethoscope"></i>
                                </div>
                                <div class="card-text">
                                    <span class="card-title">Consulta</span>
                                    <span class="card-subtitle">Programada</span>
                                </div>
                            </div>
                            <div class="floating-card card-2">
                                <div class="card-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div class="card-text">
                                    <span class="card-title">Salud</span>
                                    <span class="card-subtitle">Óptima</span>
                                </div>
                            </div>
                            <div class="floating-card card-3">
                                <div class="card-icon">
                                    <i class="fas fa-paw"></i>
                                </div>
                                <div class="card-text">
                                    <span class="card-title">Cuidado</span>
                                    <span class="card-subtitle">Premium</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-scroll-indicator">
            <div class="scroll-arrow">
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number" data-target="<?php echo $total_clientes; ?>">0</div>
                    <div class="stat-label">Mascotas Atendidas</div>
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-target="8">0</div>
                    <div class="stat-label">Años de Experiencia</div>
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-target="<?php echo $consultas_completadas; ?>">0</div>
                    <div class="stat-label">Consultas Exitosas</div>
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-target="24">0</div>
                    <div class="stat-label">Horas Disponibles</div>
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services" id="services">
        <div class="container">
            <div class="section-header">
                <div class="section-tag">
                    <span>Nuestros Servicios</span>
                </div>
                <h2 class="section-title">Todo lo que tu mascota necesita</h2>
                <p class="section-subtitle">
                    Servicios profesionales diseñados para mantener a tu mascota saludable y feliz
                </p>
            </div>

            <div class="services-grid">
                <div class="service-card">
                    <div class="service-image">
                        <img src="https://escuelafarmacia.com/wp-content/uploads/cl%C3%ADnica-veterinaria.jpg" alt="Consultas Veterinarias" />
                        <div class="service-overlay">
                            <div class="service-icon">
                                <i class="fas fa-user-md"></i>
                            </div>
                        </div>
                    </div>
                    <div class="service-content">
                        <h3>Consultas Veterinarias</h3>
                        <p>Atención médica profesional con veterinarios especializados para mantener a tu mascota en óptimas condiciones.</p>
                        <a href="consultas.php" class="service-link">
                            <span>Agendar Consulta</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <div class="service-card">
                    <div class="service-image">
                        <img src="https://labovet.com.br/wp-content/uploads/2024/11/Banner_Produtos_Destaque_pet_545x430-2-1.webp" alt="Productos Premium" />
                        <div class="service-overlay">
                            <div class="service-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                        </div>
                    </div>
                    <div class="service-content">
                        <h3>Productos Premium</h3>
                        <p>Los mejores productos para el cuidado, alimentación y entretenimiento de tu mascota, seleccionados por expertos.</p>
                        <a href="productos.php" class="service-link">
                            <span>Ver Productos</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <div class="service-card">
                    <div class="service-image">
                        <img src="https://www.onlypet24horas.com.br/images/blog/veterinario-em-domicilio-em-poa/veterinario-em-domicilio-em-poa-1.jpg" alt="Cuidado Integral" />
                        <div class="service-overlay">
                            <div class="service-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                        </div>
                    </div>
                    <div class="service-content">
                        <h3>Cuidado Integral</h3>
                        <p>Desde consultas preventivas hasta tratamientos especializados, brindamos atención completa para tu mascota.</p>
                        <a href="consultas.php" class="service-link">
                            <span>Conocer Más</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Preview -->
    <?php if (!empty($productos_destacados)): ?>
        <section class="products-section">
            <div class="container">
                <div class="section-header">
                    <div class="section-tag">
                        <span>Productos Destacados</span>
                    </div>
                    <h2 class="section-title">Los favoritos de nuestras mascotas</h2>
                    <p class="section-subtitle">
                        Productos cuidadosamente seleccionados para el bienestar de tu compañero
                    </p>
                </div>

                <div class="products-grid">
                    <?php foreach (array_slice($productos_destacados, 0, 3) as $index => $producto): ?>
                        <div class="product-card" style="animation-delay: <?php echo $index * 0.2; ?>s">
                            <div class="product-image">
                                <img src="<?php echo $producto['imagen_url'] ?: '/placeholder.svg?height=250&width=250&query=' . urlencode($producto['nombre']); ?>"
                                    alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                <div class="product-overlay">
                                    <?php if (isLoggedIn() && isClient()): ?>
                                        <button class="quick-add-btn" data-product-id="<?php echo $producto['id_producto']; ?>">
                                            <i class="fas fa-shopping-cart"></i>
                                            <span>Agregar</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <div class="product-badge">
                                    <span><?php echo htmlspecialchars($producto['categoria']); ?></span>
                                </div>
                            </div>
                            <div class="product-content">
                                <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                                <p><?php echo htmlspecialchars(substr($producto['descripcion'], 0, 80)) . '...'; ?></p>
                                <div class="product-footer">
                                    <span class="product-price">Bs.<?php echo number_format($producto['precio'], 2); ?></span>
                                    <div class="product-rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <span class="rating-text">5.0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="section-cta">
                    <a href="productos.php" class="btn btn-outline btn-large">
                        <span>Ver Todos los Productos</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Gallery Section -->
    <section class="gallery-section">
        <div class="container">
            <div class="section-header">
                <div class="section-tag">
                    <span>Galería</span>
                </div>
                <h2 class="section-title">Momentos felices en Mi Lugar Pet</h2>
                <p class="section-subtitle">
                    Descubre el día a día en nuestra clínica y conoce a algunas de nuestras mascotas favoritas
                </p>
            </div>

            <div class="gallery-grid">
                <div class="gallery-item large">
                    <img src="https://img.freepik.com/fotos-premium/veterinario-tiene-perro-feliz-chequeo_116547-86057.jpg" alt="Perro feliz en la clínica" />
                    <div class="gallery-overlay">
                        <div class="gallery-content">
                            <h4>Momentos de Alegría</h4>
                            <p>Cada visita es una oportunidad para crear momentos especiales</p>
                        </div>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="https://hips.hearstapps.com/hmg-prod/images/gettyimages-1294792706-170667a-65114ef2865cd.jpg?crop=0.888015717092338xw:1xh;center,top&resize=1200:*" alt="Gato en consulta" />
                    <div class="gallery-overlay">
                        <div class="gallery-content">
                            <h4>Cuidado Especializado</h4>
                        </div>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="https://diagximag.com/wp-content/uploads/2025/03/Ecografos-para-veterinaria-e1742995398589.webp" alt="Equipamiento moderno" />
                    <div class="gallery-overlay">
                        <div class="gallery-content">
                            <h4>Tecnología Avanzada</h4>
                        </div>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="https://mundovets.com/wp-content/uploads/2023/04/satisfaccion-cliente-veterinaria.jpg" alt="Cliente satisfecho" />
                    <div class="gallery-overlay">
                        <div class="gallery-content">
                            <h4>Familias Felices</h4>
                        </div>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="https://enlinea.santotomas.cl/web/wp-content/uploads/sites/2/2016/12/mascota-correcta.jpg" alt="Mascotas diversas" />
                    <div class="gallery-overlay">
                        <div class="gallery-content">
                            <h4>Todos son Bienvenidos</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <div class="section-tag">
                        <span>Sobre Nosotros</span>
                    </div>
                    <h2 class="section-title">Una familia que ama a las mascotas</h2>
                    <p>
                        En Mi Lugar Pet, creemos que cada mascota merece el mejor cuidado posible. 
                        Nuestro equipo de veterinarios especializados está comprometido con brindar 
                        atención médica de calidad con el amor y la dedicación que tu compañero merece.
                    </p>
                    <p>
                        Con más de 8 años de experiencia y cientos de mascotas atendidas, 
                        nos hemos convertido en el lugar de confianza para familias que buscan 
                        lo mejor para sus compañeros de cuatro patas.
                    </p>
                    <div class="about-features">
                        <div class="feature-item">
                            <i class="fas fa-award"></i>
                            <span>Veterinarios Certificados</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-clock"></i>
                            <span>Atención 24/7</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Equipamiento Moderno</span>
                        </div>
                    </div>
                    <a href="#" class="btn btn-primary">
                        <span>Conocer Más</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="about-visual">
                    <div class="about-image-grid">
                        <div class="about-image main">
                            <img src="https://media.istockphoto.com/id/521072827/es/foto/perro-en-vet.jpg?s=612x612&w=0&k=20&c=elqIWjQe_EEzEhJfHK_DX2F3_NLUrqG6tnThcOeV-sk=" alt="Equipo veterinario" />
                        </div>
                        <div class="about-image secondary">
                            <img src="https://decosalud.com/wp-content/uploads/2017/10/CLINICA-VETERINARIA-DISE%C3%91O-500x333.jpg" alt="Clínica moderna" />
                        </div>
                        <div class="about-image accent">
                            <img src="https://www.bankwithunited.com/adobe/dynamicmedia/deliver/dm-aid--3996c6e7-67a9-401f-a344-eaeb3c43516f/puppy-owner.jpg?preferwebp=true&quality=85" alt="Cliente feliz" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section main">
                    <div class="footer-logo">
                        <div class="logo-icon">
                            <i class="fas fa-paw"></i>
                        </div>
                        <span class="logo-text">Mi Lugar Pet</span>
                    </div>
                    <p>Cuidando a tus mascotas con amor y profesionalismo desde 2019.</p>
                    <div class="social-links">
                        <a href="#" class="social-link">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>

                <div class="footer-section">
                    <h3>Servicios</h3>
                    <ul>
                        <li><a href="#">Consultas Veterinarias</a></li>
                        <li><a href="#">Cirugías</a></li>
                        <li><a href="#">Vacunación</a></li>
                        <li><a href="#">Productos Premium</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Información</h3>
                    <ul>
                        <li><a href="#">Sobre Nosotros</a></li>
                        <li><a href="#">Nuestro Equipo</a></li>
                        <li><a href="#">Testimonios</a></li>
                        <li><a href="#">Blog</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Contacto</h3>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Av. Principal 123, Ciudad</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+591 62960477</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>info@milugarpet.com</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Mi Lugar Pet. Todos los derechos reservados.</p>
                <div class="footer-links">
                    <a href="#">Política de Privacidad</a>
                    <a href="#">Términos y Condiciones</a>
                </div>
            </div>
        </div>
    </footer>

    <style>
        :root {
            --primary-teal: #0D9488;
            --primary-blue: #0EA5E9;
            --primary-orange: #F97316;
            --primary-yellow: #FCD34D;
            --dark-green: #047857;
            --light-green: #A7F3D0;
            --dark-gray: #1F2937;
            --text-gray: #6B7280;
            --light-gray: #F9FAFB;
            --white: #FFFFFF;
            --gradient-primary: linear-gradient(135deg, var(--primary-teal) 0%, var(--dark-green) 100%);
            --gradient-secondary: linear-gradient(135deg, var(--light-green) 0%, var(--primary-teal) 100%);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--dark-gray);
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* Header */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            z-index: 1000;
            padding: 1rem 0;
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(13, 148, 136, 0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-teal);
        }

        .nav {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .nav-link {
            color: var(--dark-gray);
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 0;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--primary-teal);
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--primary-teal);
            border-radius: 1px;
        }

        /* Dropdown */
        .dropdown {
            position: relative;
        }

        .dropdown-btn {
            background: none;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--dark-gray);
            font-weight: 500;
            padding: 0.5rem 0;
            transition: all 0.3s ease;
        }

        .dropdown-btn:hover {
            color: var(--primary-teal);
        }

        .dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            min-width: 200px;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            margin-top: 0.5rem;
            z-index: 1000;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--dark-gray);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background: var(--light-gray);
            color: var(--primary-teal);
        }

        /* User Menu */
        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-welcome {
            font-weight: 600;
            color: var(--primary-teal);
        }

        .admin-link, .employee-link {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .admin-link {
            background: var(--primary-orange);
            color: white !important;
        }

        .employee-link {
            background: var(--primary-yellow);
            color: var(--dark-gray) !important;
        }

        .login-btn, .logout-btn {
            background: var(--gradient-primary);
            color: white !important;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
        }

        /* Mobile Menu */
        .mobile-menu-toggle {
            display: none;
            flex-direction: column;
            cursor: pointer;
            gap: 4px;
        }

        .mobile-menu-toggle span {
            width: 25px;
            height: 3px;
            background: var(--dark-gray);
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            background: linear-gradient(135deg, #F0FDF4 0%, #DCFCE7 50%, #BBF7D0 100%);
            padding-top: 100px;
            overflow: hidden;
        }

        .hero-background {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
        }

        .hero-video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(240, 253, 244, 0.8);
        }

        .hero-patterns {
            position: absolute;
            width: 100%;
            height: 100%;
        }

        .pattern-circle {
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
        }

        .pattern-1 {
            width: 300px;
            height: 300px;
            background: var(--primary-teal);
            top: -100px;
            right: -100px;
            animation: float 8s ease-in-out infinite;
        }

        .pattern-2 {
            width: 200px;
            height: 200px;
            background: var(--primary-orange);
            bottom: 20%;
            left: -50px;
            animation: float 6s ease-in-out infinite reverse;
        }

        .pattern-3 {
            width: 150px;
            height: 150px;
            background: var(--primary-blue);
            top: 50%;
            right: 10%;
            animation: float 10s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(180deg); }
        }

        .hero-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 0.75rem 1.25rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--primary-teal);
            margin-bottom: 2rem;
            border: 1px solid rgba(13, 148, 136, 0.2);
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
        }

        .title-main {
            display: block;
            color: var(--dark-gray);
        }

        .title-accent {
            display: block;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: var(--text-gray);
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: var(--shadow-lg);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-xl);
        }

        .btn-outline {
            background: rgba(255, 255, 255, 0.9);
            color: var(--dark-gray);
            border: 2px solid rgba(13, 148, 136, 0.2);
        }

        .btn-outline:hover {
            background: white;
            border-color: var(--primary-teal);
            color: var(--primary-teal);
            transform: translateY(-2px);
        }

        .btn-large {
            padding: 1.25rem 2.5rem;
            font-size: 1.1rem;
        }

        /* Hero Visual */
        .hero-visual {
            position: relative;
        }

        .hero-image-container {
            position: relative;
        }

        .hero-main-image {
            position: relative;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--shadow-xl);
        }

        .hero-main-image img {
            width: 100%;
            height: auto;
            display: block;
        }

        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(13, 148, 136, 0.1) 0%, rgba(4, 120, 87, 0.1) 100%);
        }

        .floating-cards {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
        }

        .floating-card {
            position: absolute;
            background: white;
            padding: 1rem;
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            backdrop-filter: blur(10px);
        }

        .card-1 {
            top: 10%;
            right: -20px;
            animation: floatCard 4s ease-in-out infinite;
        }

        .card-2 {
            bottom: 30%;
            left: -30px;
            animation: floatCard 5s ease-in-out infinite reverse;
        }

        .card-3 {
            top: 60%;
            right: -25px;
            animation: floatCard 6s ease-in-out infinite;
        }

        @keyframes floatCard {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }

        .card-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient-secondary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-teal);
        }

        .card-text {
            display: flex;
            flex-direction: column;
        }

        .card-title {
            font-weight: 600;
            color: var(--dark-gray);
            font-size: 0.9rem;
        }

        .card-subtitle {
            font-size: 0.75rem;
            color: var(--text-gray);
        }

        .hero-scroll-indicator {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
        }

        .scroll-arrow {
            width: 40px;
            height: 40px;
            border: 2px solid var(--primary-teal);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-teal);
            animation: bounce 2s ease-in-out infinite;
            cursor: pointer;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(10px); }
        }

        /* Stats Section */
        .stats-section {
            padding: 5rem 0;
            background: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .stat-item {
            text-align: center;
            padding: 2rem;
            border-radius: 20px;
            background: var(--light-gray);
            position: relative;
            transition: all 0.3s ease;
        }

        .stat-item:hover {
            transform: translateY(-5px);
            background: white;
            box-shadow: var(--shadow-lg);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: var(--primary-teal);
            margin-bottom: 0.5rem;
            display: block;
        }

        .stat-label {
            font-size: 1rem;
            color: var(--text-gray);
            font-weight: 500;
        }

        .stat-icon {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 40px;
            height: 40px;
            background: var(--gradient-secondary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-teal);
            opacity: 0.7;
        }

        /* Sections */
        .services, .products-section, .gallery-section, .about-section {
            padding: 6rem 0;
        }

        .services {
            background: var(--light-gray);
        }

        .products-section {
            background: white;
        }

        .gallery-section {
            background: var(--light-gray);
        }

        .about-section {
            background: white;
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-tag {
            display: inline-block;
            background: var(--gradient-secondary);
            color: var(--primary-teal);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--dark-gray);
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .section-subtitle {
            font-size: 1.2rem;
            color: var(--text-gray);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Services Grid */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }

        .service-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .service-image {
            position: relative;
            height: 280px;
            overflow: hidden;
        }

        .service-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .service-card:hover .service-image img {
            transform: scale(1.05);
        }

        .service-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(13, 148, 136, 0.8) 0%, rgba(4, 120, 87, 0.8) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .service-card:hover .service-overlay {
            opacity: 1;
        }

        .service-icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .service-content {
            padding: 2rem;
        }

        .service-content h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-gray);
            margin-bottom: 1rem;
        }

        .service-content p {
            color: var(--text-gray);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .service-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-teal);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .service-link:hover {
            gap: 1rem;
            color: var(--dark-green);
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .product-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }

        .product-image {
            position: relative;
            height: 250px;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .product-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .product-card:hover .product-overlay {
            opacity: 1;
        }

        .quick-add-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: white;
            color: var(--primary-teal);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quick-add-btn:hover {
            background: var(--primary-teal);
            color: white;
            transform: scale(1.05);
        }

        .product-badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: var(--gradient-primary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .product-content {
            padding: 1.5rem;
        }

        .product-content h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
        }

        .product-content p {
            color: var(--text-gray);
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-orange);
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .product-rating i {
            color: var(--primary-yellow);
            font-size: 0.8rem;
        }

        .rating-text {
            font-size: 0.8rem;
            color: var(--text-gray);
            margin-left: 0.25rem;
        }

        .section-cta {
            text-align: center;
        }

        /* Gallery Grid */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .gallery-item {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .gallery-item.large {
            grid-row: span 2;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.05);
        }

        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(13, 148, 136, 0.8) 0%, rgba(4, 120, 87, 0.8) 100%);
            display: flex;
            align-items: flex-end;
            padding: 1.5rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }

        .gallery-content {
            color: white;
        }

        .gallery-content h4 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .gallery-content p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* About Section */
        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .about-text p {
            color: var(--text-gray);
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }

        .about-features {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin: 2rem 0;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--dark-gray);
            font-weight: 500;
        }

        .feature-item i {
            color: var(--primary-teal);
            font-size: 1.2rem;
        }

        .about-visual {
            position: relative;
        }

        .about-image-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            grid-template-rows: 2fr 1fr;
            gap: 1rem;
            height: 500px;
        }

        .about-image {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
        }

        .about-image:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .about-image.main {
            grid-row: span 2;
        }

        .about-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Footer */
        .footer {
            background: var(--dark-gray);
            color: white;
            padding: 4rem 0 2rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.5fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .footer-section h3 {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: white;
        }

        .footer-section.main p {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
            margin: 1rem 0 2rem;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 0.75rem;
        }

        .footer-section ul li a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-section ul li a:hover {
            color: var(--primary-teal);
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .contact-item i {
            color: var(--primary-teal);
            width: 16px;
        }

        .social-links {
            display: flex;
            gap: 1rem;
        }

        .social-link {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: var(--primary-teal);
            color: white;
            transform: translateY(-2px);
        }

        .footer-bottom {
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: rgba(255, 255, 255, 0.6);
        }

        .footer-links {
            display: flex;
            gap: 2rem;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--primary-teal);
        }

        /* Animations */
        .fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease forwards;
        }

        .delay-1 { animation-delay: 0.2s; }
        .delay-2 { animation-delay: 0.4s; }
        .delay-3 { animation-delay: 0.6s; }
        .delay-4 { animation-delay: 0.8s; }

        .fade-in-right {
            opacity: 0;
            transform: translateX(50px);
            animation: fadeInRight 1s ease forwards;
            animation-delay: 1s;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInRight {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .hero-content,
            .about-content {
                grid-template-columns: 1fr;
                gap: 3rem;
                text-align: center;
            }

            .about-image-grid {
                height: 400px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .footer-content {
                grid-template-columns: 1fr 1fr;
                gap: 2rem;
            }
        }

        @media (max-width: 768px) {
            .nav {
                display: none;
            }

            .mobile-menu-toggle {
                display: flex;
            }

            .hero {
                padding-top: 120px;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-buttons {
                justify-content: center;
            }

            .section-title {
                font-size: 2rem;
            }

            .services-grid,
            .products-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .gallery-grid {
                grid-template-columns: 1fr;
            }

            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .footer-bottom {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .about-image-grid {
                grid-template-columns: 1fr;
                grid-template-rows: auto auto auto;
                height: auto;
            }

            .about-image.main {
                grid-row: span 1;
            }

            .floating-card {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 1rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .btn {
                padding: 0.75rem 1.5rem;
                font-size: 0.9rem;
            }

            .service-card {
                margin: 0 1rem;
            }

            .product-card {
                margin: 0 1rem;
            }
        }
    </style>

    <script>
        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Animated counters
        function animateCounter(element, target) {
            let current = 0;
            const increment = target / 100;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current) + (target > 100 ? '+' : '');
            }, 20);
        }

        // Intersection Observer
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    if (entry.target.classList.contains('stats-section')) {
                        const statNumbers = entry.target.querySelectorAll('.stat-number');
                        statNumbers.forEach(stat => {
                            const target = parseInt(stat.getAttribute('data-target'));
                            animateCounter(stat, target);
                        });
                    }

                    if (entry.target.classList.contains('service-card') ||
                        entry.target.classList.contains('product-card') ||
                        entry.target.classList.contains('gallery-item')) {
                        entry.target.style.opacity = '0';
                        entry.target.style.transform = 'translateY(30px)';
                        entry.target.style.transition = 'all 0.6s ease';
                        
                        setTimeout(() => {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                        }, 100);
                    }
                }
            });
        }, observerOptions);

        // Initialize observers
        document.addEventListener('DOMContentLoaded', function() {
            const statsSection = document.querySelector('.stats-section');
            if (statsSection) {
                observer.observe(statsSection);
            }

            document.querySelectorAll('.service-card, .product-card, .gallery-item').forEach(card => {
                observer.observe(card);
            });

            // Header scroll effect
            const header = document.querySelector('.header');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 100) {
                    header.style.background = 'rgba(255, 255, 255, 0.98)';
                    header.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.1)';
                } else {
                    header.style.background = 'rgba(255, 255, 255, 0.95)';
                    header.style.boxShadow = 'none';
                }
            });

            // Dropdown functionality
            const dropdown = document.querySelector('.dropdown');
            const dropdownBtn = document.querySelector('.dropdown-btn');
            const dropdownContent = document.querySelector('.dropdown-content');

            if (window.innerWidth <= 768) {
                dropdownBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    dropdownContent.style.display = dropdownContent.style.display === 'block' ? 'none' : 'block';
                });

                document.addEventListener('click', function(e) {
                    if (!dropdown.contains(e.target)) {
                        dropdownContent.style.display = 'none';
                    }
                });
            }

            // Mobile menu toggle
            const mobileToggle = document.querySelector('.mobile-menu-toggle');
            const nav = document.querySelector('.nav');

            mobileToggle.addEventListener('click', function() {
                nav.classList.toggle('active');
            });

            // Scroll indicator
            const scrollIndicator = document.querySelector('.scroll-arrow');
            if (scrollIndicator) {
                scrollIndicator.addEventListener('click', function() {
                    document.querySelector('.stats-section').scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            }

            // Quick add to cart functionality
            document.querySelectorAll('.quick-add-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const productId = this.getAttribute('data-product-id');
                    console.log('Adding product to cart:', productId);
                    
                    // Visual feedback
                    const originalContent = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-check"></i><span>Agregado</span>';
                    this.style.background = 'var(--primary-teal)';
                    this.style.color = 'white';
                    
                    setTimeout(() => {
                        this.innerHTML = originalContent;
                        this.style.background = '';
                        this.style.color = '';
                    }, 2000);
                });
            });

            // Parallax effect for hero patterns
            window.addEventListener('scroll', function() {
                const scrolled = window.pageYOffset;
                const rate = scrolled * -0.5;
                
                document.querySelectorAll('.pattern-circle').forEach((pattern, index) => {
                    const speed = (index + 1) * 0.3;
                    pattern.style.transform = `translateY(${rate * speed}px)`;
                });
            });

            // Gallery lightbox effect (simple implementation)
            document.querySelectorAll('.gallery-item').forEach(item => {
                item.addEventListener('click', function() {
                    const img = this.querySelector('img');
                    if (img) {
                        // Simple lightbox implementation
                        const lightbox = document.createElement('div');
                        lightbox.className = 'lightbox';
                        lightbox.innerHTML = `
                            <div class="lightbox-content">
                                <img src="${img.src}" alt="${img.alt}">
                                <button class="lightbox-close">&times;</button>
                            </div>
                        `;
                        document.body.appendChild(lightbox);
                        
                        lightbox.addEventListener('click', function(e) {
                            if (e.target === lightbox || e.target.classList.contains('lightbox-close')) {
                                document.body.removeChild(lightbox);
                            }
                        });
                    }
                });
            });
        });

        // Add lightbox styles
        const lightboxStyles = `
            .lightbox {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.9);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
                opacity: 0;
                animation: fadeIn 0.3s ease forwards;
            }
            
            .lightbox-content {
                position: relative;
                max-width: 90%;
                max-height: 90%;
            }
            
            .lightbox-content img {
                width: 100%;
                height: auto;
                border-radius: 10px;
            }
            
            .lightbox-close {
                position: absolute;
                top: -40px;
                right: -40px;
                background: none;
                border: none;
                color: white;
                font-size: 2rem;
                cursor: pointer;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
            }
            
            .lightbox-close:hover {
                background: rgba(255, 255, 255, 0.2);
            }
        `;

        const styleSheet = document.createElement('style');
        styleSheet.textContent = lightboxStyles;
        document.head.appendChild(styleSheet);
    </script>
</body>

</html>
