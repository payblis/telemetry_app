<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/config.php';

// Démarrer la session
session_start();

// Rediriger si déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: ' . url());
    exit;
}

// Variables pour les messages d'erreur et de succès
$error = '';
$success = '';

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // Validation des données
    if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($password_confirm)) {
        $error = 'Tous les champs sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } elseif (strlen($password) < 8) {
        $error = 'Le mot de passe doit contenir au moins 8 caractères.';
    } elseif ($password !== $password_confirm) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        // Connexion à la base de données
        $conn = getDBConnection();
        
        // Vérifier si l'email existe déjà
        $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $error = 'Cette adresse email est déjà utilisée.';
        } else {
            // Hacher le mot de passe
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Préparer la requête d'insertion
            $stmt = $conn->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, 'user')");
            $stmt->bind_param("ssss", $nom, $prenom, $email, $hashed_password);
            
            // Exécuter la requête
            if ($stmt->execute()) {
                // Générer un token de vérification
                $user_id = $conn->insert_id;
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                $stmt = $conn->prepare("INSERT INTO verification_tokens (utilisateur_id, token, expires_at) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $user_id, $token, $expires);
                $stmt->execute();
                
                // Envoyer l'email de vérification
                $verification_url = url("auth/verify.php?token=$token");
                $to = $email;
                $subject = "TeleMoto - Vérification de votre compte";
                $message = "
                <html>
                <head>
                    <title>Vérification de votre compte TeleMoto</title>
                </head>
                <body>
                    <h2>Bienvenue sur TeleMoto !</h2>
                    <p>Merci de vous être inscrit. Pour activer votre compte, veuillez cliquer sur le lien ci-dessous :</p>
                    <p><a href='$verification_url'>Vérifier mon compte</a></p>
                    <p>Ce lien expirera dans 24 heures.</p>
                    <p>Si vous n'avez pas créé de compte, veuillez ignorer cet email.</p>
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
                
                $success = 'Votre compte a été créé avec succès ! Veuillez vérifier votre email pour activer votre compte.';
                
                // Rediriger vers la page de connexion après 3 secondes
                header("refresh:3;url=" . url('auth/login.php'));
            } else {
                $error = 'Une erreur est survenue lors de la création du compte.';
            }
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
        <h2 class="card-title">Créer un compte</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php else: ?>
            <form method="POST" action="<?php echo url('auth/register.php'); ?>" class="auth-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($nom ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($prenom ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                    <small class="form-text">Le mot de passe doit contenir au moins 8 caractères.</small>
                </div>
                
                <div class="form-group">
                    <label for="password_confirm">Confirmer le mot de passe</label>
                    <input type="password" id="password_confirm" name="password_confirm" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">S'inscrire</button>
                </div>
                
                <div class="auth-links">
                    <span>Déjà inscrit ?</span>
                    <a href="<?php echo url('auth/login.php'); ?>">Se connecter</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<style>
.auth-container {
    max-width: 600px;
    margin: 2rem auto;
}

.auth-card {
    padding: 2rem;
}

.form-row {
    display: flex;
    gap: 1rem;
}

.form-row .form-group {
    flex: 1;
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
