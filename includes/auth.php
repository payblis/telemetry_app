<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

class Auth {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Authentification de l'utilisateur
    public function login($username, $password) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Vérifier si le compte n'est pas verrouillé
                if ($this->isAccountLocked($user['id'])) {
                    logLoginAttempt($username, false, $_SERVER['REMOTE_ADDR']);
                    return [
                        'success' => false,
                        'message' => 'Compte temporairement verrouillé. Veuillez réessayer plus tard.'
                    ];
                }
                
                // Réinitialiser les tentatives de connexion
                $this->resetLoginAttempts($user['id']);
                
                // Créer la session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                
                // Générer un nouveau token CSRF
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                
                logLoginAttempt($username, true, $_SERVER['REMOTE_ADDR']);
                logAccess($user['id'], 'login');
                
                return [
                    'success' => true,
                    'user' => $user
                ];
            }
            
            // Incrémenter les tentatives de connexion échouées
            $this->incrementLoginAttempts($username);
            logLoginAttempt($username, false, $_SERVER['REMOTE_ADDR']);
            
            return [
                'success' => false,
                'message' => 'Identifiants incorrects'
            ];
            
        } catch (PDOException $e) {
            logCustomError('Erreur lors de la connexion', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Une erreur est survenue'
            ];
        }
    }
    
    // Déconnexion de l'utilisateur
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            logAccess($_SESSION['user_id'], 'logout');
        }
        
        // Détruire toutes les variables de session
        $_SESSION = array();
        
        // Détruire le cookie de session
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-3600, '/');
        }
        
        // Détruire la session
        session_destroy();
    }
    
    // Vérifier si l'utilisateur est connecté
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    // Vérifier si l'utilisateur a un rôle spécifique
    public function hasRole($role) {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }
    
    // Vérifier si le compte est verrouillé
    private function isAccountLocked($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as attempts, MAX(attempt_time) as last_attempt
                FROM login_attempts
                WHERE user_id = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            
            return $result['attempts'] >= MAX_LOGIN_ATTEMPTS;
        } catch (PDOException $e) {
            logCustomError('Erreur lors de la vérification du verrouillage du compte', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    // Incrémenter les tentatives de connexion
    private function incrementLoginAttempts($username) {
        try {
            // Récupérer l'ID de l'utilisateur
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO login_attempts (user_id, attempt_time, ip_address)
                    VALUES (?, NOW(), ?)
                ");
                $stmt->execute([$user['id'], $_SERVER['REMOTE_ADDR']]);
            }
        } catch (PDOException $e) {
            logCustomError('Erreur lors de l\'incrémentation des tentatives de connexion', ['error' => $e->getMessage()]);
        }
    }
    
    // Réinitialiser les tentatives de connexion
    private function resetLoginAttempts($userId) {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM login_attempts
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            logCustomError('Erreur lors de la réinitialisation des tentatives de connexion', ['error' => $e->getMessage()]);
        }
    }
    
    // Vérifier et mettre à jour l'activité de la session
    public function checkSession() {
        if ($this->isLoggedIn()) {
            if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > LOGIN_TIMEOUT)) {
                $this->logout();
                return false;
            }
            $_SESSION['last_activity'] = time();
            return true;
        }
        return false;
    }
    
    // Changer le mot de passe
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            $stmt = $this->pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($currentPassword, $user['password'])) {
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
                
                $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $userId]);
                
                logAccess($userId, 'password_change');
                
                return [
                    'success' => true,
                    'message' => 'Mot de passe modifié avec succès'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Mot de passe actuel incorrect'
            ];
            
        } catch (PDOException $e) {
            logCustomError('Erreur lors du changement de mot de passe', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Une erreur est survenue'
            ];
        }
    }
    
    // Réinitialiser le mot de passe
    public function resetPassword($email) {
        try {
            $stmt = $this->pdo->prepare("SELECT id, username FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                $stmt = $this->pdo->prepare("
                    INSERT INTO password_resets (user_id, token, expiry)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$user['id'], $token, $expiry]);
                
                // Ici, vous pouvez ajouter le code pour envoyer l'email
                
                logAccess($user['id'], 'password_reset_request');
                
                return [
                    'success' => true,
                    'message' => 'Instructions envoyées par email'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Adresse email non trouvée'
            ];
            
        } catch (PDOException $e) {
            logCustomError('Erreur lors de la réinitialisation du mot de passe', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Une erreur est survenue'
            ];
        }
    }
}

// Créer une instance de la classe Auth
$auth = new Auth($pdo); 