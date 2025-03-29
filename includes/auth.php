<?php
/**
 * Fonctions d'authentification pour l'application
 */

/**
 * Vérifier si un utilisateur est connecté
 * @return bool Résultat de la vérification
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Obtenir l'ID de l'utilisateur connecté
 * @return int|null ID de l'utilisateur ou null
 */
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Obtenir le rôle de l'utilisateur connecté
 * @return string|null Rôle de l'utilisateur ou null
 */
function getCurrentUserRole() {
    return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
}

/**
 * Vérifier si l'utilisateur a un rôle spécifique
 * @param string|array $roles Rôle(s) à vérifier
 * @return bool Résultat de la vérification
 */
function hasRole($roles) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $userRole = getCurrentUserRole();
    
    if (is_array($roles)) {
        return in_array($userRole, $roles);
    }
    
    return $userRole === $roles;
}

/**
 * Vérifier si l'utilisateur est administrateur
 * @return bool Résultat de la vérification
 */
function isAdmin() {
    return hasRole('ADMIN');
}

/**
 * Vérifier si l'utilisateur est expert
 * @return bool Résultat de la vérification
 */
function isExpert() {
    return hasRole(['ADMIN', 'EXPERT']);
}

/**
 * Restreindre l'accès aux utilisateurs connectés
 * Redirige vers la page de connexion si non connecté
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setAlert('Vous devez être connecté pour accéder à cette page.', 'danger');
        redirect(BASE_URL . '/index.php?page=login');
    }
}

/**
 * Restreindre l'accès aux administrateurs
 * Redirige vers le tableau de bord si non administrateur
 */
function requireAdmin() {
    requireLogin();
    
    if (!isAdmin()) {
        setAlert('Vous n\'avez pas les droits pour accéder à cette page.', 'danger');
        redirect(BASE_URL . '/index.php?page=dashboard');
    }
}

/**
 * Restreindre l'accès aux experts
 * Redirige vers le tableau de bord si non expert
 */
function requireExpert() {
    requireLogin();
    
    if (!isExpert()) {
        setAlert('Vous n\'avez pas les droits pour accéder à cette page.', 'danger');
        redirect(BASE_URL . '/index.php?page=dashboard');
    }
}

/**
 * Connecter un utilisateur
 * @param int $userId ID de l'utilisateur
 * @param string $username Nom d'utilisateur
 * @param string $role Rôle de l'utilisateur
 */
function loginUser($userId, $username, $role) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['user_role'] = $role;
    
    // Régénérer l'ID de session pour éviter les attaques de fixation de session
    session_regenerate_id(true);
}

/**
 * Déconnecter un utilisateur
 */
function logoutUser() {
    // Détruire toutes les données de session
    $_SESSION = [];
    
    // Détruire le cookie de session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Détruire la session
    session_destroy();
}
