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

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation des données
    if (empty($email) || empty($password)) {
        $error = 'Tous les champs sont obligatoires.';
    } else {
        // Connexion à la base de données
        $conn = getDBConnection();
        
        // Préparer la requête
        $stmt = $conn->prepare("SELECT id, nom, prenom, email, mot_de_passe, role FROM utilisateurs WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Vérifier le mot de passe
            if (password_verify($password, $user['mot_de_passe'])) {
                // Connexion réussie, créer la session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Enregistrer la connexion dans les logs
                $ip = $_SERVER['REMOTE_ADDR'];
                $agent = $_SERVER['HTTP_USER_AGENT'];
                $stmt = $conn->prepare("INSERT INTO connexions_log (utilisateur_id, ip_address, user_agent) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $user['id'], $ip, $agent);
                $stmt->execute();
                
                // Rediriger vers la page d'accueil
                header('Location: ' . url());
                exit;
            } else {
                $error = 'Email ou mot de passe incorrect.';
            }
        } else {
            $error = 'Email ou mot de passe incorrect.';
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
        <h2 class="card-title">Connexion</h2>
        
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
        
        <form method="POST" action="<?php echo url('auth/login.php'); ?>" class="auth-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Se connecter</button>
            </div>
            
            <div class="auth-links">
                <a href="<?php echo url('auth/forgot_password.php'); ?>">Mot de passe oublié ?</a>
                <span class="separator">|</span>
                <a href="<?php echo url('auth/register.php'); ?>">Créer un compte</a>
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

.auth-links {
    margin-top: 1.5rem;
    text-align: center;
    font-size: 0.9rem;
}

.separator {
    margin: 0 0.5rem;
    color: var(--light-gray);
}
</style>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
