<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/config.php';

// Démarrer la session
session_start();

// Variables pour les messages d'erreur et de succès
$error = '';
$success = '';
$token = '';

// Vérifier si un token est fourni
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    
    // Connexion à la base de données
    $conn = getDBConnection();
    
    // Vérifier si le token existe et est valide
    $stmt = $conn->prepare("SELECT prt.utilisateur_id, prt.expires_at, u.email 
                           FROM password_reset_tokens prt 
                           JOIN utilisateurs u ON prt.utilisateur_id = u.id 
                           WHERE prt.token = ? AND prt.expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result || $result->num_rows === 0) {
        $error = 'Le lien de réinitialisation est invalide ou a expiré.';
        $token = '';
    }
    
    $stmt->close();
    $conn->close();
} else {
    $error = 'Aucun token de réinitialisation fourni.';
}

// Traitement du formulaire de réinitialisation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($token)) {
    // Récupérer les données du formulaire
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // Validation des données
    if (empty($password) || empty($password_confirm)) {
        $error = 'Tous les champs sont obligatoires.';
    } elseif (strlen($password) < 8) {
        $error = 'Le mot de passe doit contenir au moins 8 caractères.';
    } elseif ($password !== $password_confirm) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        // Connexion à la base de données
        $conn = getDBConnection();
        
        // Vérifier si le token existe et est valide
        $stmt = $conn->prepare("SELECT utilisateur_id FROM password_reset_tokens WHERE token = ? AND expires_at > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $user_id = $row['utilisateur_id'];
            
            // Hacher le nouveau mot de passe
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Mettre à jour le mot de passe
            $stmt = $conn->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                // Supprimer le token utilisé
                $stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
                $stmt->bind_param("s", $token);
                $stmt->execute();
                
                $success = 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.';
                
                // Rediriger vers la page de connexion après 3 secondes
                header("refresh:3;url=" . url('auth/login.php'));
            } else {
                $error = 'Une erreur est survenue lors de la réinitialisation du mot de passe.';
            }
        } else {
            $error = 'Le lien de réinitialisation est invalide ou a expiré.';
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-container">
    <div class="card auth-card">
        <h2 class="card-title">Réinitialisation du mot de passe</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <?php if (empty($token)): ?>
                <div class="auth-links">
                    <a href="<?php echo url('auth/forgot_password.php'); ?>">Demander un nouveau lien de réinitialisation</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php elseif (!empty($token) && empty($error)): ?>
            <form method="POST" action="<?php echo url('auth/reset_password.php?token=' . htmlspecialchars($token)); ?>" class="auth-form">
                <div class="form-group">
                    <label for="password">Nouveau mot de passe</label>
                    <input type="password" id="password" name="password" required>
                    <small class="form-text">Le mot de passe doit contenir au moins 8 caractères.</small>
                </div>
                
                <div class="form-group">
                    <label for="password_confirm">Confirmer le nouveau mot de passe</label>
                    <input type="password" id="password_confirm" name="password_confirm" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Réinitialiser le mot de passe</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<style>
.auth-container {
    max-width: 500px;
    margin: 2rem auto;
}

.auth-card {
    padding: 2rem;
}

.auth-form .form-group {
    margin-bottom: 1.5rem;
}

.form-text {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.8rem;
    color: var(--dark-gray);
}

.auth-links {
    margin-top: 1.5rem;
    text-align: center;
    font-size: 0.9rem;
}
</style>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
