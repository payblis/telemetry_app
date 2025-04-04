<?php
/**
 * Page de connexion
 */

// Si l'utilisateur est déjà connecté, rediriger vers le tableau de bord
if (isLoggedIn()) {
    redirect('index.php?page=dashboard');
}

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validation des champs
    $errors = [];
    
    if (empty($email)) {
        $errors[] = 'L\'adresse email est requise';
    } elseif (!validateEmail($email)) {
        $errors[] = 'L\'adresse email n\'est pas valide';
    }
    
    if (empty($password)) {
        $errors[] = 'Le mot de passe est requis';
    }
    
    // Si pas d'erreurs, tenter la connexion
    if (empty($errors)) {
        $user = authenticateUser($email, $password);
        
        if ($user) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
            $_SESSION['user_role'] = $user['role'];
            
            // Si "Se souvenir de moi" est coché, créer un cookie
            if ($remember) {
                $token = generateToken();
                $expiry = time() + (30 * 24 * 60 * 60); // 30 jours
                
                // Stocker le token en base de données
                execute(
                    "UPDATE users SET remember_token = ?, remember_expiry = ? WHERE id = ?",
                    [$token, date('Y-m-d H:i:s', $expiry), $user['id']]
                );
                
                // Créer le cookie
                setcookie('remember_token', $token, $expiry, '/', '', false, true);
            }
            
            // Rediriger vers le tableau de bord
            setFlashMessage('success', 'Connexion réussie. Bienvenue, ' . $_SESSION['user_name'] . ' !');
            redirect('index.php?page=dashboard');
        } else {
            // Échec de la connexion
            $errors[] = 'Email ou mot de passe incorrect';
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-sign-in-alt"></i> Connexion</h4>
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
                
                <form method="POST" action="index.php?page=login">
                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= $email ?? '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Se souvenir de moi</label>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Se connecter
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="index.php?page=forgot_password">Mot de passe oublié ?</a>
                    <a href="index.php?page=register">Créer un compte</a>
                </div>
            </div>
        </div>
    </div>
</div>
