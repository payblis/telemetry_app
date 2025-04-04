<?php
/**
 * Page de gestion des pilotes
 */

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    redirect('index.php?page=login');
}

// Récupérer l'ID de l'utilisateur
$userId = getCurrentUserId();

// Récupérer tous les pilotes de l'utilisateur
$pilotes = getPilotesByUserId($userId);
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="display-5">
            <i class="fas fa-user-astronaut"></i> Mes pilotes
        </h1>
        <p class="lead">Gérez vos profils de pilotes pour vos sessions de télémétrie.</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="index.php?page=pilotes_create" class="btn btn-primary btn-lg">
            <i class="fas fa-plus"></i> Ajouter un pilote
        </a>
    </div>
</div>

<div class="card shadow">
    <div class="card-body">
        <?php if (empty($pilotes)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Vous n'avez pas encore de pilotes enregistrés.
                <a href="index.php?page=pilotes_create" class="alert-link">Créez votre premier pilote</a>.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Catégorie</th>
                            <th>Niveau</th>
                            <th>Sessions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pilotes as $pilote): ?>
                            <tr>
                                <td><?= escape($pilote['nom']) ?></td>
                                <td><?= escape($pilote['prenom']) ?></td>
                                <td><?= escape($pilote['categorie'] ?? 'Non spécifié') ?></td>
                                <td>
                                    <?php
                                    $niveauClass = 'bg-secondary';
                                    if ($pilote['niveau'] === 'debutant') {
                                        $niveauClass = 'bg-success';
                                    } elseif ($pilote['niveau'] === 'intermediaire') {
                                        $niveauClass = 'bg-info';
                                    } elseif ($pilote['niveau'] === 'avance') {
                                        $niveauClass = 'bg-warning';
                                    } elseif ($pilote['niveau'] === 'expert') {
                                        $niveauClass = 'bg-danger';
                                    }
                                    ?>
                                    <span class="badge <?= $niveauClass ?>">
                                        <?= ucfirst(escape($pilote['niveau'] ?? 'Non spécifié')) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    // Compter le nombre de sessions pour ce pilote
                                    $nbSessions = count(query("SELECT id FROM sessions WHERE pilote_id = ?", [$pilote['id']]));
                                    echo $nbSessions;
                                    ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="index.php?page=pilotes_view&id=<?= $pilote['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="index.php?page=pilotes_edit&id=<?= $pilote['id'] ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="index.php?page=pilotes_delete&id=<?= $pilote['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce pilote ?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
