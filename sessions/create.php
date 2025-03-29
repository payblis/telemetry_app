<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../api/chatgpt.php';
require_once __DIR__ . '/../api/communautaire.php';

// Vérifier la connexion à la base de données
$conn = getDBConnection();

// Traitement du formulaire
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $date = trim($_POST['date'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $pilote_id = !empty($_POST['pilote_id']) ? intval($_POST['pilote_id']) : null;
    $moto_id = !empty($_POST['moto_id']) ? intval($_POST['moto_id']) : null;
    $circuit_id = !empty($_POST['circuit_id']) ? intval($_POST['circuit_id']) : null;
    $conditions = trim($_POST['conditions'] ?? '');
    $reglages_initiaux = trim($_POST['reglages_initiaux'] ?? '');
    
    // Données techniques détaillées
    $precharge_avant = !empty($_POST['precharge_avant']) ? floatval($_POST['precharge_avant']) : null;
    $precharge_arriere = !empty($_POST['precharge_arriere']) ? floatval($_POST['precharge_arriere']) : null;
    $compression_basse_avant = !empty($_POST['compression_basse_avant']) ? floatval($_POST['compression_basse_avant']) : null;
    $compression_haute_avant = !empty($_POST['compression_haute_avant']) ? floatval($_POST['compression_haute_avant']) : null;
    $compression_basse_arriere = !empty($_POST['compression_basse_arriere']) ? floatval($_POST['compression_basse_arriere']) : null;
    $compression_haute_arriere = !empty($_POST['compression_haute_arriere']) ? floatval($_POST['compression_haute_arriere']) : null;
    $detente_avant = !empty($_POST['detente_avant']) ? floatval($_POST['detente_avant']) : null;
    $detente_arriere = !empty($_POST['detente_arriere']) ? floatval($_POST['detente_arriere']) : null;
    $hauteur_fourche = !empty($_POST['hauteur_fourche']) ? floatval($_POST['hauteur_fourche']) : null;
    $hauteur_arriere = !empty($_POST['hauteur_arriere']) ? floatval($_POST['hauteur_arriere']) : null;
    
    // Pneumatiques
    $type_pneu_avant = trim($_POST['type_pneu_avant'] ?? '');
    $type_pneu_arriere = trim($_POST['type_pneu_arriere'] ?? '');
    $pression_avant_froid = !empty($_POST['pression_avant_froid']) ? floatval($_POST['pression_avant_froid']) : null;
    $pression_arriere_froid = !empty($_POST['pression_arriere_froid']) ? floatval($_POST['pression_arriere_froid']) : null;
    
    // Transmission
    $pignon_avant = !empty($_POST['pignon_avant']) ? intval($_POST['pignon_avant']) : null;
    $pignon_arriere = !empty($_POST['pignon_arriere']) ? intval($_POST['pignon_arriere']) : null;
    $longueur_chaine = !empty($_POST['longueur_chaine']) ? floatval($_POST['longueur_chaine']) : null;
    
    // Validation des données
    $errors = [];
    
    if (empty($date)) {
        $errors[] = "La date est obligatoire";
    }
    
    if (empty($type)) {
        $errors[] = "Le type de session est obligatoire";
    }
    
    if (!$pilote_id) {
        $errors[] = "Le pilote est obligatoire";
    }
    
    if (!$moto_id) {
        $errors[] = "La moto est obligatoire";
    }
    
    if (!$circuit_id) {
        $errors[] = "Le circuit est obligatoire";
    }
    
    // Si pas d'erreurs, insérer dans la base de données
    if (empty($errors)) {
        // Insérer la session
        $sql = "INSERT INTO sessions (date, type, pilote_id, moto_id, circuit_id, conditions, reglages_initiaux, 
                pignon_avant, pignon_arriere, longueur_chaine, pneu_avant, pneu_arriere) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiissiidsss", $date, $type, $pilote_id, $moto_id, $circuit_id, $conditions, $reglages_initiaux, 
                          $pignon_avant, $pignon_arriere, $longueur_chaine, $type_pneu_avant, $type_pneu_arriere);
        
        if ($stmt->execute()) {
            // Récupérer l'ID de la session insérée
            $session_id = $conn->insert_id;
            
            // Insérer les données techniques détaillées
            $sql = "INSERT INTO donnees_techniques_session (session_id, precharge_avant, precharge_arriere, 
                    compression_basse_avant, compression_haute_avant, compression_basse_arriere, compression_haute_arriere, 
                    detente_avant, detente_arriere, hauteur_fourche, hauteur_arriere, 
                    type_pneu_avant, type_pneu_arriere, pression_avant_froid, pression_arriere_froid) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iddddddddddssdd", $session_id, $precharge_avant, $precharge_arriere, 
                             $compression_basse_avant, $compression_haute_avant, $compression_basse_arriere, $compression_haute_arriere, 
                             $detente_avant, $detente_arriere, $hauteur_fourche, $hauteur_arriere, 
                             $type_pneu_avant, $type_pneu_arriere, $pression_avant_froid, $pression_arriere_froid);
            
            $stmt->execute();
            
            // Redirection vers la page de détails de la session
            header("Location: /telemoto/sessions/view.php?id=$session_id&success=1");
            exit;
        } else {
            $message = "Erreur lors de la création de la session : " . $conn->error;
            $messageType = "danger";
        }
    } else {
        $message = "Veuillez corriger les erreurs suivantes :<br>" . implode("<br>", $errors);
        $messageType = "danger";
    }
}

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <h2 class="card-title">Créer une Nouvelle Session</h2>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="" class="needs-validation">
        <div class="form-tabs">
            <button type="button" class="tab-button active" data-tab="infos-base">Informations de base</button>
            <button type="button" class="tab-button" data-tab="suspension">Suspension</button>
            <button type="button" class="tab-button" data-tab="transmission">Transmission</button>
            <button type="button" class="tab-button" data-tab="pneumatiques">Pneumatiques</button>
            <button type="button" class="tab-button" data-tab="electronique">Électronique</button>
        </div>
        
        <div class="tab-content">
            <!-- Onglet Informations de base -->
            <div class="tab-pane active" id="infos-base">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="date">Date *</label>
                        <input type="date" id="date" name="date" required value="<?php echo htmlspecialchars($_POST['date'] ?? date('Y-m-d')); ?>">
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="type">Type de session *</label>
                        <select id="type" name="type" required>
                            <option value="">Sélectionnez un type</option>
                            <option value="course" <?php echo (isset($_POST['type']) && $_POST['type'] === 'course') ? 'selected' : ''; ?>>Course</option>
                            <option value="qualification" <?php echo (isset($_POST['type']) && $_POST['type'] === 'qualification') ? 'selected' : ''; ?>>Qualification</option>
                            <option value="free_practice" <?php echo (isset($_POST['type']) && $_POST['type'] === 'free_practice') ? 'selected' : ''; ?>>Free Practice</option>
                            <option value="entrainement" <?php echo (isset($_POST['type']) && $_POST['type'] === 'entrainement') ? 'selected' : ''; ?>>Entraînement</option>
                            <option value="track_day" <?php echo (isset($_POST['type']) && $_POST['type'] === 'track_day') ? 'selected' : ''; ?>>Track Day</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="pilote_id">Pilote *</label>
                        <select id="pilote_id" name="pilote_id" required>
                            <option value="">Sélectionnez un pilote</option>
                            <?php
                            // Récupérer la liste des pilotes
                            $sql = "SELECT id, nom, prenom FROM pilotes ORDER BY nom, prenom";
                            $result = $conn->query($sql);
                            
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $selected = (isset($_POST['pilote_id']) && $_POST['pilote_id'] == $row['id']) ? 'selected' : '';
                                    echo '<option value="' . $row['id'] . '" ' . $selected . '>';
                                    echo htmlspecialchars($row['prenom'] . ' ' . $row['nom']);
                                    echo '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group col-md-4">
                        <label for="moto_id">Moto *</label>
                        <select id="moto_id" name="moto_id" required>
                            <option value="">Sélectionnez une moto</option>
                            <?php
                            // Récupérer la liste des motos
                            $sql = "SELECT id, marque, modele, type FROM motos ORDER BY marque, modele";
                            $result = $conn->query($sql);
                            
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $selected = (isset($_POST['moto_id']) && $_POST['moto_id'] == $row['id']) ? 'selected' : '';
                                    echo '<option value="' . $row['id'] . '" ' . $selected . '>';
                                    echo htmlspecialchars($row['marque'] . ' ' . $row['modele']) . ' (' . ($row['type'] === 'race' ? 'Course' : 'Origine') . ')';
                                    echo '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group col-md-4">
                        <label for="circuit_id">Circuit *</label>
                        <select id="circuit_id" name="circuit_id" required>
                            <option value="">Sélectionnez un circuit</option>
                            <?php
                            // Récupérer la liste des circuits
                            $sql = "SELECT id, nom, pays FROM circuits ORDER BY nom";
                            $result = $conn->query($sql);
                            
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $selected = (isset($_POST['circuit_id']) && $_POST['circuit_id'] == $row['id']) ? 'selected' : '';
                                    echo '<option value="' . $row['id'] . '" ' . $selected . '>';
                                    echo htmlspecialchars($row['nom']) . (!empty($row['pays']) ? ' (' . htmlspecialchars($row['pays']) . ')' : '');
                                    echo '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="conditions">Conditions</label>
                    <input type="text" id="conditions" name="conditions" value="<?php echo htmlspecialchars($_POST['conditions'] ?? ''); ?>" placeholder="Ex: Sec, 25°C, vent faible">
                    <small class="form-text">Décrivez les conditions météo et de piste (température, humidité, vent, etc.)</small>
                </div>
                
                <div class="form-group">
                    <label for="reglages_initiaux">Réglages initiaux de la moto</label>
                    <textarea id="reglages_initiaux" name="reglages_initiaux" rows="5" placeholder="Ex: Précharge avant: 3 tours, Détente arrière: 2 clics, etc."><?php echo htmlspecialchars($_POST['reglages_initiaux'] ?? ''); ?></textarea>
                    <small class="form-text">Décrivez les réglages initiaux de la moto au début de la session</small>
                </div>
            </div>
            
            <!-- Onglet Suspension -->
            <div class="tab-pane" id="suspension">
                <h3>Réglages de suspension</h3>
                <p class="mb-3">Ces réglages sont importants pour l'analyse et les recommandations de l'IA.</p>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="precharge_avant">Précharge ressort avant (mm ou tours)</label>
                        <input type="number" id="precharge_avant" name="precharge_avant" step="0.1" value="<?php echo htmlspecialchars($_POST['precharge_avant'] ?? ''); ?>" placeholder="Ex: 5.5">
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="precharge_arriere">Précharge ressort arrière (mm ou tours)</label>
                        <input type="number" id="precharge_arriere" name="precharge_arriere" step="0.1" value="<?php echo htmlspecialchars($_POST['precharge_arriere'] ?? ''); ?>" placeholder="Ex: 8.0">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="compression_basse_avant">Compression basse vitesse avant (clics)</label>
                        <input type="number" id="compression_basse_avant" name="compression_basse_avant" step="1" value="<?php echo htmlspecialchars($_POST['compression_basse_avant'] ?? ''); ?>" placeholder="Ex: 12">
                    </div>
                    
                    <div class="form-group col-md-3">
                        <label for="compression_haute_avant">Compression haute vitesse avant (clics)</label>
                        <input type="number" id="compression_haute_avant" name="compression_haute_avant" step="1" value="<?php echo htmlspecialchars($_POST['compression_haute_avant'] ?? ''); ?>" placeholder="Ex: 2">
                    </div>
                    
                    <div class="form-group col-md-3">
                        <label for="compression_basse_arriere">Compression basse vitesse arrière (clics)</label>
                        <input type="number" id="compression_basse_arriere" name="compression_basse_arriere" step="1" value="<?php echo htmlspecialchars($_POST['compression_basse_arriere'] ?? ''); ?>" placeholder="Ex: 10">
                    </div>
                    
                    <div class="form-group col-md-3">
                        <label for="compression_haute_arriere">Compression haute vitesse arrière (clics)</label>
                        <input type="number" id="compression_haute_arriere" name="compression_haute_arriere" step="1" value="<?php echo htmlspecialchars($_POST['compression_haute_arriere'] ?? ''); ?>" placeholder="Ex: 2">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="detente_avant">Détente avant (clics)</label>
                        <input type="number" id="detente_avant" name="detente_avant" step="1" value="<?php echo htmlspecialchars($_POST['detente_avant'] ?? ''); ?>" placeholder="Ex: 15">
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="detente_arriere">Détente arrière (clics)</label>
                        <input type="number" id="detente_arriere" name="detente_arriere" step="1" value="<?php echo htmlspecialchars($_POST['detente_arriere'] ?? ''); ?>" placeholder="Ex: 12">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="hauteur_fourche">Hauteur de la fourche (mm)</label>
                        <input type="number" id="hauteur_fourche" name="hauteur_fourche" step="1" value="<?php echo htmlspecialchars($_POST['hauteur_fourche'] ?? ''); ?>" placeholder="Ex: 5">
                        <small class="form-text">Hauteur de la fourche qui dépasse des tés</small>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="hauteur_arriere">Hauteur arrière (mm)</label>
                        <input type="number" id="hauteur_arriere" name="hauteur_arriere" step="1" value="<?php echo htmlspecialchars($_POST['hauteur_arriere'] ?? ''); ?>" placeholder="Ex: 0">
                        <small class="form-text">Hauteur de l'arrière / assiette moto</small>
                    </div>
                </div>
            </div>
            
            <!-- Onglet Transmission -->
            <div class="tab-pane" id="transmission">
                <h3>Transmission</h3>
                <p class="mb-3">Ces réglages sont importants pour l'analyse et les recommandations de l'IA.</p>
                
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="pignon_avant">Pignon avant (dents)</label>
                        <input type="number" id="pignon_avant" name="pignon_avant" min="10" max="20" step="1" value="<?php echo htmlspecialchars($_POST['pignon_avant'] ?? ''); ?>" placeholder="Ex: 15">
                    </div>
                    
                    <div class="form-group col-md-4">
                        <label for="pignon_arriere">Pignon arrière / Couronne (dents)</label>
                        <input type="number" id="pignon_arriere" name="pignon_arriere" min="30" max="60" step="1" value="<?php echo htmlspecialchars($_POST['pignon_arriere'] ?? ''); ?>" placeholder="Ex: 45">
                    </div>
                    
                    <div class="form-group col-md-4">
                        <label for="longueur_chaine">Longueur de chaîne (maillons)</label>
                        <input type="number" id="longueur_chaine" name="longueur_chaine" min="100" max="150" step="1" value="<?php echo htmlspecialchars($_POST['longueur_chaine'] ?? ''); ?>" placeholder="Ex: 120">
                    </div>
                </div>
            </div>
            
            <!-- Onglet Pneumatiques -->
            <div class="tab-pane" id="pneumatiques">
                <h3>Pneumatiques</h3>
                <p class="mb-3">Ces réglages sont importants pour l'analyse et les recommandations de l'IA.</p>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="type_pneu_avant">Type de pneu avant</label>
                        <select id="type_pneu_avant" name="type_pneu_avant">
                            <option value="">Sélectionnez un type</option>
                            <option value="slick" <?php echo (isset($_POST['type_pneu_avant']) && $_POST['type_pneu_avant'] === 'slick') ? 'selected' : ''; ?>>Slick</option>
                            <option value="pluie" <?php echo (isset($_POST['type_pneu_avant']) && $_POST['type_pneu_avant'] === 'pluie') ? 'selected' : ''; ?>>Pluie</option>
                            <option value="mixte" <?php echo (isset($_POST['type_pneu_avant']) && $_POST['type_pneu_avant'] === 'mixte') ? 'selected' : ''; ?>>Mixte</option>
                            <option value="standard" <?php echo (isset($_POST['type_pneu_avant']) && $_POST['type_pneu_avant'] === 'standard') ? 'selected' : ''; ?>>Standard</option>
                        </select>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="type_pneu_arriere">Type de pneu arrière</label>
                        <select id="type_pneu_arriere" name="type_pneu_arriere">
                            <option value="">Sélectionnez un type</option>
                            <option value="slick" <?php echo (isset($_POST['type_pneu_arriere']) && $_POST['type_pneu_arriere'] === 'slick') ? 'selected' : ''; ?>>Slick</option>
                            <option value="pluie" <?php echo (isset($_POST['type_pneu_arriere']) && $_POST['type_pneu_arriere'] === 'pluie') ? 'selected' : ''; ?>>Pluie</option>
                            <option value="mixte" <?php echo (isset($_POST['type_pneu_arriere']) && $_POST['type_pneu_arriere'] === 'mixte') ? 'selected' : ''; ?>>Mixte</option>
                            <option value="standard" <?php echo (isset($_POST['type_pneu_arriere']) && $_POST['type_pneu_arriere'] === 'standard') ? 'selected' : ''; ?>>Standard</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="pression_avant_froid">Pression pneu avant à froid (bar)</label>
                        <input type="number" id="pression_avant_froid" name="pression_avant_froid" step="0.1" min="1.5" max="3.0" value="<?php echo htmlspecialchars($_POST['pression_avant_froid'] ?? ''); ?>" placeholder="Ex: 2.1">
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="pression_arriere_froid">Pression pneu arrière à froid (bar)</label>
                        <input type="number" id="pression_arriere_froid" name="pression_arriere_froid" step="0.1" min="1.5" max="3.0" value="<?php echo htmlspecialchars($_POST['pression_arriere_froid'] ?? ''); ?>" placeholder="Ex: 1.8">
                    </div>
                </div>
            </div>
            
            <!-- Onglet Électronique -->
            <div class="tab-pane" id="electronique">
                <h3>Électronique et aides à la conduite</h3>
                <p class="mb-3">Ces réglages sont importants pour l'analyse et les recommandations de l'IA.</p>
                
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="traction_control">Contrôle de traction (niveau)</label>
                        <input type="number" id="traction_control" name="traction_control" min="0" max="10" step="1" value="<?php echo htmlspecialchars($_POST['traction_control'] ?? ''); ?>" placeholder="Ex: 3">
                    </div>
                    
                    <div class="form-group col-md-4">
                        <label for="anti_wheeling">Anti-wheeling (niveau)</label>
                        <input type="number" id="anti_wheeling" name="anti_wheeling" min="0" max="10" step="1" value="<?php echo htmlspecialchars($_POST['anti_wheeling'] ?? ''); ?>" placeholder="Ex: 2">
                    </div>
                    
                    <div class="form-group col-md-4">
                        <label for="mode_moteur">Mode moteur</label>
                        <input type="text" id="mode_moteur" name="mode_moteur" value="<?php echo htmlspecialchars($_POST['mode_moteur'] ?? ''); ?>" placeholder="Ex: Race, Sport, Rain...">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="checkbox-container">
                            <input type="checkbox" id="launch_control" name="launch_control" value="1" <?php echo (isset($_POST['launch_control']) && $_POST['launch_control'] == '1') ? 'checked' : ''; ?>>
                            <span class="checkbox-label">Launch Control activé</span>
                        </label>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label class="checkbox-container">
                            <input type="checkbox" id="abs_active" name="abs_active" value="1" <?php echo (isset($_POST['abs_active']) && $_POST['abs_active'] == '1') ? 'checked' : ''; ?>>
                            <span class="checkbox-label">ABS activé</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <a href="/telemoto/sessions/index.php" class="btn">Annuler</a>
            <button type="submit" class="btn btn-primary">Créer la session</button>
        </div>
    </form>
</div>

<style>
.form-row {
    display: flex;
    flex-wrap: wrap;
    margin-right: -10px;
    margin-left: -10px;
}
.form-row > .form-group {
    padding-right: 10px;
    padding-left: 10px;
}
.col-md-3 {
    flex: 0 0 25%;
    max-width: 25%;
}
.col-md-4 {
    flex: 0 0 33.333333%;
    max-width: 33.333333%;
}
.col-md-6 {
    flex: 0 0 50%;
    max-width: 50%;
}
.form-tabs {
    display: flex;
    overflow-x: auto;
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--light-gray);
}
.tab-button {
    background: none;
    border: none;
    padding: 0.75rem 1.5rem;
    cursor: pointer;
    color: var(--text-color);
    font-weight: bold;
    position: relative;
    white-space: nowrap;
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
.mb-3 {
    margin-bottom: 1rem;
}
.checkbox-container {
    display: flex;
    align-items: center;
    cursor: pointer;
}
.checkbox-label {
    margin-left: 0.5rem;
}
@media (max-width: 768px) {
    .col-md-3, .col-md-4, .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
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
