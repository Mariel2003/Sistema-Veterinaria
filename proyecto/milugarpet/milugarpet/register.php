<?php
require_once 'config/database.php';
require_once 'config/session.php';

$db = new Database();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    
    if (empty($nombre) || empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos obligatorios.';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        $db->query('SELECT id_usuario FROM usuarios WHERE email = :email');
        $db->bind(':email', $email);
        $existing_user = $db->single();
        
        if ($existing_user) {
            $error = 'Este email ya está registrado.';
        } else {
            $hashed_password = $password;
            
            $db->query('INSERT INTO usuarios (nombre, email, contraseña, telefono, direccion, rol) VALUES (:nombre, :email, :password, :telefono, :direccion, :rol)');
            $db->bind(':nombre', $nombre);
            $db->bind(':email', $email);
            $db->bind(':password', $hashed_password);
            $db->bind(':telefono', $telefono);
            $db->bind(':direccion', $direccion);
            $db->bind(':rol', 'cliente');
            
            if ($db->execute()) {
                $success = 'Cuenta creada exitosamente. Ya puedes iniciar sesión.';
                $nombre = $email = $telefono = $direccion = '';
            } else {
                $error = 'Error al crear la cuenta. Inténtalo de nuevo.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse - Mi Lugar Pet</title>
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
                    <a href="consultas.php" class="nav-link">Consultas</a>
                    <a href="login.php" class="nav-link login-btn">Iniciar Sesión</a>
                </nav>
            </div>
        </div>
    </header>

    <section class="register-section">
        <div class="container">
            <div class="register-container">
                <div class="register-form-container">
                    <div class="register-header">
                        <div class="register-logo">
                            <i class="fas fa-paw"></i>
                        </div>
                        <h2>Crear Cuenta</h2>
                        <p>Únete a nuestra familia de amantes de las mascotas</p>
                    </div>

                    <form class="register-form" method="POST" action="">
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

                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre">Nombre Completo *</label>
                                <div class="input-container">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="nombre" name="nombre" placeholder="Tu nombre completo" required value="<?php echo htmlspecialchars($nombre ?? ''); ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="email">Email *</label>
                                <div class="input-container">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email" id="email" name="email" placeholder="tu@email.com" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Contraseña *</label>
                                <div class="input-container">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="confirm_password">Confirmar Contraseña *</label>
                                <div class="input-container">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Repite tu contraseña" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <div class="input-container">
                                    <i class="fas fa-phone"></i>
                                    <input type="tel" id="telefono" name="telefono" placeholder="Tu número de teléfono" value="<?php echo htmlspecialchars($telefono ?? ''); ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="direccion">Dirección</label>
                                <div class="input-container">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <input type="text" id="direccion" name="direccion" placeholder="Tu dirección" value="<?php echo htmlspecialchars($direccion ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-container">
                                <input type="checkbox" name="terms" required>
                                <span class="checkmark"></span>
                                Acepto los <a href="#" class="terms-link">términos y condiciones</a>
                            </label>
                        </div>

                        <button type="submit" class="register-btn-submit">
                            <i class="fas fa-user-plus"></i>
                            Crear Cuenta
                        </button>
                    </form>

                    <div class="register-footer">
                        <p>¿Ya tienes una cuenta? <a href="login.php" class="login-link">Inicia sesión aquí</a></p>
                    </div>
                </div>

                <div class="register-image">
                    <img src="/placeholder.svg?height=600&width=500" alt="Mascotas felices">
                    <div class="image-overlay">
                        <h3>¡Bienvenido a la familia!</h3>
                        <p>Más de 500 mascotas felices ya confían en nosotros</p>
                    </div>
                </div>
            </div>
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
        .register-section {
            padding: 120px 0 80px;
            background: var(--light-gray);
            min-height: 100vh;
        }

        .register-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: start;
            max-width: 1200px;
            margin: 0 auto;
        }

        .register-form-container {
            background: var(--white);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .register-logo {
            font-size: 3rem;
            color: var(--primary-orange);
            margin-bottom: 1rem;
        }

        .register-header h2 {
            color: var(--primary-teal);
            margin-bottom: 0.5rem;
            font-size: 2rem;
        }

        .register-header p {
            color: var(--text-gray);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
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

        .input-container {
            position: relative;
        }

        .input-container i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-gray);
        }

        .input-container input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .input-container input:focus {
            outline: none;
            border-color: var(--primary-teal);
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .checkbox-container input {
            margin-right: 0.5rem;
        }

        .terms-link {
            color: var(--primary-teal);
            text-decoration: none;
        }

        .terms-link:hover {
            color: var(--primary-orange);
        }

        .register-btn-submit {
            width: 100%;
            background: var(--primary-teal);
            color: var(--white);
            border: none;
            padding: 1rem;
            font-size: 1.1rem;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 2rem;
        }

        .register-btn-submit:hover {
            background: var(--primary-orange);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .register-footer {
            text-align: center;
        }

        .login-link {
            color: var(--primary-teal);
            text-decoration: none;
            font-weight: bold;
        }

        .login-link:hover {
            color: var(--primary-orange);
        }

        .register-image {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .register-image img {
            width: 100%;
            height: 600px;
            object-fit: cover;
        }

        .image-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .image-overlay h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        @media (max-width: 768px) {
            .register-container {
                grid-template-columns: 1fr;
            }
            
            .register-form-container {
                padding: 2rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>