<?php include 'includes/header.php'; ?>

<div class="content-container">
    <div class="page-header">
        <h2>Gestion des sessions</h2>
        <a href="index.php?page=session_add" class="btn btn-primary">Ajouter une session</a>
    </div>
    
    <div class="search-container">
        <form action="index.php" method="get" class="search-form">
            <input type="hidden" name="page" value="sessions">
            <div class="form-group">
                <div class="row">
                    <div class="col-33">
                        <select name="session_type">
                            <option value="">Tous les types</option>
                            <option value="RACE" <?php echo (isset($_GET['session_type']) && $_GET['session_type'] === 'RACE') ? 'selected' : ''; ?>>Course</option>
                            <option value="QUALIFYING" <?php echo (isset($_GET['session_type']) && $_GET['session_type'] === 'QUALIFYING') ? 'selected' : ''; ?>>Qualification</option>
                            <option value="PRACTICE" <?php echo (isset($_GET['session_type']) && $_GET['session_type'] === 'PRACTICE') ? 'selected' : ''; ?>>Entraînement libre</option>
                            <option value="TRAINING" <?php echo (isset($_GET['session_type']) && $_GET['session_type'] === 'TRAINING') ? 'selected' : ''; ?>>Entraînement</option>
                            <option value="TRACK_DAY" <?php echo (isset($_GET['session_type']) && $_GET['session_type'] === 'TRACK_DAY') ? 'selected' : ''; ?>>Track Day</option>
                        </select>
                    </div>
                    <div class="col-33">
                        <input type="text" name="search" placeholder="Rechercher..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                    </div>
                    <div class="col-33">
                        <button type="submit" class="btn btn-secondary">Filtrer</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <div class="card">
        <div class="card-header">
            Liste des sessions
        </div>
        <div class="card-body">
            <?php if (empty($sessions)): ?>
                <p>Aucune session trouvée.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Circuit</th>
                            <th>Pilote</th>
                            <th>Moto</th>
                            <th>Conditions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessions as $session): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($session['date'])); ?></td>
                            <td>
                                <?php 
                                    switch($session['session_type']) {
                                        case 'RACE': echo 'Course'; break;
                                        case 'QUALIFYING': echo 'Qualification'; break;
                                        case 'PRACTICE': echo 'Entraînement libre'; break;
                                        case 'TRAINING': echo 'Entraînement'; break;
                                        case 'TRACK_DAY': echo 'Track Day'; break;
                                        default: echo $session['session_type'];
                                    }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($session['circuit_name']); ?></td>
                            <td><?php echo htmlspecialchars($session['pilot_name']); ?></td>
                            <td><?php echo htmlspecialchars($session['moto_brand'] . ' ' . $session['moto_model']); ?></td>
                            <td><?php echo htmlspecialchars($session['weather'] ?? ''); ?></td>
                            <td>
                                <a href="index.php?page=session_details&id=<?php echo $session['id']; ?>" class="btn btn-secondary">Détails</a>
                                <a href="index.php?page=session_edit&id=<?php echo $session['id']; ?>" class="btn btn-secondary">Modifier</a>
                                <a href="index.php?page=session_delete&id=<?php echo $session['id']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette session ?');">Supprimer</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
