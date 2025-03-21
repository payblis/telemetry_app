<?php
class AuthController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function login() {
        $error = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            try {
                $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['password'])) {
                    // Connexion réussie
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    
                    // Redirection vers le tableau de bord
                    header('Location: index.php?route=dashboard');
                    exit;
                } else {
                    $error = 'Identifiants invalides';
                }
            } catch (PDOException $e) {
                $error = 'Erreur de connexion à la base de données';
            }
        }
        
        // Afficher la vue de connexion
        $pageTitle = "Connexion - Télémétrie IA";
        require_once APP_PATH . 'views/templates/header.php';
        require_once APP_PATH . 'views/auth/login.php';
        require_once APP_PATH . 'views/templates/footer.php';
    }

    public function logout() {
        // Destruction de la session
        session_destroy();
        // Redirection vers la page de connexion
        header('Location: index.php?route=login');
        exit;
    }
} 