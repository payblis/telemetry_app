<?php
// Vérifier si l'utilisateur est connecté
$isLoggedIn = isset($_SESSION['user_id']);
?>

<div class="container text-center py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="display-1 text-muted">404</h1>
            <h2 class="mb-4">Page non trouvée</h2>
            <p class="lead mb-4">La page que vous recherchez n'existe pas ou a été déplacée.</p>
            
            <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
                <?php if ($isLoggedIn): ?>
                    <a href="index.php?page=dashboard" class="btn btn-primary btn-lg px-4 gap-3">
                        <i class="bi bi-speedometer2"></i> Retour au tableau de bord
                    </a>
                <?php else: ?>
                    <a href="index.php?page=login" class="btn btn-primary btn-lg px-4 gap-3">
                        <i class="bi bi-box-arrow-in-right"></i> Se connecter
                    </a>
                <?php endif; ?>
                
                <a href="javascript:history.back()" class="btn btn-outline-secondary btn-lg px-4">
                    <i class="bi bi-arrow-left"></i> Retour à la page précédente
                </a>
            </div>
            
            <div class="mt-5">
                <p class="text-muted">
                    Si vous pensez qu'il s'agit d'une erreur, veuillez contacter l'administrateur.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
.display-1 {
    font-size: 8rem;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 1rem;
}

.lead {
    font-size: 1.25rem;
    font-weight: 300;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
}

.bi {
    margin-right: 0.5rem;
}
</style> 