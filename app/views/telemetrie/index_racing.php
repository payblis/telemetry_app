<?php
/**
 * Vue pour l'affichage des sessions de télémétrie avec le thème racing
 */
?>

<div class="telemetrie-container">
    <div class="page-header">
        <h1>Sessions de Télémétrie</h1>
        <div class="page-actions">
            <a href="<?= \App\Utils\View::url('/telemetrie/create') ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouvelle Session
            </a>
            <a href="<?= \App\Utils\View::url('/telemetrie/import') ?>" class="btn btn-secondary">
                <i class="fas fa-file-import"></i> Importer des Données
            </a>
        </div>
    </div>

    <?php \App\Utils\View::showNotifications(); ?>

    <!-- Filtres de recherche -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Filtres</h3>
        </div>
        <div class="card-body">
            <form action="<?= \App\Utils\View::url('/telemetrie') ?>" method="get" class="form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="pilote_id">Pilote</label>
                        <select id="pilote_id" name="pilote_id" class="form-control">
                            <option value="">Tous les pilotes</option>
                            <?php foreach ($pilotes as $pilote): ?>
                                <option value="<?= $pilote['id'] ?>" <?= isset($_GET['pilote_id']) && $_GET['pilote_id'] == $pilote['id'] ? 'selected' : '' ?>>
                                    <?= \App\Utils\View::escape($pilote['nom'] . ' ' . $pilote['prenom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="circuit_id">Circuit</label>
                        <select id="circuit_id" name="circuit_id" class="form-control">
                            <option value="">Tous les circuits</option>
                            <?php foreach ($circuits as $circuit): ?>
                                <option value="<?= $circuit['id'] ?>" <?= isset($_GET['circuit_id']) && $_GET['circuit_id'] == $circuit['id'] ? 'selected' : '' ?>>
                                    <?= \App\Utils\View::escape($circuit['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date_debut">Date début</label>
                        <input type="date" id="date_debut" name="date_debut" class="form-control" value="<?= $_GET['date_debut'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="date_fin">Date fin</label>
                        <input type="date" id="date_fin" name="date_fin" class="form-control" value="<?= $_GET['date_fin'] ?? '' ?>">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                    <a href="<?= \App\Utils\View::url('/telemetrie') ?>" class="btn btn-outline">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des sessions -->
    <div class="card">
        <div class="card-header">
            <h3>Sessions de télémétrie</h3>
        </div>
        <div class="card-body">
            <?php if (empty($sessions)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-stopwatch"></i>
                    </div>
                    <h2>Aucune session trouvée</h2>
                    <p>Vous n'avez pas encore créé de session ou aucune session ne correspond à vos critères de recherche.</p>
                    <a href="<?= \App\Utils\View::url('/telemetrie/create') ?>" class="btn btn-primary">
                        Créer une Session
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Circuit</th>
                                <th>Pilote</th>
                                <th>Moto</th>
                                <th>Tours</th>
                                <th>Meilleur temps</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sessions as $session): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($session['date_session'])) ?></td>
                                    <td><?= \App\Utils\View::escape($session['circuit_nom']) ?></td>
                                    <td><?= \App\Utils\View::escape($session['pilote_nom'] . ' ' . $session['pilote_prenom']) ?></td>
                                    <td><?= \App\Utils\View::escape($session['moto_marque'] . ' ' . $session['moto_modele']) ?></td>
                                    <td><?= $session['nombre_tours'] ?></td>
                                    <td>
                                        <?php if ($session['meilleur_temps']): ?>
                                            <span class="lap-time"><?= gmdate('i:s.v', $session['meilleur_temps']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?= \App\Utils\View::url('/telemetrie/view/' . $session['id']) ?>" class="btn btn-sm btn-outline" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= \App\Utils\View::url('/telemetrie/edit/' . $session['id']) ?>" class="btn btn-sm btn-outline" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= \App\Utils\View::url('/telemetrie/delete/' . $session['id']) ?>" class="btn btn-sm btn-outline btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette session ?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($current_page > 1): ?>
                            <div class="pagination-item">
                                <a href="<?= \App\Utils\View::url('/telemetrie?page=' . ($current_page - 1) . $query_params) ?>" class="pagination-link">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <div class="pagination-item">
                                <a href="<?= \App\Utils\View::url('/telemetrie?page=' . $i . $query_params) ?>" class="pagination-link <?= $i == $current_page ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            </div>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages): ?>
                            <div class="pagination-item">
                                <a href="<?= \App\Utils\View::url('/telemetrie?page=' . ($current_page + 1) . $query_params) ?>" class="pagination-link">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
