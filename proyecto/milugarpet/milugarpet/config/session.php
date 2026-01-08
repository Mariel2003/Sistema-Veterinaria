<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']) || isset($_SESSION['mysql_user']);
}

function isClient() {
    return isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'cliente';
}

function isEmployee() {
    return isset($_SESSION['mysql_user']) && $_SESSION['mysql_role'] === 'empleado';
}

function isAdmin() {
    return isset($_SESSION['mysql_user']) && $_SESSION['mysql_role'] === 'admin';
}

function isMySQLUser() {
    return isset($_SESSION['mysql_user']);
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUserName() {
    if (isset($_SESSION['mysql_user'])) {
        return $_SESSION['mysql_user'];
    }
    return $_SESSION['user_name'] ?? 'Usuario';
}

function getCurrentMySQLUser() {
    return $_SESSION['mysql_user'] ?? null;
}

function getCurrentMySQLPassword() {
    return $_SESSION['mysql_password'] ?? null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireClient() {
    if (!isClient()) {
        header('Location: login.php');
        exit;
    }
}

function requireEmployee() {
    if (!isEmployee()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: login.php');
        exit;
    }
}

function requireMySQLUser() {
    if (!isMySQLUser()) {
        header('Location: login.php');
        exit;
    }
}

function loginClient($user_id, $user_name, $user_email) {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $user_name;
    $_SESSION['user_email'] = $user_email;
    $_SESSION['user_role'] = 'cliente';
}

function loginMySQLUser($mysql_user, $mysql_password, $role) {
    $_SESSION['mysql_user'] = $mysql_user;
    $_SESSION['mysql_password'] = $mysql_password;
    $_SESSION['mysql_role'] = $role;
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}

function verifyMySQLUser($username, $password) {
    try {
        $conexion = new mysqli("localhost", $username, $password);
        
        if ($conexion->connect_error) {
            return false;
        }
        
        $consulta = $conexion->query("SHOW DATABASES LIKE 'veterinaria_db'");
        $hasAccess = $consulta && $consulta->num_rows > 0;
        
        if (!$hasAccess) {
            $conexion->close();
            return false;
        }
        
        $role = 'empleado';
        
        if ($username === 'root') {
            $role = 'admin';
        } else {
            try {
                $consulta = $conexion->query("SELECT * FROM mysql.user WHERE User = '$username'");
                if ($consulta && $consulta->num_rows > 0) {
                    $user_info = $consulta->fetch_assoc();
                    
                    if ($user_info && ($user_info['Create_user_priv'] === 'Y' || $user_info['Super_priv'] === 'Y')) {
                        $role = 'admin';
                    }
                }
            } catch (Exception $e) {
            }
        }
        
        $conexion->close();
        return $role;
        
    } catch (Exception $e) {
        return false;
    }
}

function getMySQLConnection() {
    if (!isMySQLUser()) {
        throw new Exception('No hay usuario MySQL logueado');
    }
    
    try {
        $conexion = new mysqli("localhost", getCurrentMySQLUser(), getCurrentMySQLPassword(), "veterinaria_db");
        
        if ($conexion->connect_error) {
            throw new Exception('Error de conexión: ' . $conexion->connect_error);
        }
        
        return $conexion;
    } catch (Exception $e) {
        throw new Exception('Error de conexión: ' . $e->getMessage());
    }
}
?>