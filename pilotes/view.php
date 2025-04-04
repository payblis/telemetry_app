<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/config.php';

// Vérifier la connexion à la base de données
$conn = getDBConnection();

// Vérifier si l'ID est fourni
if (!isset($_GET['id'])) {
    header("Location: " . url('pilotes/index.php?error=1'));
    exit;
}

$id = intval($_GET['id']);

// Récupérer les informations du pilote
$sql = "SELECT * FROM pilotes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: " . url('pilotes/index.php?error=2'));
    exit;
}

$pilote = $result->fetch_assoc();

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?php echo htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom']); ?></h2>
        <div class="card-actions">
            <a href="<?php echo url('pilotes/index.php'); ?>" class="btn">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <a href="<?php echo url('pilotes/edit.php?id=' . $id); ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Modifier
            </a>
        </div>
    </div>

    <div class="card-body">
        <div class="info-grid">
            <div class="info-item">
                <label>Taille</label>
                <span><?php echo $pilote['taille'] ? htmlspecialchars($pilote['taille']) . ' m' : '-'; ?></span>
            </div>
            
            <div class="info-item">
                <label>Poids</label>
                <span><?php echo $pilote['poids'] ? htmlspecialchars($pilote['poids']) . ' kg' : '-'; ?></span>
            </div>
            
            <div class="info-item">
                <label>Championnat</label>
                <span><?php echo htmlspecialchars($pilote['championnat']); ?></span>
            </div>
        </div>
    </div>

    <div class="card-section">
        <h3>Sessions récentes</h3>
        <?php
        // Récupérer les sessions du pilote
        $sql = "SELECT s.*, m.marque as moto_marque, m.modele as moto_modele, c.nom as circuit_nom 
                FROM sessions s 
                LEFT JOIN motos m ON s.moto_id = m.id 
                LEFT JOIN circuits c ON s.circuit_id = c.id 
                WHERE s.pilote_id = ? 
                ORDER BY s.date DESC 
                LIMIT 5";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            echo '<div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Moto</th>
                                <th>Circuit</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>';
            
            while ($session = $result->fetch_assoc()) {
                echo '<tr>
                        <td>' . date('d/m/Y', strtotime($session['date'])) . '</td>
                        <td>' . htmlspecialchars($session['type']) . '</td>
                        <td>' . htmlspecialchars($session['moto_marque'] . ' ' . $session['moto_modele']) . '</td>
                        <td>' . htmlspecialchars($session['circuit_nom']) . '</td>
                        <td class="table-actions">
                            <a href="' . url('sessions/view.php?id=' . $session['id']) . '" class="btn btn-sm btn-view">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>';
            }
            
            echo '</tbody>
                </table>
            </div>';
        } else {
            echo '<div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Aucune session n\'a été trouvée pour ce pilote.
                  </div>';
        }
        ?>
    </div>
</div>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
