<?php
/**
 * Vue pour la page de réinitialisation de mot de passe
 */
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Réinitialisation du mot de passe</h2>
            <p>Veuillez choisir un nouveau mot de passe</p>
        </div>

        <?php \App\Utils\View::showNotifications(); ?>

        <form action="<?= \App\Utils\View::url('/reset-password') ?>" method="post" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="token" value="<?= $token ?>">
            
            <div class="form-group">
                <label for="password">Nouveau mot de passe</label>
                <input type="password" id="password" name="password" required autofocus>
                <div class="form-help">Le mot de passe doit contenir au moins 8 caractères</div>
            </div>
            
            <div class="form-group">
                <label for="password_confirm">Confirmer le mot de passe</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
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
