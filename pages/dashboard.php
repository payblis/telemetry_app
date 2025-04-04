<?php
/**
 * Page de tableau de bord
 */

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect('index.php?page=login');
}

// Récupérer les données de l'utilisateur
$userId = getCurrentUserId();
$user = getUserById($userId);

// Récupérer les statistiques
$pilotes = getPilotesByUserId($userId);
$motos = getMotosByUserId($userId);
$sessions = getSessionsByUserId($userId);
$circuits = getAllCircuits();

// Compter les éléments
$nbPilotes = count($pilotes);
$nbMotos = count($motos);
$nbSessions = count($sessions);
$nbCircuits = count($circuits);

// Récupérer les sessions récentes (5 dernières)
$sessionsRecentes = array_slice($sessions, 0, 5);
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1 class="display-5">
            <i class="fas fa-tachometer-alt"></i> Tableau de bord
        </h1>
        <p class="lead">Bienvenue sur votre espace de télémétrie moto, <?= escape(getCurrentUserName()) ?>.</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Sessions</h6>
                        <h2 class="mb-0"><?= $nbSessions ?></h2>
                    </div>
                    <i class="fas fa-stopwatch fa-3x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="index.php?page=sessions" class="text-white">Voir toutes les sessions <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Pilotes</h6>
                        <h2 class="mb-0"><?= $nbPilotes ?></h2>
                    </div>
                    <i class="fas fa-user-astronaut fa-3x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="index.php?page=pilotes" class="text-white">Gérer les pilotes <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Motos</h6>
                        <h2 class="mb-0"><?= $nbMotos ?></h2>
                    </div>
                    <i class="fas fa-motorcycle fa-3x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="index.php?page=motos" class="text-white">Gérer les motos <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Circuits</h6>
                        <h2 class="mb-0"><?= $nbCircuits ?></h2>
                    </div>
                    <i class="fas fa-road fa-3x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="index.php?page=circuits" class="text-white">Voir les circuits <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-history"></i> Sessions récentes</h5>
            </div>
            <div class="card-body">
                <?php if (empty($sessionsRecentes)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Vous n'avez pas encore de sessions enregistrées.
                        <a href="index.php?page=sessions_create" class="alert-link">Créer votre première session</a>.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Circuit</th>
                                    <th>Pilote</th>
                                    <th>Moto</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sessionsRecentes as $session): ?>
                                    <tr>
                                        <td><?= formatDate($session['date_session']) ?></td>
                                        <td><?= escape($session['circuit_nom']) ?></td>
                                        <td><?= escape($session['pilote_prenom'] . ' ' . $session['pilote_nom']) ?></td>
                                        <td><?= escape($session['moto_marque'] . ' ' . $session['moto_modele']) ?></td>
                                        <td>
                                            <a href="index.php?page=sessions_view&id=<?= $session['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="index.php?page=sessions_edit&id=<?= $session['id'] ?>" class="btn btn-sm btn-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <a href="index.php?page=sessions" class="btn btn-outline-primary">
                    <i class="fas fa-list"></i> Toutes les sessions
                </a>
                <a href="index.php?page=sessions_create" class="btn btn-primary float-end">
                    <i class="fas fa-plus"></i> Nouvelle session
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-bolt"></i> Actions rapides</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="index.php?page=sessions_create" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Nouvelle session
                    </a>
                    <a href="index.php?page=telemetrie_import" class="btn btn-success">
                        <i class="fas fa-file-import"></i> Importer des données Sensor Logger
                    </a>
                    <a href="index.php?page=pilotes_create" class="btn btn-info">
                        <i class="fas fa-user-plus"></i> Ajouter un pilote
                    </a>
                    <a href="index.php?page=motos_create" class="btn btn-danger">
                        <i class="fas fa-motorcycle"></i> Ajouter une moto
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card shadow mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Conseils</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> Utilisation de Sensor Logger</h6>
                    <p class="mb-0">Pour enregistrer des données télémétriques, utilisez l'application Sensor Logger sur votre smartphone et exportez les données au format JSON.</p>
                </div>
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle"></i> Positionnement du téléphone</h6>
                    <p class="mb-0">Pour des mesures précises, fixez solidement votre smartphone sur la moto dans une position stable.</p>
                </div>
            </div>
        </div>
    </div>
</div>
