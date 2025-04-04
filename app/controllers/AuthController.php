<?php
/**
 * Contrôleur pour la gestion de l'authentification
 */
namespace App\Controllers;

use App\Models\UserModel;
use App\Utils\Validator;
use App\Utils\View;

class AuthController extends Controller {
    /**
     * Afficher la page d'inscription
     */
    public function register() {
        // Vérifier si l'utilisateur est déjà connecté
        if ($this->isLoggedIn()) {
            $this->redirect(BASE_URL . '/dashboard');
        }
        
        // Générer un jeton CSRF
        $csrfToken = $this->generateCsrfToken();
        
        // Afficher la vue
        $this->view('auth/register', [
            'csrf_token' => $csrfToken,
            'title' => 'Inscription'
        ]);
    }
    
    /**
     * Traiter l'inscription
     */
    public function processRegister() {
        // Vérifier si l'utilisateur est déjà connecté
        if ($this->isLoggedIn()) {
            $this->redirect(BASE_URL . '/dashboard');
        }
        
        // Vérifier le jeton CSRF
        if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
            View::addNotification('error', 'Erreur de sécurité. Veuillez réessayer.');
            $this->redirect(BASE_URL . '/register');
        }
        
        // Récupérer les données du formulaire
        $data = [
            'username' => $this->post('username'),
            'email' => $this->post('email'),
            'password' => $this->post('password'),
            'password_confirm' => $this->post('password_confirm'),
            'consent_community' => $this->post('consent_community') ? 1 : 0,
            'consent_data_collection' => $this->post('consent_data_collection') ? 1 : 0
        ];
        
        // Règles de validation
        $rules = [
            'username' => 'required|min:3|max:50',
            'email' => 'required|email|max:100',
            'password' => 'required|min:8|max:255',
            'password_confirm' => 'required|matches:password'
        ];
        
        // Messages personnalisés
        $messages = [
            'username.required' => 'Le nom d\'utilisateur est obligatoire.',
            'username.min' => 'Le nom d\'utilisateur doit contenir au moins 3 caractères.',
            'username.max' => 'Le nom d\'utilisateur ne doit pas dépasser 50 caractères.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
            'email.max' => 'L\'adresse email ne doit pas dépasser 100 caractères.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.max' => 'Le mot de passe ne doit pas dépasser 255 caractères.',
            'password_confirm.required' => 'La confirmation du mot de passe est obligatoire.',
            'password_confirm.matches' => 'Les mots de passe ne correspondent pas.'
        ];
        
        // Valider les données
        if (!Validator::validate($data, $rules, $messages)) {
            // Stocker les erreurs en session
            $_SESSION['form_errors'] = Validator::getErrors();
            $_SESSION['form_data'] = $data;
            
            // Rediriger vers le formulaire
            $this->redirect(BASE_URL . '/register');
        }
        
        // Vérifier si le nom d'utilisateur existe déjà
        $userModel = new UserModel();
        if ($userModel->usernameExists($data['username'])) {
            $_SESSION['form_errors'] = ['username' => ['Ce nom d\'utilisateur est déjà utilisé.']];
            $_SESSION['form_data'] = $data;
            $this->redirect(BASE_URL . '/register');
        }
        
        // Vérifier si l'email existe déjà
        if ($userModel->emailExists($data['email'])) {
            $_SESSION['form_errors'] = ['email' => ['Cette adresse email est déjà utilisée.']];
            $_SESSION['form_data'] = $data;
            $this->redirect(BASE_URL . '/register');
        }
        
        // Préparer les données pour l'insertion
        $userData = [
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'user',
            'active' => 1,
            'consent_community' => $data['consent_community'],
            'consent_data_collection' => $data['consent_data_collection']
        ];
        
        // Créer l'utilisateur
        $userId = $userModel->create($userData);
        
        if ($userId) {
            // Créer les préférences par défaut
            $userModel->updatePreferences($userId, [
                'theme' => 'default',
                'language' => 'fr',
                'notifications_enabled' => 1,
                'telemetry_frequency' => 10,
                'video_quality' => 'high',
                'data_sync_wifi_only' => 0
            ]);
            
            // Connecter l'utilisateur
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $userData['username'];
            $_SESSION['role'] = $userData['role'];
            
            // Ajouter un message de succès
            View::addNotification('success', 'Inscription réussie ! Bienvenue sur la plateforme de télémétrie moto.');
            
            // Rediriger vers le tableau de bord
            $this->redirect(BASE_URL . '/dashboard');
        } else {
            // Erreur lors de la création de l'utilisateur
            View::addNotification('error', 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.');
            $this->redirect(BASE_URL . '/register');
        }
    }
    
    /**
     * Afficher la page de connexion
     */
    public function login() {
        // Vérifier si l'utilisateur est déjà connecté
        if ($this->isLoggedIn()) {
            $this->redirect(BASE_URL . '/dashboard');
        }
        
        // Générer un jeton CSRF
        $csrfToken = $this->generateCsrfToken();
        
        // Afficher la vue
        $this->view('auth/login', [
            'csrf_token' => $csrfToken,
            'title' => 'Connexion'
        ]);
    }
    
    /**
     * Traiter la connexion
     */
    public function processLogin() {
        // Vérifier si l'utilisateur est déjà connecté
        if ($this->isLoggedIn()) {
            $this->redirect(BASE_URL . '/dashboard');
        }
        
        // Vérifier le jeton CSRF
        if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
            View::addNotification('error', 'Erreur de sécurité. Veuillez réessayer.');
            $this->redirect(BASE_URL . '/login');
        }
        
        // Récupérer les données du formulaire
        $username = $this->post('username');
        $password = $this->post('password');
        $remember = $this->post('remember') ? true : false;
        
        // Valider les données
        if (empty($username) || empty($password)) {
            View::addNotification('error', 'Veuillez remplir tous les champs.');
            $_SESSION['form_data'] = ['username' => $username];
            $this->redirect(BASE_URL . '/login');
        }
        
        // Authentifier l'utilisateur
        $userModel = new UserModel();
        $user = $userModel->authenticate($username, $password);
        
        if ($user) {
            // Connecter l'utilisateur
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Définir un cookie de connexion automatique si demandé
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expires = time() + (86400 * 30); // 30 jours
                
                // Stocker le token en base de données
                $stmt = $userModel->db->prepare("INSERT INTO api_tokens (user_id, token, name, expires_at) VALUES (:user_id, :token, 'remember_token', :expires_at)");
                $stmt->execute([
                    'user_id' => $user['id'],
                    'token' => $token,
                    'expires_at' => date('Y-m-d H:i:s', $expires)
                ]);
                
                // Définir le cookie
                setcookie('remember_token', $token, $expires, '/', '', false, true);
            }
            
            // Ajouter un message de succès
            View::addNotification('success', 'Connexion réussie. Bienvenue, ' . $user['username'] . ' !');
            
            // Rediriger vers le tableau de bord
            $this->redirect(BASE_URL . '/dashboard');
        } else {
            // Échec de l'authentification
            View::addNotification('error', 'Identifiants incorrects. Veuillez réessayer.');
            $_SESSION['form_data'] = ['username' => $username];
            $this->redirect(BASE_URL . '/login');
        }
    }
    
    /**
     * Déconnexion
     */
    public function logout() {
        // Supprimer le cookie de connexion automatique
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            
            // Supprimer le token de la base de données
            $userModel = new UserModel();
            $stmt = $userModel->db->prepare("DELETE FROM api_tokens WHERE token = :token AND name = 'remember_token'");
            $stmt->execute(['token' => $token]);
            
            // Supprimer le cookie
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }
        
        // Détruire la session
        session_destroy();
        
        // Rediriger vers la page d'accueil
        $this->redirect(BASE_URL);
    }
    
    /**
     * Afficher la page de mot de passe oublié
     */
    public function forgotPassword() {
        // Vérifier si l'utilisateur est déjà connecté
        if ($this->isLoggedIn()) {
            $this->redirect(BASE_URL . '/dashboard');
        }
        
        // Générer un jeton CSRF
        $csrfToken = $this->generateCsrfToken();
        
        // Afficher la vue
        $this->view('auth/forgot_password', [
            'csrf_token' => $csrfToken,
            'title' => 'Mot de passe oublié'
        ]);
    }
    
    /**
     * Traiter la demande de réinitialisation de mot de passe
     */
    public function processForgotPassword() {
        // Vérifier si l'utilisateur est déjà connecté
        if ($this->isLoggedIn()) {
            $this->redirect(BASE_URL . '/dashboard');
        }
        
        // Vérifier le jeton CSRF
        if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
            View::addNotification('error', 'Erreur de sécurité. Veuillez réessayer.');
            $this->redirect(BASE_URL . '/forgot-password');
        }
        
        // Récupérer l'email
        $email = $this->post('email');
        
        // Valider l'email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            View::addNotification('error', 'Veuillez fournir une adresse email valide.');
            $_SESSION['form_data'] = ['email' => $email];
            $this->redirect(BASE_URL . '/forgot-password');
        }
        
        // Créer un jeton de réinitialisation
        $userModel = new UserModel();
        $token = $userModel->createPasswordResetToken($email);
        
        if ($token) {
            // Envoyer un email avec le lien de réinitialisation
            $resetLink = BASE_URL . '/reset-password/' . $token;
            
            // TODO: Implémenter l'envoi d'email
            // Pour l'instant, afficher le lien dans la notification
            View::addNotification('success', 'Un email a été envoyé avec les instructions pour réinitialiser votre mot de passe. Lien de réinitialisation : ' . $resetLink);
        } else {
            // Ne pas indiquer si l'email existe ou non pour des raisons de sécurité
            View::addNotification('success', 'Si cette adresse email est associée à un compte, un email a été envoyé avec les instructions pour réinitialiser votre mot de passe.');
        }
        
        $this->redirect(BASE_URL . '/login');
    }
    
    /**
     * Afficher la page de réinitialisation de mot de passe
     * 
     * @param string $token Jeton de réinitialisation
     */
    public function resetPassword($token) {
        // Vérifier si l'utilisateur est déjà connecté
        if ($this->isLoggedIn()) {
            $this->redirect(BASE_URL . '/dashboard');
        }
        
        // Vérifier le jeton
        $userModel = new UserModel();
        $email = $userModel->verifyPasswordResetToken($token);
        
        if (!$email) {
            View::addNotification('error', 'Le lien de réinitialisation est invalide ou a expiré.');
            $this->redirect(BASE_URL . '/forgot-password');
        }
        
        // Générer un jeton CSRF
        $csrfToken = $this->generateCsrfToken();
        
        // Afficher la vue
        $this->view('auth/reset_password', [
            'csrf_token' => $csrfToken,
            'token' => $token,
            'title' => 'Réinitialisation du mot de passe'
        ]);
    }
    
    /**
     * Traiter la réinitialisation de mot de passe
     */
    public function processResetPassword() {
        // Vérifier si l'utilisateur est déjà connecté
        if ($this->isLoggedIn()) {
            $this->redirect(BASE_URL . '/dashboard');
        }
        
        // Vérifier le jeton CSRF
        if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
            View::addNotification('error', 'Erreur de sécurité. Veuillez réessayer.');
            $this->redirect(BASE_URL . '/forgot-password');
        }
        
        // Récupérer les données du formulaire
        $token = $this->post('token');
        $password = $this->post('password');
        $passwordConfirm = $this->post('password_confirm');
        
        // Valider les données
        if (empty($password) || strlen($password) < 8) {
            View::addNotification('error', 'Le mot de passe doit contenir au moins 8 caractères.');
            $this->redirect(BASE_URL . '/reset-password/' . $token);
        }
        
        if ($password !== $passwordConfirm) {
            View::addNotification('error', 'Les mots de passe ne correspondent pas.');
            $this->redirect(BASE_URL . '/reset-password/' . $token);
        }
        
        // Réinitialiser le mot de passe
        $userModel = new UserModel();
        $result = $userModel->resetPassword($token, $password);
        
        if ($result) {
            View::addNotification('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.');
            $this->redirect(BASE_URL . '/login');
        } else {
            View::addNotification('error', 'Une erreur est survenue lors de la réinitialisation du mot de passe. Veuillez réessayer.');
            $this->redirect(BASE_URL . '/forgot-password');
        }
    }
    
    /**
     * Vérifier la connexion automatique
     */
    public function checkRememberToken() {
        // Vérifier si l'utilisateur est déjà connecté
        if ($this->isLoggedIn()) {
            return true;
        }
        
        // Vérifier si un cookie de connexion automatique existe
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            
            // Vérifier le token
            $userModel = new UserModel();
            $stmt = $userModel->db->prepare("
                SELECT u.* FROM users u
                JOIN api_tokens t ON u.id = t.user_id
                WHERE t.token = :token AND t.name = 'remember_token' AND t.expires_at > NOW() AND u.active = 1
            ");
            $stmt->execute(['token' => $token]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Connecter l'utilisateur
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // Mettre à jour la dernière connexion
                $userModel->updateLastLogin($user['id']);
                
                // Mettre à jour la dernière utilisation du token
                $stmt = $userModel->db->prepare("UPDATE api_tokens SET last_used_at = NOW() WHERE token = :token");
                $stmt->execute(['token' => $token]);
                
                return true;
            }
            
            // Supprimer le cookie si le token est invalide
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }
        
        return false;
    }
}
