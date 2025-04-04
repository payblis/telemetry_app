<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$page_title = 'Sessions';

// Récupération des sessions avec pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

try {
    // Récupération du nombre total de sessions
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM sessions s 
        JOIN pilotes p ON s.pilote_id = p.id 
        WHERE p.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $total_sessions = $stmt->fetchColumn();
    $total_pages = ceil($total_sessions / $per_page);
    
    // Récupération des sessions pour la page courante
    $stmt = $pdo->prepare("
        SELECT s.*, p.nom AS pilote_nom, p.prenom AS pilote_prenom, 
               m.marque, m.modele, c.nom AS circuit_nom, c.pays AS circuit_pays,
               (SELECT MIN(temps_tour) FROM laps WHERE session_id = s.id) as meilleur_temps
        FROM sessions s
        JOIN pilotes p ON s.pilote_id = p.id
        JOIN motos m ON s.moto_id = m.id
        JOIN circuits c ON s.circuit_id = c.id
        WHERE p.user_id = ?
        ORDER BY s.date_session DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$_SESSION['user_id'], $per_page, $offset]);
    $sessions = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des sessions: " . $e->getMessage());
    $_SESSION['flash_message'] = "Une erreur est survenue lors du chargement des sessions.";
    $_SESSION['flash_type'] = "danger";
}

require_once '../includes/header.php';
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Sessions</h1>
        <a href="add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouvelle session
        </a>
    </div>
    
    <?php if (empty($sessions)): ?>
        <div class="alert alert-info">
            Aucune session n'a été trouvée. <a href="add.php">Créer une nouvelle session</a>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Pilote</th>
                                <th>Moto</th>
                                <th>Circuit</th>
                                <th>Type</th>
                                <th>Meilleur temps</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sessions as $session): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($session['date_session'])); ?></td>
                                    <td><?php echo htmlspecialchars($session['pilote_prenom'] . ' ' . $session['pilote_nom']); ?></td>
                                    <td><?php echo htmlspecialchars($session['marque'] . ' ' . $session['modele']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($session['circuit_nom']); ?>
                                        <small class="text-muted">(<?php echo htmlspecialchars($session['circuit_pays']); ?>)</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($session['type_session']) {
                                                'free practice' => 'info',
                                                'qualification' => 'warning',
                                                'course' => 'danger',
                                                'trackday' => 'success',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo htmlspecialchars($session['type_session']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($session['meilleur_temps']): ?>
                                            <?php echo formatTime($session['meilleur_temps']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="view.php?id=<?php echo $session['id']; ?>" class="btn btn-sm btn-primary" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit.php?id=<?php echo $session['id']; ?>" class="btn btn-sm btn-secondary" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete.php?id=<?php echo $session['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               title="Supprimer"
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette session ?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Navigation des pages">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
