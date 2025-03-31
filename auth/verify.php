<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/config.php';

// Démarrer la session
session_start();

// Variables pour les messages d'erreur et de succès
$error = '';
$success = '';

// Vérifier si un token est fourni
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    
    // Connexion à la base de données
    $conn = getDBConnection();
    
    // Vérifier si le token existe et est valide
    $stmt = $conn->prepare("SELECT vt.utilisateur_id, vt.expires_at, u.email 
                           FROM verification_tokens vt 
                           JOIN utilisateurs u ON vt.utilisateur_id = u.id 
                           WHERE vt.token = ? AND vt.expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $user_id = $row['utilisateur_id'];
        
        // Activer le compte utilisateur
        $stmt = $conn->prepare("UPDATE utilisateurs SET email_verified = 1 WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            // Supprimer le token utilisé
            $stmt = $conn->prepare("DELETE FROM verification_tokens WHERE token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            
            $success = 'Votre compte a été vérifié avec succès ! Vous pouvez maintenant vous connecter.';
        } else {
            $error = 'Une erreur est survenue lors de la vérification de votre compte.';
        }
    } else {
        $error = 'Le lien de vérification est invalide ou a expiré.';
    }
    
    $stmt->close();
    $conn->close();
} else {
    $error = 'Aucun token de vérification fourni.';
}

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-container">
    <div class="card auth-card">
        <h2 class="card-title">Vérification du compte</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <div class="auth-links">
                <a href="<?php echo url('auth/login.php'); ?>">Retour à la connexion</a>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
            <div class="auth-links">
                <a href="<?php echo url('auth/login.php'); ?>" class="btn btn-primary">Se connecter</a>
            </div>
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

.auth-links {
    margin-top: 1.5rem;
    text-align: center;
}
</style>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
