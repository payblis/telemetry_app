<?php
/**
 * Vue pour la page de connexion
 */
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Connexion</h2>
            <p>Accédez à votre espace de télémétrie moto</p>
        </div>

        <?php \App\Utils\View::showNotifications(); ?>

        <form action="<?= \App\Utils\View::url('/login') ?>" method="post" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            
            <div class="form-group">
                <label for="username">Nom d'utilisateur ou Email</label>
                <input type="text" id="username" name="username" value="<?= isset($_SESSION['form_data']['username']) ? \App\Utils\View::escape($_SESSION['form_data']['username']) : '' ?>" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
                <div class="form-help">
                    <a href="<?= \App\Utils\View::url('/forgot-password') ?>">Mot de passe oublié ?</a>
                </div>
            </div>
            
            <div class="form-group checkbox">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Se souvenir de moi</label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
            </div>
        </form>
        
        <div class="auth-footer">
            <p>Vous n'avez pas de compte ? <a href="<?= \App\Utils\View::url('/register') ?>">Inscrivez-vous</a></p>
        </div>
    </div>
</div>

<?php
// Nettoyer les données de formulaire en session
unset($_SESSION['form_data']);
?>
