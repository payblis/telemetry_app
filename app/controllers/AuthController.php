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
                // Debug des données reçues
                error_log("Tentative de connexion - Username: " . $username);
                
                $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Debug des résultats de la requête
                error_log("Résultat de la requête: " . ($user ? "Utilisateur trouvé" : "Utilisateur non trouvé"));
                
                if ($user) {
                    error_log("Vérification du mot de passe pour l'utilisateur: " . $user['username']);
                    if (password_verify($password, $user['password'])) {
                        // Connexion réussie
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role'] = $user['role'];
                        
                        error_log("Connexion réussie pour l'utilisateur: " . $user['username']);
                        
                        // Redirection vers le tableau de bord
                        header('Location: index.php?route=dashboard');
                        exit;
                    } else {
                        error_log("Échec de la vérification du mot de passe pour l'utilisateur: " . $user['username']);
                        $error = 'Identifiants invalides';
                    }
                } else {
                    error_log("Aucun utilisateur trouvé avec le nom d'utilisateur: " . $username);
                    $error = 'Identifiants invalides';
                }
            } catch (PDOException $e) {
                error_log("Erreur PDO: " . $e->getMessage());
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