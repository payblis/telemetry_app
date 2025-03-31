<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/config.php';

// Démarrer la session
session_start();

// Variables pour les messages d'erreur et de succès
$error = '';
$success = '';
$email = '';

// Traitement du formulaire de récupération de mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer l'email du formulaire
    $email = trim($_POST['email'] ?? '');
    
    // Validation de l'email
    if (empty($email)) {
        $error = 'Veuillez entrer votre adresse email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } else {
        // Connexion à la base de données
        $conn = getDBConnection();
        
        // Vérifier si l'email existe
        $stmt = $conn->prepare("SELECT id, nom, prenom FROM utilisateurs WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Générer un token de réinitialisation
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Supprimer les anciens tokens pour cet utilisateur
            $stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE utilisateur_id = ?");
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();
            
            // Enregistrer le nouveau token
            $stmt = $conn->prepare("INSERT INTO password_reset_tokens (utilisateur_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user['id'], $token, $expires);
            
            if ($stmt->execute()) {
                // Envoyer l'email de réinitialisation
                $reset_url = url("auth/reset_password.php?token=$token");
                $to = $email;
                $subject = "TeleMoto - Réinitialisation de votre mot de passe";
                $message = "
                <html>
                <head>
                    <title>Réinitialisation de votre mot de passe TeleMoto</title>
                </head>
                <body>
                    <h2>Bonjour " . htmlspecialchars($user['prenom']) . " " . htmlspecialchars($user['nom']) . ",</h2>
                    <p>Vous avez demandé la réinitialisation de votre mot de passe. Veuillez cliquer sur le lien ci-dessous pour définir un nouveau mot de passe :</p>
                    <p><a href='$reset_url'>Réinitialiser mon mot de passe</a></p>
                    <p>Ce lien expirera dans 1 heure.</p>
                    <p>Si vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet email.</p>
                </body>
                </html>
                ";
                
                // En-têtes pour éviter les spams
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= "From: TeleMoto <noreply@telemoto.com>" . "\r\n";
                $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
                $headers .= "X-Priority: 1\r\n"; 
                $headers .= "X-MSMail-Priority: High\r\n"; 
                $headers .= "Importance: High\r\n";
                
                // Désactiver pour le développement, activer en production
                // mail($to, $subject, $message, $headers);
                
                $success = 'Un email de réinitialisation a été envoyé à votre adresse email. Veuillez vérifier votre boîte de réception.';
            } else {
                $error = 'Une erreur est survenue. Veuillez réessayer plus tard.';
            }
        } else {
            // Ne pas indiquer si l'email existe ou non pour des raisons de sécurité
            $success = 'Si cette adresse email est associée à un compte, un email de réinitialisation a été envoyé. Veuillez vérifier votre boîte de réception.';
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
        <h2 class="card-title">Mot de passe oublié</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo url('auth/forgot_password.php'); ?>" class="auth-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                <small class="form-text">Entrez l'adresse email associée à votre compte pour recevoir un lien de réinitialisation.</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Envoyer le lien de réinitialisation</button>
            </div>
            
            <div class="auth-links">
                <a href="<?php echo url('auth/login.php'); ?>">Retour à la connexion</a>
            </div>
        </form>
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
