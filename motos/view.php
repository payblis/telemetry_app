<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/database.php';

// Vérifier la connexion à la base de données
$conn = getDBConnection();

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: /telemoto/motos/index.php?error=1");
    exit;
}

$id = intval($_GET['id']);

// Récupérer les données de la moto
$sql = "SELECT * FROM motos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: /telemoto/motos/index.php?error=2");
    exit;
}

$moto = $result->fetch_assoc();

// Récupérer les équipements spécifiques si moto de course
$equipements = [];
if ($moto['type'] === 'race') {
    $sql = "SELECT * FROM equipements_moto WHERE moto_id = ? ORDER BY categorie";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $equipements_result = $stmt->get_result();
    
    while ($equipement = $equipements_result->fetch_assoc()) {
        $equipements[] = $equipement;
    }
}

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <h2 class="card-title">Détails de la Moto</h2>
    
    <div class="mb-3">
        <a href="/telemoto/motos/index.php" class="btn">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
        <a href="/telemoto/motos/edit.php?id=<?php echo $moto['id']; ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> Modifier
        </a>
    </div>
    
    <div class="moto-header">
        <div class="moto-title">
            <h3><?php echo htmlspecialchars($moto['marque'] . ' ' . $moto['modele']); ?></h3>
            <div class="moto-type-badge <?php echo $moto['type'] === 'race' ? 'race-badge' : 'origine-badge'; ?>">
                <?php echo $moto['type'] === 'race' ? 'Moto de course' : 'Moto d\'origine'; ?>
            </div>
        </div>
        <div class="moto-specs">
            <?php if ($moto['cylindree']): ?>
                <div class="spec-item">
                    <span class="spec-label">Cylindrée:</span>
                    <span class="spec-value"><?php echo htmlspecialchars($moto['cylindree']); ?> cc</span>
                </div>
            <?php endif; ?>
            
            <?php if ($moto['annee']): ?>
                <div class="spec-item">
                    <span class="spec-label">Année:</span>
                    <span class="spec-value"><?php echo htmlspecialchars($moto['annee']); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="moto-content">
        <div class="moto-tabs">
            <button class="tab-button active" data-tab="reglages">Réglages standards</button>
            <?php if ($moto['type'] === 'race'): ?>
                <button class="tab-button" data-tab="equipements">Équipements spécifiques</button>
            <?php endif; ?>
            <button class="tab-button" data-tab="sessions">Sessions récentes</button>
        </div>
        
        <div class="tab-content">
            <!-- Onglet Réglages standards -->
            <div class="tab-pane active" id="reglages">
                <h3>Réglages standards</h3>
                <div class="reglages-content">
                    <?php if (!empty($moto['reglages_standards'])): ?>
                        <pre><?php echo htmlspecialchars($moto['reglages_standards']); ?></pre>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Aucun réglage standard spécifié.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($moto['type'] === 'race'): ?>
                <!-- Onglet Équipements spécifiques -->
                <div class="tab-pane" id="equipements">
                    <h3>Équipements spécifiques</h3>
                    
                    <?php if (!empty($moto['equipements_specifiques'])): ?>
                        <div class="equipements-description mb-3">
                            <h4>Description générale</h4>
                            <div class="equipements-content">
                                <p><?php echo nl2br(htmlspecialchars($moto['equipements_specifiques'])); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($equipements)): ?>
                        <div class="equipements-list">
                            <h4>Liste des équipements</h4>
                            
                            <?php
                            // Regrouper les équipements par catégorie
                            $equipements_par_categorie = [];
                            foreach ($equipements as $equipement) {
                                $categorie = $equipement['categorie'];
                                if (!isset($equipements_par_categorie[$categorie])) {
                                    $equipements_par_categorie[$categorie] = [];
                                }
                                $equipements_par_categorie[$categorie][] = $equipement;
                            }
                            
                            // Afficher les équipements par catégorie
                            foreach ($equipements_par_categorie as $categorie => $equipements_categorie):
                            ?>
                                <div class="equipement-categorie">
                                    <h5><?php echo htmlspecialchars($categorie); ?></h5>
                                    <div class="equipements-grid">
                                        <?php foreach ($equipements_categorie as $equipement): ?>
                                            <div class="equipement-card">
                                                <div class="equipement-header">
                                                    <span class="equipement-marque"><?php echo htmlspecialchars($equipement['marque']); ?></span>
                                                    <?php if (!empty($equipement['modele'])): ?>
                                                        <span class="equipement-modele"><?php echo htmlspecialchars($equipement['modele']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($equipement['specifications'])): ?>
                                                    <div class="equipement-specs">
                                                        <p><?php echo nl2br(htmlspecialchars($equipement['specifications'])); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Aucun équipement spécifique enregistré.
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Onglet Sessions récentes -->
            <div class="tab-pane" id="sessions">
                <h3>Sessions récentes</h3>
                
                <?php
                // Récupérer les sessions récentes avec cette moto
                $sql = "SELECT s.*, p.nom as pilote_nom, p.prenom as pilote_prenom, c.nom as circuit_nom 
                        FROM sessions s 
                        JOIN pilotes p ON s.pilote_id = p.id 
                        JOIN circuits c ON s.circuit_id = c.id 
                        WHERE s.moto_id = ? 
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
                                    <th>Circuit</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>';
                    
                    while ($session = $sessions->fetch_assoc()) {
                        echo '<tr>
                                <td>' . date('d/m/Y', strtotime($session['date'])) . '</td>
                                <td>' . ucfirst(str_replace('_', ' ', $session['type'])) . '</td>
                                <td>' . htmlspecialchars($session['pilote_prenom'] . ' ' . $session['pilote_nom']) . '</td>
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
                            <i class="fas fa-exclamation-triangle"></i> Aucune session récente avec cette moto.
                          </div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<style>
.moto-header {
    margin-bottom: 1.5rem;
}
.moto-title {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.5rem;
}
.moto-type-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: bold;
}
.origine-badge {
    background-color: rgba(0, 168, 255, 0.2);
    color: var(--primary-color);
}
.race-badge {
    background-color: rgba(255, 61, 0, 0.2);
    color: var(--danger-color);
}
.moto-specs {
    display: flex;
    gap: 1.5rem;
}
.spec-label {
    font-weight: bold;
    color: var(--dark-gray);
    margin-right: 0.25rem;
}
.moto-tabs {
    display: flex;
    border-bottom: 1px solid var(--light-gray);
    margin-bottom: 1rem;
    overflow-x: auto;
}
.tab-button {
    background: none;
    border: none;
    padding: 0.75rem 1.5rem;
    cursor: pointer;
    color: var(--text-color);
    font-weight: bold;
    position: relative;
}
.tab-button.active {
    color: var(--primary-color);
}
.tab-button.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background-color: var(--primary-color);
}
.tab-pane {
    display: none;
    padding: 1rem 0;
}
.tab-pane.active {
    display: block;
}
.reglages-content, .equipements-content {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: var(--border-radius);
    padding: 1rem;
    border: 1px solid var(--light-gray);
}
.reglages-content pre {
    white-space: pre-wrap;
    font-family: inherit;
    margin: 0;
}
.mb-3 {
    margin-bottom: 1rem;
}
.equipements-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
    margin-top: 0.5rem;
}
.equipement-card {
    background-color: rgba(0, 0, 0, 0.1);
    border-radius: var(--border-radius);
    padding: 1rem;
    border: 1px solid var(--light-gray);
}
.equipement-header {
    margin-bottom: 0.5rem;
}
.equipement-marque {
    font-weight: bold;
    color: var(--primary-color);
}
.equipement-modele {
    margin-left: 0.5rem;
    color: var(--dark-gray);
}
.equipement-categorie {
    margin-bottom: 1.5rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des onglets
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Désactiver tous les onglets
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            
            // Activer l'onglet sélectionné
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
});
</script>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
