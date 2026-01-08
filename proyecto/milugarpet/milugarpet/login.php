<?php
require_once 'config/database.php';
require_once 'config/session.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login_type = $_POST['login_type'] ?? 'cliente';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } else {
        if ($login_type === 'cliente') {
            $db = new Database();
            $db->query('SELECT * FROM usuarios WHERE (email = :username OR nombre = :username) AND rol = "cliente"');
            $db->bind(':username', $username);
            $user = $db->single(); 
            
            if ($user && $password === $user['contraseña']) {
                loginClient($user['id_usuario'], $user['nombre'], $user['email']);
                header('Location: index.php');
                exit;
            } else {
                $error = 'Credenciales incorrectas.';
            }
        } else {
            $mysql_role = verifyMySQLUser($username, $password);
            
            if ($mysql_role) {
                if (($login_type === 'admin' && $mysql_role === 'admin') || 
                    ($login_type === 'empleado' && ($mysql_role === 'empleado' || $mysql_role === 'admin'))) {
                    
                    loginMySQLUser($username, $password, $mysql_role);
                    
                    if ($mysql_role === 'admin') {
                        header('Location: admin/dashboard.php');
                    } else {
                        header('Location: empleado/dashboard.php');
                    }
                    exit;
                } else {
                    $error = 'No tienes permisos para acceder como ' . $login_type . '.';
                }
            } else {
                $error = 'Credenciales incorrectas o sin acceso a la base de datos.';
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
    <title>Iniciar Sesión - Mi Lugar Pet</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-paw"></i>
                    <span>Mi Lugar Pet</span>
                </div>
                <h2>Iniciar Sesión</h2>
            </div>
            <div class="login-type-selector">
                <button type="button" class="type-btn active" data-type="cliente">
                    <i class="fas fa-user"></i>
                    Cliente
                </button>
                <button type="button" class="type-btn" data-type="empleado">
                    <i class="fas fa-user-tie"></i>
                    Empleado
                </button>
                <button type="button" class="type-btn" data-type="admin">
                    <i class="fas fa-user-shield"></i>
                    Administrador
                </button>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form class="login-form" method="POST" action="">
                <input type="hidden" name="login_type" id="login_type" value="cliente">
                
                <div class="form-group">
                    <label for="username" id="username_label">Email o Nombre de Usuario</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" required 
                               placeholder="Ingresa tu email o nombre de usuario">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required 
                               placeholder="Ingresa tu contraseña">
                        <button type="button" class="toggle-password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="login-info" id="cliente-info">
                    <div class="info-box">
                        <i class="fas fa-info-circle"></i>
                        <p>Usa tu email y contraseña de cliente registrado</p>
                    </div>
                </div>

                <div class="login-info" id="empleado-info" style="display: none;">
                    <div class="info-box">
                        <i class="fas fa-database"></i>
                        <p>Usa tus credenciales de MySQL con acceso a veterinaria_db</p>
                    </div>
                </div>

                <div class="login-info" id="admin-info" style="display: none;">
                    <div class="info-box">
                        <i class="fas fa-shield-alt"></i>
                        <p>Usa credenciales de administrador MySQL (ej: root)</p>
                    </div>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Iniciar Sesión
                </button>
            </form>

            <div class="login-footer">
                <div class="register-link" id="register-section">
                    <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
                </div>
                <div class="back-link">
                    <a href="index.php">
                        <i class="fas fa-arrow-left"></i>
                        Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-teal) 0%, var(--primary-blue) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-card {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 3rem;
            width: 100%;
            max-width: 450px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 2rem;
            color: var(--primary-teal);
            margin-bottom: 1rem;
        }

        .login-header h2 {
            color: var(--dark-gray);
            margin: 0;
        }

        .login-type-selector {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            background: var(--light-gray);
            padding: 0.5rem;
            border-radius: 15px;
        }

        .type-btn {
            flex: 1;
            background: transparent;
            border: none;
            padding: 1rem 0.5rem;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.9rem;
            color: var(--text-gray);
        }

        .type-btn.active {
            background: var(--primary-teal);
            color: var(--white);
        }

        .type-btn:hover:not(.active) {
            background: var(--white);
            color: var(--primary-teal);
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

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-group i {
            position: absolute;
            left: 1rem;
            color: var(--text-gray);
            z-index: 1;
        }

        .input-group input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--primary-teal);
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            background: none;
            border: none;
            color: var(--text-gray);
            cursor: pointer;
            z-index: 1;
        }

        .login-info {
            margin-bottom: 1.5rem;
        }

        .info-box {
            background: var(--primary-blue);
            padding: 1rem;
            border-radius: 10px;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .info-box i {
            color: var(--primary-teal);
            margin-top: 0.25rem;
        }

        .info-box p {
            margin: 0;
            color: var(--text-gray);
            font-size: 0.9rem;
        }

        .info-box small {
            display: block;
            margin-top: 0.5rem;
            color: var(--primary-teal);
            font-weight: 500;
        }

        .login-btn {
            width: 100%;
            background: var(--primary-orange);
            color: var(--white);
            border: none;
            padding: 1rem;
            border-radius: 10px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 2rem;
        }

        .login-btn:hover {
            background: var(--primary-teal);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .login-footer {
            text-align: center;
        }

        .register-link {
            margin-bottom: 1rem;
        }

        .register-link a {
            color: var(--primary-teal);
            text-decoration: none;
            font-weight: 500;
        }

        .register-link a:hover {
            color: var(--primary-orange);
        }

        .back-link a {
            color: var(--text-gray);
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: var(--primary-teal);
        }

        @media (max-width: 768px) {
            .login-card {
                padding: 2rem;
                margin: 1rem;
            }

            .type-btn {
                font-size: 0.8rem;
                padding: 0.75rem 0.25rem;
            }
        }
    </style>

    <script>
        document.querySelectorAll('.type-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active'));
                
                this.classList.add('active');
                
                const type = this.dataset.type;
                document.getElementById('login_type').value = type;
                
                const usernameLabel = document.getElementById('username_label');
                const usernameInput = document.getElementById('username');
                const registerSection = document.getElementById('register-section');
                
                document.querySelectorAll('.login-info').forEach(info => {
                    info.style.display = 'none';
                });
                
                document.getElementById(type + '-info').style.display = 'block';
                
                if (type === 'cliente') {
                    usernameLabel.textContent = 'Email o Nombre de Usuario';
                    usernameInput.placeholder = 'Ingresa tu email o nombre de usuario';
                    registerSection.style.display = 'block';
                } else {
                    usernameLabel.textContent = 'Usuario MySQL';
                    usernameInput.placeholder = 'Ingresa tu usuario de MySQL';
                    registerSection.style.display = 'none';
                }
            });
        });

        document.querySelector('.toggle-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>