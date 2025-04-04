<?php
/**
 * Vue pour la page de mot de passe oublié
 */
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Mot de passe oublié</h2>
            <p>Entrez votre adresse email pour réinitialiser votre mot de passe</p>
        </div>

        <?php \App\Utils\View::showNotifications(); ?>

        <form action="<?= \App\Utils\View::url('/forgot-password') ?>" method="post" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            
            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" value="<?= isset($_SESSION['form_data']['email']) ? \App\Utils\View::escape($_SESSION['form_data']['email']) : '' ?>" required autofocus>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-block">Réinitialiser le mot de passe</button>
            </div>
        </form>
        
        <div class="auth-footer">
            <p><a href="<?= \App\Utils\View::url('/login') ?>">Retour à la connexion</a></p>
        </div>
    </div>
</div>

<?php
// Nettoyer les données de formulaire en session
unset($_SESSION['form_data']);
?>
