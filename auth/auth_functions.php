<?php
// Fonctions d'authentification pour TeleMoto

/**
 * Vérifie si l'utilisateur est connecté
 * 
 * @return bool True si l'utilisateur est connecté, false sinon
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur a un rôle spécifique
 * 
 * @param string $role Le rôle à vérifier ('admin', 'expert', 'user')
 * @return bool True si l'utilisateur a le rôle spécifié, false sinon
 */
function hasRole($role) {
    if (!isLoggedIn()) {
        return false;
    }
    
    return $_SESSION['user_role'] === $role;
}

/**
 * Vérifie si l'utilisateur est un administrateur
 * 
 * @return bool True si l'utilisateur est un administrateur, false sinon
 */
function isAdmin() {
    return hasRole('admin');
}

/**
 * Vérifie si l'utilisateur est un expert
 * 
 * @return bool True si l'utilisateur est un expert, false sinon
 */
function isExpert() {
    return hasRole('expert') || hasRole('admin');
}

/**
 * Redirige l'utilisateur s'il n'est pas connecté
 * 
 * @param string $redirect_url URL de redirection si l'utilisateur n'est pas connecté
 * @return void
 */
function requireLogin($redirect_url = null) {
    if (!isLoggedIn()) {
        if ($redirect_url === null) {
            $redirect_url = url('auth/login.php');
        }
        
        header('Location: ' . $redirect_url);
        exit;
    }
}

/**
 * Redirige l'utilisateur s'il n'a pas le rôle d'administrateur
 * 
 * @param string $redirect_url URL de redirection si l'utilisateur n'est pas administrateur
 * @return void
 */
function requireAdmin($redirect_url = null) {
    requireLogin();
    
    if (!isAdmin()) {
        if ($redirect_url === null) {
            $redirect_url = url();
        }
        
        header('Location: ' . $redirect_url);
        exit;
    }
}

/**
 * Redirige l'utilisateur s'il n'a pas le rôle d'expert
 * 
 * @param string $redirect_url URL de redirection si l'utilisateur n'est pas expert
 * @return void
 */
function requireExpert($redirect_url = null) {
    requireLogin();
    
    if (!isExpert()) {
        if ($redirect_url === null) {
            $redirect_url = url();
        }
        
        header('Location: ' . $redirect_url);
        exit;
    }
}

/**
 * Génère un menu utilisateur en fonction du statut de connexion
 * 
 * @return string HTML du menu utilisateur
 */
function getUserMenu() {
    $html = '<div class="user-menu">';
    
    if (isLoggedIn()) {
        $html .= '<div class="dropdown">';
        $html .= '<button class="dropdown-toggle">';
        $html .= '<i class="fas fa-user-circle"></i> ' . htmlspecialchars($_SESSION['user_name']);
        $html .= '</button>';
        $html .= '<div class="dropdown-menu">';
        
        if (isAdmin()) {
            $html .= '<a href="' . url('admin/') . '"><i class="fas fa-cog"></i> Administration</a>';
        }
        
        if (isExpert()) {
            $html .= '<a href="' . url('experts/') . '"><i class="fas fa-user-tie"></i> Espace Expert</a>';
        }
        
        $html .= '<a href="' . url('profile/') . '"><i class="fas fa-id-card"></i> Mon Profil</a>';
        $html .= '<a href="' . url('auth/logout.php') . '"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>';
        $html .= '</div>';
        $html .= '</div>';
    } else {
        $html .= '<a href="' . url('auth/login.php') . '" class="login-btn">Connexion</a>';
        $html .= '<a href="' . url('auth/register.php') . '" class="register-btn">Inscription</a>';
    }
    
    $html .= '</div>';
    
    return $html;
}
?>
