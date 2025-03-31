<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/database.php';

// Vérifier la connexion à la base de données
$conn = getDBConnection();

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: /telemoto/pilotes/index.php?error=1");
    exit;
}

$id = intval($_GET['id']);

// Récupérer les données du pilote
$sql = "SELECT * FROM pilotes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: /telemoto/pilotes/index.php?error=2");
    exit;
}

$pilote = $result->fetch_assoc();

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <h2 class="card-title">Détails du Pilote</h2>
    
    <div class="mb-3">
        <a href="/telemoto/pilotes/index.php" class="btn">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
        <a href="/telemoto/pilotes/edit.php?id=<?php echo $pilote['id']; ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> Modifier
        </a>
    </div>
    
    <div class="pilote-details">
        <div class="detail-group">
            <label>Nom:</label>
            <p><?php echo htmlspecialchars($pilote['nom']); ?></p>
        </div>
        
        <div class="detail-group">
            <label>Prénom:</label>
            <p><?php echo htmlspecialchars($pilote['prenom']); ?></p>
        </div>
        
        <div class="detail-group">
            <label>Taille:</label>
            <p><?php echo $pilote['taille'] ? htmlspecialchars($pilote['taille']) . ' m' : 'Non spécifié'; ?></p>
        </div>
        
        <div class="detail-group">
            <label>Poids:</label>
            <p><?php echo $pilote['poids'] ? htmlspecialchars($pilote['poids']) . ' kg' : 'Non spécifié'; ?></p>
        </div>
        
        <div class="detail-group">
            <label>Championnat:</label>
            <p><?php echo !empty($pilote['championnat']) ? htmlspecialchars($pilote['championnat']) : 'Non spécifié'; ?></p>
        </div>
        
        <div class="detail-group">
            <label>Date de création:</label>
            <p><?php echo date('d/m/Y H:i', strtotime($pilote['created_at'])); ?></p>
        </div>
        
        <div class="detail-group">
            <label>Dernière mise à jour:</label>
            <p><?php echo date('d/m/Y H:i', strtotime($pilote['updated_at'])); ?></p>
        </div>
    </div>
    
    <h3 class="mt-3 mb-2">Sessions récentes</h3>
    
    <?php
    // Récupérer les sessions récentes du pilote
    $sql = "SELECT s.*, m.marque, m.modele, c.nom as circuit_nom 
            FROM sessions s 
            JOIN motos m ON s.moto_id = m.id 
            JOIN circuits c ON s.circuit_id = c.id 
            WHERE s.pilote_id = ? 
            ORDER BY s.date DESC 
            LIMIT 5";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $sessions = $stmt->get_result();
    
    if ($sessions && $sessions->num_rows > 0) {
        echo '<table>
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
        
        while ($session = $sessions->fetch_assoc()) {
            echo '<tr>
                    <td>' . date('d/m/Y', strtotime($session['date'])) . '</td>
                    <td>' . ucfirst(str_replace('_', ' ', $session['type'])) . '</td>
                    <td>' . htmlspecialchars($session['marque'] . ' ' . $session['modele']) . '</td>
                    <td>' . htmlspecialchars($session['circuit_nom']) . '</td>
                    <td class="table-actions">
                        <a href="/telemoto/sessions/view.php?id=' . $session['id'] . '" class="btn btn-sm btn-view">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>';
        }
        
        echo '</tbody>
            </table>';
    } else {
        echo '<div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Aucune session récente pour ce pilote.
              </div>';
    }
    ?>
</div>

<style>
.pilote-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
}
.detail-group {
    margin-bottom: 1rem;
}
.detail-group label {
    font-weight: bold;
    color: var(--dark-gray);
    margin-bottom: 0.2rem;
}
.detail-group p {
    font-size: 1.1rem;
}
</style>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
