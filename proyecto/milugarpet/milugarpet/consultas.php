<?php
require_once 'config/database.php';
require_once 'config/session.php';

$db = new Database();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isClient()) {
    $motivo = trim($_POST['motivo'] ?? '');
    $fecha_preferida = $_POST['fecha_preferida'] ?? '';
    $hora_preferida = $_POST['hora_preferida'] ?? '';
    $nombre_mascota = trim($_POST['nombre_mascota'] ?? '');
    $tipo_mascota = $_POST['tipo_mascota'] ?? '';
    $edad_mascota = trim($_POST['edad_mascota'] ?? '');
    
    if (empty($motivo) || empty($fecha_preferida) || empty($hora_preferida)) {
        $error = 'Por favor, completa todos los campos obligatorios.';
    } else {
        $foto_nombre = null;
        
        // Procesar la imagen si se subió
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (in_array($_FILES['foto']['type'], $allowed_types) && $_FILES['foto']['size'] <= $max_size) {
                $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $foto_nombre = uniqid() . '_' . time() . '.' . $extension;
                $upload_path = 'uploads/' . $foto_nombre;
                
                // Crear directorio si no existe
                if (!file_exists('uploads/')) {
                    mkdir('uploads/', 0777, true);
                }
                
                if (!move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                    $error = 'Error al subir la imagen.';
                    $foto_nombre = null;
                }
            } else {
                $error = 'La imagen debe ser JPG, PNG o GIF y no superar 5MB.';
            }
        }
        
        if (empty($error)) {
            $motivo_completo = "Mascota: $nombre_mascota ($tipo_mascota, $edad_mascota)\n";
            $motivo_completo .= "Fecha preferida: $fecha_preferida $hora_preferida\n";
            $motivo_completo .= "Motivo: $motivo";
            
            $db->query('INSERT INTO consultas (id_usuario, motivo, estado, foto) VALUES (:id_usuario, :motivo, :estado, :foto)');
            $db->bind(':id_usuario', getCurrentUserId());
            $db->bind(':motivo', $motivo_completo);
            $db->bind(':estado', 'pendiente');
            $db->bind(':foto', $foto_nombre);
            
            if ($db->execute()) {
                $success = '¡Consulta agendada exitosamente! Te contactaremos pronto para confirmar la cita.';
                $motivo = $fecha_preferida = $hora_preferida = $nombre_mascota = $tipo_mascota = $edad_mascota = '';
            } else {
                $error = 'Error al agendar la consulta. Inténtalo de nuevo.';
                // Eliminar imagen si falló la inserción
                if ($foto_nombre && file_exists('uploads/' . $foto_nombre)) {
                    unlink('uploads/' . $foto_nombre);
                }
            }
        }
    }
}

$mis_consultas = [];
if (isClient()) {
    $db->query('SELECT * FROM consultas WHERE id_usuario = :id_usuario ORDER BY fecha DESC');
    $db->bind(':id_usuario', getCurrentUserId());
    $mis_consultas = $db->resultset();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultas - Mi Lugar Pet</title>
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
                    <a href="productos.php" class="nav-link">Productos</a>
                    <a href="ver_consultas.php" class="nav-link">Ver consultas</a>
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
    <section class="consultation-hero">
        <div class="container">
            <h1>Consultas Veterinarias</h1>
            <p>Agenda una cita con nuestros especialistas</p>
        </div>
    </section>

    <?php if (!isLoggedIn()): ?>
        <section class="login-required">
            <div class="container">
                <div class="login-required-content">
                    <i class="fas fa-user-lock"></i>
                    <h2>Inicia sesión para agendar una consulta</h2>
                    <p>Necesitas tener una cuenta para poder agendar consultas veterinarias</p>
                    <div class="login-buttons">
                        <a href="login.php" class="btn-primary">Iniciar Sesión</a>
                        <a href="register.php" class="btn-secondary">Crear Cuenta</a>
                    </div>
                </div>
            </div>
        </section>
    <?php else: ?>
        <section class="appointment-section">
            <div class="container">
                <div class="appointment-content">
                    <div class="form-container">
                        <h2>Agendar Consulta</h2>
                        
                        <?php if ($error): ?>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="success-message">
                                <i class="fas fa-check-circle"></i>
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form class="appointment-form" method="POST" action="" enctype="multipart/form-data">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nombre_mascota">Nombre de la Mascota</label>
                                    <input type="text" id="nombre_mascota" name="nombre_mascota" required value="<?php echo htmlspecialchars($nombre_mascota ?? ''); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="tipo_mascota">Tipo de Mascota</label>
                                    <select id="tipo_mascota" name="tipo_mascota" required>
                                        <option value="">Seleccionar</option>
                                        <option value="perro" <?php echo ($tipo_mascota ?? '') === 'perro' ? 'selected' : ''; ?>>Perro</option>
                                        <option value="gato" <?php echo ($tipo_mascota ?? '') === 'gato' ? 'selected' : ''; ?>>Gato</option>
                                        <option value="ave" <?php echo ($tipo_mascota ?? '') === 'ave' ? 'selected' : ''; ?>>Ave</option>
                                        <option value="otro" <?php echo ($tipo_mascota ?? '') === 'otro' ? 'selected' : ''; ?>>Otro</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="edad_mascota">Edad de la Mascota</label>
                                <input type="text" id="edad_mascota" name="edad_mascota" placeholder="Ej: 2 años" value="<?php echo htmlspecialchars($edad_mascota ?? ''); ?>">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="fecha_preferida">Fecha Preferida</label>
                                    <input type="date" id="fecha_preferida" name="fecha_preferida" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($fecha_preferida ?? ''); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="hora_preferida">Hora Preferida</label>
                                    <select id="hora_preferida" name="hora_preferida" required>
                                        <option value="">Seleccionar</option>
                                        <option value="09:00" <?php echo ($hora_preferida ?? '') === '09:00' ? 'selected' : ''; ?>>9:00 AM</option>
                                        <option value="10:00" <?php echo ($hora_preferida ?? '') === '10:00' ? 'selected' : ''; ?>>10:00 AM</option>
                                        <option value="11:00" <?php echo ($hora_preferida ?? '') === '11:00' ? 'selected' : ''; ?>>11:00 AM</option>
                                        <option value="14:00" <?php echo ($hora_preferida ?? '') === '14:00' ? 'selected' : ''; ?>>2:00 PM</option>
                                        <option value="15:00" <?php echo ($hora_preferida ?? '') === '15:00' ? 'selected' : ''; ?>>3:00 PM</option>
                                        <option value="16:00" <?php echo ($hora_preferida ?? '') === '16:00' ? 'selected' : ''; ?>>4:00 PM</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="motivo">Motivo de la Consulta</label>
                                <textarea id="motivo" name="motivo" rows="4" placeholder="Describe brevemente el motivo de la consulta..." required><?php echo htmlspecialchars($motivo ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="foto">Foto de la Mascota (Opcional)</label>
                                <input type="file" id="foto" name="foto" accept="image/*" class="file-input">
                                <div class="file-input-info">
                                    <i class="fas fa-camera"></i>
                                    <span>Sube una foto de tu mascota (JPG, PNG, GIF - Máx. 5MB)</span>
                                </div>
                                <div id="image-preview" class="image-preview"></div>
                            </div>

                            <button type="submit" class="submit-btn">
                                <i class="fas fa-calendar-check"></i>
                                Agendar Consulta
                            </button>
                        </form>
                    </div>

                    <div class="info-container">
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h3>Horarios de Atención</h3>
                            <p>Lunes a Viernes: 9:00 AM - 6:00 PM<br>
                               Sábados: 9:00 AM - 2:00 PM<br>
                               Emergencias: 24/7</p>
                        </div>

                        <div class="info-card">
                            <div class="info-icon">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <h3>Nuestros Especialistas</h3>
                            <p>Contamos con veterinarios especializados en diferentes áreas para brindar el mejor cuidado a tu mascota.</p>
                        </div>

                        <div class="info-card">
                            <div class="info-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <h3>Cuidado Integral</h3>
                            <p>Desde consultas preventivas hasta tratamientos especializados, cuidamos la salud integral de tu mascota.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php if (!empty($mis_consultas)): ?>
        <section class="my-consultations">
            <div class="container">
                <h2>Mis Consultas</h2>
                <div class="consultations-grid">
                    <?php foreach($mis_consultas as $consulta): ?>
                        <div class="consultation-card">
                            <div class="consultation-header">
                                <div class="consultation-date">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($consulta['fecha'])); ?>
                                </div>
                                <div class="consultation-status <?php echo $consulta['estado']; ?>">
                                    <?php echo ucfirst($consulta['estado']); ?>
                                </div>
                            </div>
                            <div class="consultation-content">
                                <?php if (!empty($consulta['foto'])): ?>
                                    <div class="consultation-image">
                                        <img src="uploads/<?php echo htmlspecialchars($consulta['foto']); ?>" 
                                             alt="Foto de la mascota" 
                                             class="pet-photo">
                                    </div>
                                <?php endif; ?>
                                <p><?php echo nl2br(htmlspecialchars($consulta['motivo'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
    <?php endif; ?>

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
                        <p><i class="fas fa-phone"></i> +1 234 567 8900</p>
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

        .consultation-hero {
            background: linear-gradient(135deg, var(--primary-teal) 0%, var(--primary-blue) 100%);
            padding: 120px 0 60px;
            text-align: center;
            color: var(--white);
        }

        .consultation-hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .login-required {
            padding: 80px 0;
            background: var(--light-gray);
            text-align: center;
        }

        .login-required-content i {
            font-size: 4rem;
            color: var(--primary-teal);
            margin-bottom: 2rem;
        }

        .login-required-content h2 {
            color: var(--primary-teal);
            margin-bottom: 1rem;
        }

        .login-required-content p {
            color: var(--text-gray);
            margin-bottom: 2rem;
        }

        .login-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn-primary {
            background: var(--primary-teal);
            color: var(--white);
            padding: 1rem 2rem;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--primary-orange);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--primary-teal);
            padding: 1rem 2rem;
            border-radius: 25px;
            text-decoration: none;
            border: 2px solid var(--primary-teal);
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: var(--primary-teal);
            color: var(--white);
        }

        .appointment-section {
            padding: 80px 0;
            background: var(--light-gray);
        }

        .appointment-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 4rem;
            align-items: start;
        }

        .form-container {
            background: var(--white);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .form-container h2 {
            color: var(--primary-teal);
            margin-bottom: 2rem;
            font-size: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark-gray);
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-teal);
        }

        .file-input {
            display: none;
        }

        .file-input-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border: 2px dashed #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .file-input-info:hover {
            border-color: var(--primary-teal);
            background: var(--light-gray);
        }

        .file-input-info i {
            font-size: 1.5rem;
            color: var(--primary-teal);
        }

        .image-preview {
            margin-top: 1rem;
            text-align: center;
        }

        .image-preview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .submit-btn {
            background: var(--primary-orange);
            color: var(--white);
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            justify-content: center;
        }

        .submit-btn:hover {
            background: var(--primary-teal);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
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

        .info-container {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .info-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }

        .info-icon {
            font-size: 3rem;
            color: var(--primary-orange);
            margin-bottom: 1rem;
        }

        .info-card h3 {
            color: var(--primary-teal);
            margin-bottom: 1rem;
        }

        .my-consultations {
            padding: 80px 0;
            background: var(--white);
        }

        .my-consultations h2 {
            color: var(--primary-teal);
            margin-bottom: 2rem;
            text-align: center;
        }

        .consultations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
        }

        .consultation-card {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .consultation-header {
            background: var(--light-gray);
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .consultation-date {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-gray);
            font-size: 0.9rem;
        }

        .consultation-status {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .consultation-status.pendiente {
            background: #fff3cd;
            color: #856404;
        }

        .consultation-status.finalizada {
            background: #d4edda;
            color: #155724;
        }

        .consultation-content {
            padding: 1.5rem;
        }

        .consultation-image {
            margin-bottom: 1rem;
            text-align: center;
        }

        .pet-photo {
            max-width: 150px;
            max-height: 150px;
            border-radius: 10px;
            object-fit: cover;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .consultation-content p {
            color: var(--text-gray);
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .user-menu {
                flex-direction: column;
                gap: 0.5rem;
            }

            .appointment-content {
                grid-template-columns: 1fr;
            }
            
            .form-container {
                padding: 2rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }

            .login-buttons {
                flex-direction: column;
                align-items: center;
            }

            .consultations-grid {
                grid-template-columns: 1fr;
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

        // Preview de imagen
        document.getElementById('foto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('image-preview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '';
            }
        });

        // Click en el área de archivo
        document.querySelector('.file-input-info').addEventListener('click', function() {
            document.getElementById('foto').click();
        });
    </script>
</body>
</html>