<?php
// Inclure le fichier de configuration
require_once __DIR__ . '/config/config.php';

// Inclure l'en-tête
include_once __DIR__ . '/includes/header.php';
?>

<div class="dashboard">
    <div class="welcome-section">
        <h1>Bienvenue sur TeleMoto</h1>
        <p class="subtitle">Application d'Assistance Technique Moto Racing avec Intégration IA</p>
        
        <div class="dashboard-description">
            <p>Cette application vous permet d'optimiser les réglages de votre moto en fonction du pilote, du circuit et des conditions spécifiques rencontrées.</p>
            <p>Utilisez l'intelligence artificielle pour obtenir des recommandations précises et améliorez vos performances sur piste.</p>
        </div>
    </div>
    
    <div class="dashboard-cards">
        <div class="card dashboard-card">
            <div class="card-icon"><i class="fas fa-user-helmet-safety"></i></div>
            <h3>Pilotes</h3>
            <p>Gérez les profils des pilotes avec leurs caractéristiques physiques et leur championnat.</p>
            <a href="<?php echo url('pilotes/'); ?>" class="btn">Accéder</a>
        </div>
        
        <div class="card dashboard-card">
            <div class="card-icon"><i class="fas fa-motorcycle"></i></div>
            <h3>Motos</h3>
            <p>Configurez vos motos avec leurs réglages standards et équipements spécifiques.</p>
            <a href="<?php echo url('motos/'); ?>" class="btn">Accéder</a>
        </div>
        
        <div class="card dashboard-card">
            <div class="card-icon"><i class="fas fa-road"></i></div>
            <h3>Circuits</h3>
            <p>Importez automatiquement les données des circuits via ChatGPT pour des analyses précises.</p>
            <a href="<?php echo url('circuits/'); ?>" class="btn">Accéder</a>
        </div>
        
        <div class="card dashboard-card highlight">
            <div class="card-icon"><i class="fas fa-stopwatch"></i></div>
            <h3>Sessions</h3>
            <p>Créez et gérez vos sessions d'entraînement, qualifications et courses avec tous les réglages techniques.</p>
            <a href="<?php echo url('sessions/'); ?>" class="btn btn-primary">Accéder</a>
        </div>
        
        <div class="card dashboard-card">
            <div class="card-icon"><i class="fas fa-robot"></i></div>
            <h3>ChatGPT</h3>
            <p>Obtenez des recommandations instantanées basées sur les données de vos sessions.</p>
            <a href="<?php echo url('chatgpt/'); ?>" class="btn">Accéder</a>
        </div>
        
        <div class="card dashboard-card">
            <div class="card-icon"><i class="fas fa-user-tie"></i></div>
            <h3>Experts</h3>
            <p>Consultez les experts techniques et contribuez à l'IA communautaire interne.</p>
            <a href="<?php echo url('experts/'); ?>" class="btn">Accéder</a>
        </div>
    </div>
</div>

<?php
// Inclure le pied de page
include_once __DIR__ . '/includes/footer.php';
?>
