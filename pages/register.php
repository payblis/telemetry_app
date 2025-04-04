<?php
/**
 * Page d'inscription
 */

// Si l'utilisateur est déjà connecté, rediriger vers le tableau de bord
if (isLoggedIn()) {
    redirect('index.php?page=dashboard');
}

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // Validation des champs
    $errors = [];
    
    if (empty($nom)) {
        $errors[] = 'Le nom est requis';
    }
    
    if (empty($prenom)) {
        $errors[] = 'Le prénom est requis';
    }
    
    if (empty($email)) {
        $errors[] = 'L\'adresse email est requise';
    } elseif (!validateEmail($email)) {
        $errors[] = 'L\'adresse email n\'est pas valide';
    } else {
        // Vérifier si l'email existe déjà
        $existingUser = getUserByEmail($email);
        if ($existingUser) {
            $errors[] = 'Cette adresse email est déjà utilisée';
        }
    }
    
    if (empty($password)) {
        $errors[] = 'Le mot de passe est requis';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Le mot de passe doit contenir au moins 6 caractères';
    }
    
    if ($password !== $password_confirm) {
        $errors[] = 'Les mots de passe ne correspondent pas';
    }
    
    // Si pas d'erreurs, créer l'utilisateur
    if (empty($errors)) {
        $userId = createUser($email, $password, $nom, $prenom);
        
        if ($userId) {
            // Inscription réussie, connecter l'utilisateur
            $user = getUserById($userId);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
            $_SESSION['user_role'] = $user['role'];
            
            // Rediriger vers le tableau de bord
            setFlashMessage('success', 'Inscription réussie. Bienvenue, ' . $_SESSION['user_name'] . ' !');
            redirect('index.php?page=dashboard');
        } else {
            // Échec de l'inscription
            $errors[] = 'Une erreur est survenue lors de l\'inscription';
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-user-plus"></i> Inscription</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="index.php?page=register">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nom" name="nom" value="<?= $nom ?? '' ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="prenom" name="prenom" value="<?= $prenom ?? '' ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= $email ?? '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">Le mot de passe doit contenir au moins 6 caractères.</div>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Confirmer le mot de passe</label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> S'inscrire
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                Vous avez déjà un compte ? <a href="index.php?page=login">Connectez-vous</a>
            </div>
        </div>
    </div>
</div>
