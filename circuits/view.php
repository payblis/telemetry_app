<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/database.php';

// Vérifier la connexion à la base de données
$conn = getDBConnection();

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: /telemoto/circuits/index.php?error=1");
    exit;
}

$id = intval($_GET['id']);

// Récupérer les données du circuit
$sql = "SELECT * FROM circuits WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: /telemoto/circuits/index.php?error=2");
    exit;
}

$circuit = $result->fetch_assoc();

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <h2 class="card-title">Détails du Circuit</h2>
    
    <div class="mb-3">
        <a href="/telemoto/circuits/index.php" class="btn">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
        <a href="/telemoto/circuits/edit.php?id=<?php echo $circuit['id']; ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> Modifier
        </a>
    </div>
    
    <div class="circuit-details">
        <div class="detail-group">
            <label>Nom:</label>
            <p><?php echo htmlspecialchars($circuit['nom']); ?></p>
        </div>
        
        <div class="detail-group">
            <label>Pays:</label>
            <p><?php echo !empty($circuit['pays']) ? htmlspecialchars($circuit['pays']) : 'Non spécifié'; ?></p>
        </div>
        
        <div class="detail-group">
            <label>Longueur:</label>
            <p><?php echo $circuit['longueur'] ? htmlspecialchars($circuit['longueur']) . ' km' : 'Non spécifié'; ?></p>
        </div>
        
        <div class="detail-group">
            <label>Largeur moyenne:</label>
            <p><?php echo $circuit['largeur'] ? htmlspecialchars($circuit['largeur']) . ' m' : 'Non spécifié'; ?></p>
        </div>
    </div>
    
    <div class="circuit-map-container mt-3 mb-3">
        <h3 class="mb-2">Tracé du Circuit</h3>
        <div class="circuit-map">
            <div class="circuit-map-placeholder">
                <i class="fas fa-map"></i>
                <p>Tracé du circuit non disponible</p>
            </div>
        </div>
    </div>
    
    <div class="virages-details mt-3">
        <h3 class="mb-2">Détails des Virages</h3>
        <?php if (!empty($circuit['details_virages'])): ?>
            <div class="virages-content">
                <?php 
                $virages = explode("\n", $circuit['details_virages']);
                foreach ($virages as $virage) {
                    if (!empty(trim($virage))) {
                        echo '<div class="virage-item">';
                        echo '<i class="fas fa-angle-right"></i> ';
                        echo htmlspecialchars($virage);
                        echo '</div>';
                    }
                }
                ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Aucun détail de virage spécifié.
            </div>
        <?php endif; ?>
    </div>
    
    <h3 class="mt-3 mb-2">Sessions récentes</h3>
    
    <?php
    // Récupérer les sessions récentes sur ce circuit
    $sql = "SELECT s.*, p.nom as pilote_nom, p.prenom as pilote_prenom, m.marque, m.modele 
            FROM sessions s 
            JOIN pilotes p ON s.pilote_id = p.id 
            JOIN motos m ON s.moto_id = m.id 
            WHERE s.circuit_id = ? 
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
                        <th>Pilote</th>
                        <th>Moto</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>';
        
        while ($session = $sessions->fetch_assoc()) {
            echo '<tr>
                    <td>' . date('d/m/Y', strtotime($session['date'])) . '</td>
                    <td>' . ucfirst(str_replace('_', ' ', $session['type'])) . '</td>
                    <td>' . htmlspecialchars($session['pilote_prenom'] . ' ' . $session['pilote_nom']) . '</td>
                    <td>' . htmlspecialchars($session['marque'] . ' ' . $session['modele']) . '</td>
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
                <i class="fas fa-exclamation-triangle"></i> Aucune session récente sur ce circuit.
              </div>';
    }
    ?>
</div>

<style>
.circuit-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.detail-group {
    margin-bottom: 1rem;
}
.detail-group label {
    font-weight: bold;
    color: var(--primary-color);
    margin-bottom: 0.2rem;
}
.detail-group p {
    font-size: 1.1rem;
}
.circuit-map {
    width: 100%;
    height: 300px;
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: var(--border-radius);
    position: relative;
    overflow: hidden;
    border: 1px solid var(--light-gray);
}
.circuit-map-placeholder {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    color: var(--light-gray);
}
.circuit-map-placeholder i {
    font-size: 3rem;
    margin-bottom: 1rem;
}
.virages-content {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: var(--border-radius);
    padding: 1rem;
    border: 1px solid var(--light-gray);
}
.virage-item {
    padding: 0.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}
.virage-item:last-child {
    border-bottom: none;
}
.virage-item i {
    color: var(--primary-color);
    margin-right: 0.5rem;
}
</style>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
