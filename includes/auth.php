<?php
require_once 'config.php';

// Configuration de la session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
session_start();

// Fonction d'authentification
function authenticate($email, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['last_activity'] = time();
            return true;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Erreur d'authentification: " . $e->getMessage());
        return false;
    }
}

// Fonction de vérification de session
function check_auth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
    
    // Vérification de l'expiration de la session
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
        session_unset();
        session_destroy();
        header('Location: ' . APP_URL . '/login.php?expired=1');
        exit;
    }
    
    $_SESSION['last_activity'] = time();
}

// Fonction de vérification des rôles
function check_role($required_role) {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $required_role) {
        header('Location: ' . APP_URL . '/dashboard.php?error=unauthorized');
        exit;
    }
}

// Fonction de déconnexion
function logout() {
    session_unset();
    session_destroy();
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Fonction d'enregistrement
function register($email, $password, $role = 'user') {
    global $pdo;
    
    try {
        $hashed_password = password_hash($password, PASSWORD_HASH_ALGO, PASSWORD_HASH_OPTIONS);
        
        $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
        return $stmt->execute([$email, $hashed_password, $role]);
    } catch (PDOException $e) {
        error_log("Erreur d'enregistrement: " . $e->getMessage());
        return false;
    }
}

// Vérification de l'authentification pour les pages protégées
if (basename($_SERVER['PHP_SELF']) !== 'login.php' && 
    basename($_SERVER['PHP_SELF']) !== 'register.php' && 
    basename($_SERVER['PHP_SELF']) !== 'logout.php') {
    check_auth();
}
?>