<?php
/**
 * Page 404 - Page non trouvée
 */
?>

<div class="row justify-content-center">
    <div class="col-md-8 text-center">
        <div class="error-container mt-5">
            <div class="racing-stripe"></div>
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle fa-5x text-danger"></i>
            </div>
            <h1 class="display-1 text-danger">404</h1>
            <h2 class="display-4 mb-4">Page non trouvée</h2>
            <p class="lead mb-4">
                La page que vous recherchez n'existe pas ou a été déplacée.
                Vérifiez l'URL ou retournez à la page d'accueil.
            </p>
            <a href="index.php" class="btn btn-primary btn-lg">
                <i class="fas fa-home"></i> Retour à l'accueil
            </a>
            <div class="racing-stripe mt-5"></div>
        </div>
    </div>
</div>

<style>
    .error-container {
        padding: 2rem;
    }
    
    .racing-stripe {
        height: 8px;
        width: 100%;
        background: linear-gradient(to right, #e30613 0%, #e30613 33%, #ffffff 33%, #ffffff 66%, #0066cc 66%, #0066cc 100%);
        margin-bottom: 2rem;
    }
    
    .error-icon {
        margin-bottom: 1.5rem;
    }
</style>
