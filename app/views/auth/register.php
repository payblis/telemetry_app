<?php
/**
 * Vue pour la page d'inscription
 */
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Inscription</h2>
            <p>Créez votre compte pour accéder à la plateforme de télémétrie moto</p>
        </div>

        <?php \App\Utils\View::showNotifications(); ?>

        <form action="<?= \App\Utils\View::url('/register') ?>" method="post" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            
            <div class="form-group <?= isset($_SESSION['form_errors']['username']) ? 'has-error' : '' ?>">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" value="<?= isset($_SESSION['form_data']['username']) ? \App\Utils\View::escape($_SESSION['form_data']['username']) : '' ?>" required>
                <?php if (isset($_SESSION['form_errors']['username'])): ?>
                    <div class="form-error"><?= \App\Utils\View::escape($_SESSION['form_errors']['username'][0]) ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group <?= isset($_SESSION['form_errors']['email']) ? 'has-error' : '' ?>">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" value="<?= isset($_SESSION['form_data']['email']) ? \App\Utils\View::escape($_SESSION['form_data']['email']) : '' ?>" required>
                <?php if (isset($_SESSION['form_errors']['email'])): ?>
                    <div class="form-error"><?= \App\Utils\View::escape($_SESSION['form_errors']['email'][0]) ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group <?= isset($_SESSION['form_errors']['password']) ? 'has-error' : '' ?>">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
                <?php if (isset($_SESSION['form_errors']['password'])): ?>
                    <div class="form-error"><?= \App\Utils\View::escape($_SESSION['form_errors']['password'][0]) ?></div>
                <?php endif; ?>
                <div class="form-help">Le mot de passe doit contenir au moins 8 caractères</div>
            </div>
            
            <div class="form-group <?= isset($_SESSION['form_errors']['password_confirm']) ? 'has-error' : '' ?>">
                <label for="password_confirm">Confirmer le mot de passe</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
                <?php if (isset($_SESSION['form_errors']['password_confirm'])): ?>
                    <div class="form-error"><?= \App\Utils\View::escape($_SESSION['form_errors']['password_confirm'][0]) ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group checkbox">
                <input type="checkbox" id="consent_community" name="consent_community" <?= isset($_SESSION['form_data']['consent_community']) && $_SESSION['form_data']['consent_community'] ? 'checked' : '' ?>>
                <label for="consent_community">J'accepte de partager mes données de manière anonyme avec la communauté pour améliorer les recommandations</label>
            </div>
            
            <div class="form-group checkbox">
                <input type="checkbox" id="consent_data_collection" name="consent_data_collection" <?= isset($_SESSION['form_data']['consent_data_collection']) && $_SESSION['form_data']['consent_data_collection'] ? 'checked' : '' ?>>
                <label for="consent_data_collection">J'accepte la collecte de données pour l'amélioration du service</label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-block">S'inscrire</button>
            </div>
        </form>
        
        <div class="auth-footer">
            <p>Vous avez déjà un compte ? <a href="<?= \App\Utils\View::url('/login') ?>">Connectez-vous</a></p>
        </div>
    </div>
</div>

<?php
// Nettoyer les données de formulaire et les erreurs en session
unset($_SESSION['form_data']);
unset($_SESSION['form_errors']);
?>
