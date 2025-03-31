<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/auth_functions.php';

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier que l'utilisateur est connecté
requireLogin();

// Connexion à la base de données
$conn = getDBConnection();

// Récupérer les motos de l'utilisateur
$user_id = $_SESSION['user_id'];
$sql = "SELECT id, marque, modele, cylindree, annee, type FROM motos ORDER BY marque, modele";
$result = $conn->query($sql);
$motos = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $motos[] = $row;
    }
}

// Récupérer les entretiens si une moto est sélectionnée
$entretiens = [];
$moto_selected = null;
if (isset($_GET['moto_id']) && !empty($_GET['moto_id'])) {
    $moto_id = intval($_GET['moto_id']);
    
    // Récupérer les informations de la moto
    $stmt = $conn->prepare("SELECT * FROM motos WHERE id = ?");
    $stmt->bind_param("i", $moto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $moto_selected = $result->fetch_assoc();
    }
    
    // Récupérer les entretiens de la moto
    $stmt = $conn->prepare("SELECT * FROM entretiens WHERE moto_id = ? ORDER BY date DESC");
    $stmt->bind_param("i", $moto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $entretiens[] = $row;
        }
    }
}

// Traitement du formulaire d'ajout d'entretien
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_entretien'])) {
    $moto_id = intval($_POST['moto_id'] ?? 0);
    $type = $_POST['type'] ?? '';
    $date = $_POST['date'] ?? '';
    $kilometrage = intval($_POST['kilometrage'] ?? 0);
    $description = $_POST['description'] ?? '';
    $pieces = $_POST['pieces'] ?? '';
    $cout = floatval($_POST['cout'] ?? 0);
    $prochain_entretien_km = intval($_POST['prochain_entretien_km'] ?? 0);
    $prochain_entretien_date = $_POST['prochain_entretien_date'] ?? null;
    
    // Validation des données
    if (empty($moto_id) || empty($type) || empty($date) || empty($description)) {
        $error_message = 'Tous les champs obligatoires doivent être remplis.';
    } else {
        // Insérer l'entretien dans la base de données
        $stmt = $conn->prepare("INSERT INTO entretiens (moto_id, type, date, kilometrage, description, pieces_remplacees, cout, prochain_entretien_km, prochain_entretien_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ississdis", $moto_id, $type, $date, $kilometrage, $description, $pieces, $cout, $prochain_entretien_km, $prochain_entretien_date);
        
        if ($stmt->execute()) {
            $success_message = 'Entretien ajouté avec succès.';
            
            // Rediriger pour éviter la soumission multiple du formulaire
            header("Location: " . url("entretien/?moto_id=$moto_id&success=1"));
            exit;
        } else {
            $error_message = 'Erreur lors de l\'ajout de l\'entretien: ' . $conn->error;
        }
    }
}

// Message de succès après redirection
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = 'Entretien ajouté avec succès.';
}

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="entretien-container">
    <h1 class="entretien-title">Carnet d'Entretien Moto</h1>
    
    <div class="entretien-intro">
        <p>Suivez l'historique d'entretien de vos motos, planifiez les prochaines maintenances et gardez une trace de toutes les interventions mécaniques.</p>
    </div>
    
    <div class="entretien-selection">
        <form method="GET" action="<?php echo url('entretien/'); ?>" class="moto-select-form">
            <div class="form-group">
                <label for="moto_id">Sélectionner une moto:</label>
                <select id="moto_id" name="moto_id" class="form-control" onchange="this.form.submit()">
                    <option value="">Choisir une moto</option>
                    <?php foreach ($motos as $moto): ?>
                        <option value="<?php echo $moto['id']; ?>" <?php echo (isset($_GET['moto_id']) && $_GET['moto_id'] == $moto['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($moto['marque'] . ' ' . $moto['modele'] . ' (' . $moto['cylindree'] . 'cc, ' . $moto['annee'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($moto_selected): ?>
        <div class="moto-details">
            <div class="moto-header">
                <h2><?php echo htmlspecialchars($moto_selected['marque'] . ' ' . $moto_selected['modele']); ?></h2>
                <div class="moto-specs">
                    <span><i class="fas fa-tachometer-alt"></i> <?php echo $moto_selected['cylindree']; ?>cc</span>
                    <span><i class="fas fa-calendar-alt"></i> <?php echo $moto_selected['annee']; ?></span>
                    <span><i class="fas fa-tag"></i> <?php echo ucfirst($moto_selected['type']); ?></span>
                </div>
            </div>
            
            <div class="entretien-actions">
                <button class="btn btn-primary" id="showAddEntretienForm">
                    <i class="fas fa-plus"></i> Ajouter un entretien
                </button>
            </div>
            
            <div class="add-entretien-form" id="addEntretienForm" style="display: none;">
                <h3>Ajouter un nouvel entretien</h3>
                
                <form method="POST" action="<?php echo url('entretien/'); ?>" class="entretien-form">
                    <input type="hidden" name="add_entretien" value="1">
                    <input type="hidden" name="moto_id" value="<?php echo $moto_selected['id']; ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="type">Type d'entretien:</label>
                            <select id="type" name="type" required>
                                <option value="">Sélectionner un type</option>
                                <option value="revision">Révision périodique</option>
                                <option value="huile">Vidange huile</option>
                                <option value="pneus">Changement pneus</option>
                                <option value="freins">Entretien freins</option>
                                <option value="chaine">Entretien chaîne/transmission</option>
                                <option value="suspension">Entretien suspension</option>
                                <option value="reparation">Réparation</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="date">Date:</label>
                            <input type="date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="kilometrage">Kilométrage:</label>
                            <input type="number" id="kilometrage" name="kilometrage" min="0" step="1" placeholder="Kilométrage actuel">
                        </div>
                        
                        <div class="form-group">
                            <label for="cout">Coût (€):</label>
                            <input type="number" id="cout" name="cout" min="0" step="0.01" placeholder="Coût de l'intervention">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" rows="3" required placeholder="Décrivez l'entretien réalisé"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="pieces">Pièces remplacées:</label>
                        <textarea id="pieces" name="pieces" rows="2" placeholder="Liste des pièces remplacées"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="prochain_entretien_km">Prochain entretien (km):</label>
                            <input type="number" id="prochain_entretien_km" name="prochain_entretien_km" min="0" step="1" placeholder="Kilométrage du prochain entretien">
                        </div>
                        
                        <div class="form-group">
                            <label for="prochain_entretien_date">Prochain entretien (date):</label>
                            <input type="date" id="prochain_entretien_date" name="prochain_entretien_date">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <button type="button" class="btn btn-secondary" id="cancelAddEntretien">Annuler</button>
                    </div>
                </form>
            </div>
            
            <div class="entretien-list">
                <h3>Historique d'entretien</h3>
                
                <?php if (empty($entretiens)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Aucun entretien enregistré pour cette moto.
                    </div>
                <?php else: ?>
                    <div class="entretien-timeline">
                        <?php foreach ($entretiens as $entretien): ?>
                            <div class="entretien-item">
                                <div class="entretien-date">
                                    <div class="date-badge"><?php echo date('d', strtotime($entretien['date'])); ?></div>
                                    <div class="date-month"><?php echo date('M', strtotime($entretien['date'])); ?></div>
                                    <div class="date-year"><?php echo date('Y', strtotime($entretien['date'])); ?></div>
                                </div>
                                
                                <div class="entretien-content">
                                    <div class="entretien-header">
                                        <h4><?php echo getEntretienTypeLabel($entretien['type']); ?></h4>
                                        <?php if ($entretien['kilometrage']): ?>
                                            <span class="kilometrage"><?php echo number_format($entretien['kilometrage'], 0, ',', ' '); ?> km</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="entretien-description">
                                        <?php echo nl2br(htmlspecialchars($entretien['description'])); ?>
                                    </div>
                                    
                                    <?php if (!empty($entretien['pieces_remplacees'])): ?>
                                        <div class="entretien-pieces">
                                            <strong>Pièces remplacées:</strong> <?php echo nl2br(htmlspecialchars($entretien['pieces_remplacees'])); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($entretien['cout'] > 0): ?>
                                        <div class="entretien-cout">
                                            <strong>Coût:</strong> <?php echo number_format($entretien['cout'], 2, ',', ' '); ?> €
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($entretien['prochain_entretien_km'] || $entretien['prochain_entretien_date']): ?>
                                        <div class="entretien-prochain">
                                            <strong>Prochain entretien:</strong>
                                            <?php if ($entretien['prochain_entretien_km']): ?>
                                                <span><?php echo number_format($entretien['prochain_entretien_km'], 0, ',', ' '); ?> km</span>
                                            <?php endif; ?>
                                            <?php if ($entretien['prochain_entretien_date']): ?>
                                                <span><?php echo date('d/m/Y', strtotime($entretien['prochain_entretien_date'])); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="entretien-rappels">
                <h3>Rappels d'entretien</h3>
                
                <?php
                $rappels = [];
                foreach ($entretiens as $entretien) {
                    if ($entretien['prochain_entretien_date'] && strtotime($entretien['prochain_entretien_date']) > time()) {
                        $rappels[] = [
                            'type' => $entretien['type'],
                            'date' => $entretien['prochain_entretien_date'],
                            'km' => $entretien['prochain_entretien_km'],
                            'days_left' => ceil((strtotime($entretien['prochain_entretien_date']) - time()) / (60 * 60 * 24))
                        ];
                    }
                }
                
                usort($rappels, function($a, $b) {
                    return strtotime($a['date']) - strtotime($b['date']);
                });
                ?>
                
                <?php if (empty($rappels)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Aucun rappel d'entretien programmé.
                    </div>
                <?php else: ?>
                    <div class="rappels-list">
                        <?php foreach ($rappels as $rappel): ?>
                            <div class="rappel-item <?php echo $rappel['days_left'] <= 30 ? 'urgent' : ''; ?>">
                                <div class="rappel-icon">
                                    <i class="<?php echo getEntretienTypeIcon($rappel['type']); ?>"></i>
                                </div>
                                <div class="rappel-details">
                                    <div class="rappel-type"><?php echo getEntretienTypeLabel($rappel['type']); ?></div>
                                    <div class="rappel-date">
                                        <i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($rappel['date'])); ?>
                                        (dans <?php echo $rappel['days_left']; ?> jours)
                                    </div>
                                    <?php if ($rappel['km']): ?>
                                        <div class="rappel-km">
                                            <i class="fas fa-tachometer-alt"></i> <?php echo number_format($rappel['km'], 0, ',', ' '); ?> km
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Veuillez sélectionner une moto pour afficher son carnet d'entretien.
        </div>
    <?php endif; ?>
    
    <div class="entretien-info">
        <h3>Pourquoi tenir un carnet d'entretien ?</h3>
        <p>Un carnet d'entretien bien tenu est essentiel pour :</p>
        <ul>
            <li>Suivre l'historique complet des interventions sur votre moto</li>
            <li>Planifier les entretiens préventifs et éviter les pannes</li>
            <li>Optimiser la durée de vie des composants</li>
            <li>Maintenir les performances optimales de votre moto</li>
            <li>Conserver une valeur de revente plus élevée</li>
            <li>Respecter les conditions de garantie du constructeur</li>
        </ul>
        <p>Pour les motos de compétition, un entretien rigoureux est encore plus crucial pour garantir la fiabilité et les performances en conditions de course.</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const showAddEntretienBtn = document.getElementById('showAddEntretienForm');
    const addEntretienForm = document.getElementById('addEntretienForm');
    const cancelAddEntretienBtn = document.getElementById('cancelAddEntretien');
    
    if (showAddEntretienBtn) {
        showAddEntretienBtn.addEventListener('click', function() {
            addEntretienForm.style.display = 'block';
            this.style.display = 'none';
        });
    }
    
    if (cancelAddEntretienBtn) {
        cancelAddEntretienBtn.addEventListener('click', function() {
            addEntretienForm.style.display = 'none';
            showAddEntretienBtn.style.display = 'block';
        });
    }
    
    // Pré-remplir le prochain entretien en fonction du type
    const typeSelect = document.getElementById('type');
    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            const kilometrageInput = document.getElementById('kilometrage');
            const prochainKmInput = document.getElementById('prochain_entretien_km');
            const prochainDateInput = document.getElementById('prochain_entretien_date');
            
            if (!kilometrageInput.value) return;
            
            const km = parseInt(kilometrageInput.value);
            let nextKm = 0;
            let nextDate = new Date();
            
            switch(this.value) {
                case 'revision':
                    nextKm = km + 10000;
                    nextDate.setMonth(nextDate.getMonth() + 12);
                    break;
                case 'huile':
                    nextKm = km + 5000;
                    nextDate.setMonth(nextDate.getMonth() + 6);
                    break;
                case 'pneus':
                    nextKm = km + 8000;
                    break;
                case 'freins':
                    nextKm = km + 15000;
                    break;
                case 'chaine':
                    nextKm = km + 3000;
                    nextDate.setMonth(nextDate.getMonth() + 3);
                    break;
                case 'suspension':
                    nextKm = km + 20000;
                    nextDate.setMonth(nextDate.getMonth() + 24);
                    break;
            }
            
            if (nextKm > km) {
                prochainKmInput.value = nextKm;
            }
            
            if (this.value !== 'pneus' && this.value !== 'freins') {
                prochainDateInput.value = nextDate.toISOString().split('T')[0];
            }
        });
    }
});
</script>

<style>
.entretien-container {
    padding: 1rem 0;
}

.entretien-title {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--primary-color);
    padding-bottom: 0.5rem;
}

.entretien-intro {
    margin-bottom: 2rem;
    font-size: 1.1rem;
    line-height: 1.6;
}

.entretien-selection {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid var(--light-gray);
}

.moto-select-form .form-group {
    margin-bottom: 0;
}

.moto-select-form label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
}

.moto-select-form select {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid var(--light-gray);
    border-radius: var(--border-radius);
    background-color: rgba(255, 255, 255, 0.05);
    color: var(--text-color);
}

.moto-details {
    margin-bottom: 2rem;
}

.moto-header {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid var(--light-gray);
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.moto-header h2 {
    margin: 0;
    color: var(--primary-color);
}

.moto-specs {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.moto-specs span {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.moto-specs i {
    color: var(--primary-color);
}

.entretien-actions {
    margin-bottom: 1.5rem;
    text-align: right;
}

.add-entretien-form {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid var(--light-gray);
}

.add-entretien-form h3 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 0.5rem;
}

.entretien-form .form-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

@media (min-width: 768px) {
    .entretien-form .form-row {
        grid-template-columns: 1fr 1fr;
    }
}

.entretien-form .form-group {
    margin-bottom: 1rem;
}

.entretien-form label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
}

.entretien-form input, .entretien-form select, .entretien-form textarea {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid var(--light-gray);
    border-radius: var(--border-radius);
    background-color: rgba(255, 255, 255, 0.05);
    color: var(--text-color);
}

.entretien-form textarea {
    resize: vertical;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.entretien-list, .entretien-rappels {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid var(--light-gray);
}

.entretien-list h3, .entretien-rappels h3 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 0.5rem;
}

.entretien-timeline {
    position: relative;
    padding-left: 2rem;
}

.entretien-timeline::before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    width: 2px;
    background-color: var(--primary-color);
}

.entretien-item {
    position: relative;
    margin-bottom: 2rem;
    display: flex;
    gap: 1.5rem;
}

.entretien-item:last-child {
    margin-bottom: 0;
}

.entretien-date {
    position: relative;
    min-width: 80px;
    text-align: center;
    background-color: var(--primary-color);
    color: #000;
    border-radius: var(--border-radius);
    padding: 0.5rem;
    margin-left: -3rem;
}

.entretien-date::before {
    content: '';
    position: absolute;
    top: 50%;
    left: -10px;
    width: 20px;
    height: 20px;
    background-color: var(--primary-color);
    border-radius: 50%;
    transform: translateY(-50%);
}

.date-badge {
    font-size: 1.5rem;
    font-weight: bold;
    line-height: 1;
}

.date-month {
    font-size: 0.9rem;
    text-transform: uppercase;
}

.date-year {
    font-size: 0.8rem;
}

.entretien-content {
    flex: 1;
    background-color: rgba(255, 255, 255, 0.05);
    border-radius: var(--border-radius);
    padding: 1rem;
    border: 1px solid var(--light-gray);
}

.entretien-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.entretien-header h4 {
    margin: 0;
    color: var(--text-color);
}

.kilometrage {
    font-weight: bold;
    color: var(--primary-color);
}

.entretien-description {
    margin-bottom: 0.5rem;
    line-height: 1.6;
}

.entretien-pieces, .entretien-cout, .entretien-prochain {
    margin-top: 0.5rem;
    font-size: 0.9rem;
    color: var(--dark-gray);
}

.rappels-list {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

@media (min-width: 768px) {
    .rappels-list {
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    }
}

.rappel-item {
    display: flex;
    gap: 1rem;
    background-color: rgba(255, 255, 255, 0.05);
    border-radius: var(--border-radius);
    padding: 1rem;
    border: 1px solid var(--light-gray);
}

.rappel-item.urgent {
    border-color: var(--danger-color);
    background-color: rgba(255, 62, 62, 0.1);
}

.rappel-icon {
    font-size: 2rem;
    color: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
}

.rappel-details {
    flex: 1;
}

.rappel-type {
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.rappel-date, .rappel-km {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.entretien-info {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    border: 1px solid var(--light-gray);
}

.entretien-info h3 {
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.entretien-info p {
    margin-bottom: 1rem;
    line-height: 1.6;
}

.entretien-info ul {
    margin-left: 1.5rem;
    margin-bottom: 1rem;
}

.entretien-info li {
    margin-bottom: 0.5rem;
    line-height: 1.6;
}
</style>

<?php
// Fonction pour obtenir le libellé du type d'entretien
function getEntretienTypeLabel($type) {
    $types = [
        'revision' => 'Révision périodique',
        'huile' => 'Vidange huile',
        'pneus' => 'Changement pneus',
        'freins' => 'Entretien freins',
        'chaine' => 'Entretien chaîne/transmission',
        'suspension' => 'Entretien suspension',
        'reparation' => 'Réparation',
        'autre' => 'Autre intervention'
    ];
    
    return $types[$type] ?? 'Intervention';
}

// Fonction pour obtenir l'icône du type d'entretien
function getEntretienTypeIcon($type) {
    $icons = [
        'revision' => 'fas fa-tools',
        'huile' => 'fas fa-oil-can',
        'pneus' => 'fas fa-circle-notch',
        'freins' => 'fas fa-brake-system',
        'chaine' => 'fas fa-link',
        'suspension' => 'fas fa-compress-alt',
        'reparation' => 'fas fa-wrench',
        'autre' => 'fas fa-cog'
    ];
    
    return $icons[$type] ?? 'fas fa-cog';
}

// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
