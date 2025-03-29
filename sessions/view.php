<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../api/chatgpt.php';
require_once __DIR__ . '/../api/communautaire.php';

// Vérifier la connexion à la base de données
$conn = getDBConnection();

// Vérifier si l'ID est fourni
if (!isset($_GET['id'])) {
    header("Location: " . url('sessions/index.php?error=1'));
    exit;
}

$id = intval($_GET['id']);

// Récupérer les informations de la session
$sql = "SELECT s.*, p.nom as pilote_nom, p.prenom as pilote_prenom, p.taille as pilote_taille, p.poids as pilote_poids,
        m.marque as moto_marque, m.modele as moto_modele, m.cylindree as moto_cylindree, m.type as moto_type, m.reglages_standards,
        c.nom as circuit_nom, c.pays as circuit_pays, c.longueur as circuit_longueur, c.details_virages
        FROM sessions s
        LEFT JOIN pilotes p ON s.pilote_id = p.id
        LEFT JOIN motos m ON s.moto_id = m.id
        LEFT JOIN circuits c ON s.circuit_id = c.id
        WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: " . url('sessions/index.php?error=2'));
    exit;
}

$session = $result->fetch_assoc();

// Récupérer les données techniques détaillées
$sql = "SELECT * FROM donnees_techniques_session WHERE session_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$tech_result = $stmt->get_result();
$tech_data = $tech_result->fetch_assoc();

// Récupérer les chronos de la session
$sql = "SELECT * FROM chronos WHERE session_id = ? ORDER BY tour_numero";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$chronos = $stmt->get_result();

// Récupérer les remarques du pilote
$sql = "SELECT * FROM remarques_pilote WHERE session_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$remarques = $stmt->get_result();

// Récupérer les recommandations IA
$sql = "SELECT * FROM recommandations WHERE session_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$recommandations = $stmt->get_result();

// Traitement de l'ajout de chrono
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_chrono') {
    $tour_numero = intval($_POST['tour_numero'] ?? 0);
    $temps = trim($_POST['temps'] ?? '');
    
    if ($tour_numero > 0 && !empty($temps)) {
        // Convertir le temps au format MM:SS.mmm en secondes
        $temps_parts = explode(':', $temps);
        $minutes = intval($temps_parts[0]);
        $seconds_parts = explode('.', $temps_parts[1]);
        $seconds = floatval($seconds_parts[0] . '.' . ($seconds_parts[1] ?? '0'));
        $temps_secondes = $minutes * 60 + $seconds;
        
        $sql = "INSERT INTO chronos (session_id, tour_numero, temps, temps_secondes) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issd", $id, $tour_numero, $temps, $temps_secondes);
        
        if ($stmt->execute()) {
            header("Location: " . url('sessions/view.php?id=' . $id . '&success=1'));
            exit;
        }
    }
}

// Traitement de l'ajout de remarque
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_remarque') {
    $remarque = trim($_POST['remarque'] ?? '');
    
    if (!empty($remarque)) {
        $sql = "INSERT INTO remarques_pilote (session_id, remarque) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $id, $remarque);
        
        if ($stmt->execute()) {
            header("Location: " . url('sessions/view.php?id=' . $id . '&success=2'));
            exit;
        }
    }
}

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <h2 class="card-title">Détails de la Session</h2>
    
    <div class="mb-3">
        <a href="<?php echo url('sessions/index.php'); ?>" class="btn">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
        <a href="<?php echo url('sessions/edit.php?id=' . $session['id']); ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> Modifier
        </a>
    </div>
    
    <div class="session-header">
        <div class="session-info">
            <h3><?php echo date('d/m/Y', strtotime($session['date'])); ?> - <?php echo ucfirst(str_replace('_', ' ', $session['type'])); ?></h3>
            <div class="session-details">
                <div class="detail-item">
                    <span class="detail-label">Pilote:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($session['pilote_prenom'] . ' ' . $session['pilote_nom']); ?> 
                    (<?php echo $session['pilote_taille']; ?>m, <?php echo $session['pilote_poids']; ?>kg)</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Moto:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($session['moto_marque'] . ' ' . $session['moto_modele']); ?> 
                    (<?php echo $session['moto_cylindree']; ?>cc) - <?php echo ($session['moto_type'] === 'race') ? 'Course' : 'Origine'; ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Circuit:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($session['circuit_nom']); ?> 
                    <?php echo !empty($session['circuit_pays']) ? '(' . htmlspecialchars($session['circuit_pays']) . ')' : ''; ?>
                    <?php echo !empty($session['circuit_longueur']) ? ' - ' . htmlspecialchars($session['circuit_longueur']) . ' km' : ''; ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Conditions:</span>
                    <span class="detail-value"><?php echo !empty($session['conditions']) ? htmlspecialchars($session['conditions']) : 'Non spécifiées'; ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="session-content">
        <div class="session-tabs">
            <button class="tab-button active" data-tab="chronos">Chronos</button>
            <button class="tab-button" data-tab="reglages">Réglages</button>
            <button class="tab-button" data-tab="donnees-tech">Données Techniques</button>
            <button class="tab-button" data-tab="remarques">Remarques Pilote</button>
            <button class="tab-button" data-tab="ia">Recommandations IA</button>
            <button class="tab-button" data-tab="circuit">Détails Circuit</button>
        </div>
        
        <div class="tab-content">
            <!-- Onglet Chronos -->
            <div class="tab-pane active" id="chronos">
                <div class="chronos-container">
                    <div class="chronos-list">
                        <h3>Chronos</h3>
                        
                        <?php if ($chronos && $chronos->num_rows > 0): ?>
                            <div class="chronos-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Tour</th>
                                            <th>Temps</th>
                                            <th>Écart</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $best_time = null;
                                        $chronos_array = [];
                                        
                                        while ($chrono = $chronos->fetch_assoc()) {
                                            $chronos_array[] = $chrono;
                                            if ($best_time === null || $chrono['temps_secondes'] < $best_time) {
                                                $best_time = $chrono['temps_secondes'];
                                            }
                                        }
                                        
                                        foreach ($chronos_array as $chrono) {
                                            $ecart = $chrono['temps_secondes'] - $best_time;
                                            $class = '';
                                            
                                            if ($ecart === 0) {
                                                $class = 'best-time';
                                            } else if ($ecart < 0.5) {
                                                $class = 'good-time';
                                            } else if ($ecart > 2) {
                                                $class = 'bad-time';
                                            }
                                            
                                            echo '<tr class="' . $class . '">';
                                            echo '<td>' . $chrono['tour_numero'] . '</td>';
                                            echo '<td>' . $chrono['temps'] . '</td>';
                                            echo '<td>' . ($ecart === 0 ? '-' : '+' . number_format($ecart, 3)) . '</td>';
                                            echo '</tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Aucun chrono enregistré pour cette session.
                            </div>
                        <?php endif; ?>
                        
                        <div class="add-chrono-form mt-3">
                            <h4>Ajouter un chrono</h4>
                            <form method="POST" action="" class="form-inline">
                                <input type="hidden" name="action" value="add_chrono">
                                <div class="form-group mr-2">
                                    <label for="tour_numero" class="mr-1">Tour:</label>
                                    <input type="number" id="tour_numero" name="tour_numero" min="1" max="99" required style="width: 60px;">
                                </div>
                                <div class="form-group mr-2">
                                    <label for="temps" class="mr-1">Temps:</label>
                                    <input type="text" id="temps" name="temps" placeholder="MM:SS.mmm" pattern="[0-9]{1,2}:[0-9]{2}.[0-9]{1,3}" required>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary">Ajouter</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="chronos-chart">
                        <h3>Graphique d'évolution</h3>
                        <div class="chart-container">
                            <?php if ($chronos && $chronos->num_rows > 0): ?>
                                <div class="chart-placeholder">
                                    <i class="fas fa-chart-line"></i>
                                    <p>Graphique d'évolution des chronos</p>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Ajoutez des chronos pour voir le graphique d'évolution.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Onglet Réglages -->
            <div class="tab-pane" id="reglages">
                <div class="reglages-container">
                    <div class="reglages-initiaux">
                        <h3>Réglages Initiaux</h3>
                        <div class="reglages-content">
                            <?php if (!empty($session['reglages_initiaux'])): ?>
                                <pre><?php echo htmlspecialchars($session['reglages_initiaux']); ?></pre>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Aucun réglage initial spécifié.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="reglages-standards">
                        <h3>Réglages Standards de la Moto</h3>
                        <div class="reglages-content">
                            <?php if (!empty($session['reglages_standards'])): ?>
                                <pre><?php echo htmlspecialchars($session['reglages_standards']); ?></pre>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Aucun réglage standard spécifié pour cette moto.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="reglages-modifications mt-3">
                    <h3>Modifications de Réglages</h3>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Les modifications de réglages seront affichées ici lorsque des recommandations IA seront validées.
                    </div>
                </div>
            </div>
            
            <!-- Onglet Données Techniques -->
            <div class="tab-pane" id="donnees-tech">
                <div class="tech-data-container">
                    <div class="tech-data-tabs">
                        <button class="tech-tab-button active" data-tech-tab="suspension">Suspension</button>
                        <button class="tech-tab-button" data-tech-tab="transmission">Transmission</button>
                        <button class="tech-tab-button" data-tech-tab="pneumatiques">Pneumatiques</button>
                        <button class="tech-tab-button" data-tech-tab="electronique">Électronique</button>
                    </div>
                    
                    <div class="tech-tab-content">
                        <!-- Onglet Suspension -->
                        <div class="tech-tab-pane active" id="suspension">
                            <h3>Données de Suspension</h3>
                            
                            <div class="tech-data-grid">
                                <div class="tech-data-section">
                                    <h4>Fourche Avant</h4>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Précharge:</span>
                                        <span class="tech-data-value"><?php echo isset($tech_data['precharge_avant']) ? htmlspecialchars($tech_data['precharge_avant']) . ' mm/tours' : 'Non spécifié'; ?></span>
                                    </div>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Compression basse vitesse:</span>
                                        <span class="tech-data-value"><?php echo isset($tech_data['compression_basse_avant']) ? htmlspecialchars($tech_data['compression_basse_avant']) . ' clics' : 'Non spécifié'; ?></span>
                                    </div>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Compression haute vitesse:</span>
                                        <span class="tech-data-value"><?php echo isset($tech_data['compression_haute_avant']) ? htmlspecialchars($tech_data['compression_haute_avant']) . ' clics' : 'Non spécifié'; ?></span>
                                    </div>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Détente:</span>
                                        <span class="tech-data-value"><?php echo isset($tech_data['detente_avant']) ? htmlspecialchars($tech_data['detente_avant']) . ' clics' : 'Non spécifié'; ?></span>
                                    </div>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Hauteur de fourche:</span>
                                        <span class="tech-data-value"><?php echo isset($tech_data['hauteur_fourche']) ? htmlspecialchars($tech_data['hauteur_fourche']) . ' mm' : 'Non spécifié'; ?></span>
                                    </div>
                                </div>
                                
                                <div class="tech-data-section">
                                    <h4>Amortisseur Arrière</h4>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Précharge:</span>
                                        <span class="tech-data-value"><?php echo isset($tech_data['precharge_arriere']) ? htmlspecialchars($tech_data['precharge_arriere']) . ' mm/tours' : 'Non spécifié'; ?></span>
                                    </div>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Compression basse vitesse:</span>
                                        <span class="tech-data-value"><?php echo isset($tech_data['compression_basse_arriere']) ? htmlspecialchars($tech_data['compression_basse_arriere']) . ' clics' : 'Non spécifié'; ?></span>
                                    </div>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Compression haute vitesse:</span>
                                        <span class="tech-data-value"><?php echo isset($tech_data['compression_haute_arriere']) ? htmlspecialchars($tech_data['compression_haute_arriere']) . ' clics' : 'Non spécifié'; ?></span>
                                    </div>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Détente:</span>
                                        <span class="tech-data-value"><?php echo isset($tech_data['detente_arriere']) ? htmlspecialchars($tech_data['detente_arriere']) . ' clics' : 'Non spécifié'; ?></span>
                                    </div>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Hauteur arrière:</span>
                                        <span class="tech-data-value"><?php echo isset($tech_data['hauteur_arriere']) ? htmlspecialchars($tech_data['hauteur_arriere']) . ' mm' : 'Non spécifié'; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Onglet Transmission -->
                        <div class="tech-tab-pane" id="transmission">
                            <h3>Données de Transmission</h3>
                            
                            <div class="tech-data-grid">
                                <div class="tech-data-section">
                                    <h4>Rapport de Transmission</h4>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Pignon avant:</span>
                                        <span class="tech-data-value"><?php echo isset($session['pignon_avant']) ? htmlspecialchars($session['pignon_avant']) . ' dents' : 'Non spécifié'; ?></span>
                                    </div>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Pignon arrière / Couronne:</span>
                                        <span class="tech-data-value"><?php echo isset($session['pignon_arriere']) ? htmlspecialchars($session['pignon_arriere']) . ' dents' : 'Non spécifié'; ?></span>
                                    </div>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Rapport final:</span>
                                        <span class="tech-data-value">
                                            <?php 
                                            if (isset($session['pignon_avant']) && isset($session['pignon_arriere']) && $session['pignon_avant'] > 0) {
                                                $rapport = round($session['pignon_arriere'] / $session['pignon_avant'], 2);
                                                echo $rapport;
                                            } else {
                                                echo 'Non spécifié';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="tech-data-section">
                                    <h4>Chaîne</h4>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Longueur de chaîne:</span>
                                        <span class="tech-data-value"><?php echo isset($session['longueur_chaine']) ? htmlspecialchars($session['longueur_chaine']) . ' maillons' : 'Non spécifié'; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Onglet Pneumatiques -->
                        <div class="tech-tab-pane" id="pneumatiques">
                            <h3>Données des Pneumatiques</h3>
                            
                            <div class="tech-data-grid">
                                <div class="tech-data-section">
                                    <h4>Pneu Avant</h4>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Type:</span>
                                        <span class="tech-data-value">
                                            <?php 
                                            if (isset($tech_data['type_pneu_avant'])) {
                                                $type_map = [
                                                    'slick' => 'Slick',
                                                    'pluie' => 'Pluie',
                                                    'mixte' => 'Mixte',
                                                    'standard' => 'Standard'
                                                ];
                                                echo $type_map[$tech_data['type_pneu_avant']] ?? $tech_data['type_pneu_avant'];
                                            } else {
                                                echo 'Non spécifié';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Pression à froid:</span>
                                        <span class="tech-data-value"><?php echo isset($tech_data['pression_avant_froid']) ? htmlspecialchars($tech_data['pression_avant_froid']) . ' bar' : 'Non spécifié'; ?></span>
                                    </div>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Pression à chaud:</span>
                                        <span class="tech-data-value"><?php echo isset($tech_data['pression_avant_chaud']) ? htmlspecialchars($tech_data['pression_avant_chaud']) . ' bar' : 'Non spécifié'; ?></span>
                                    </div>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Température:</span>
                                        <span class="tech-data-value"><?php echo isset($tech_data['temperature_pneu_avant']) ? htmlspecialchars($tech_data['temperature_pneu_avant']) . ' °C' : 'Non spécifié'; ?></span>
                                    </div>
                                </div>
                                
                                <div class="tech-data-section">
                                    <h4>Pneu Arrière</h4>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Type:</span>
                                        <span class="tech-data-value">
                                            <?php 
                                            if (isset($tech_data['type_pneu_arriere'])) {
                                                $type_map = [
                                                    'slick' => 'Slick',
                                                    'pluie' => 'Pluie',
                                                    'mixte' => 'Mixte',
                                                    'standard' => 'Standard'
                                                ];
                                                echo $type_map[$tech_data['type_pneu_arriere']] ?? $tech_data['type_pneu_arriere'];
                                            } else {
                                                echo 'Non spécifié';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Pression à froid:</span>
                                        <span class="tech-data-value"><?php echo isset($tech_data['pression_arriere_froid']) ? htmlspecialchars($tech_data['pression_arriere_froid']) . ' bar' : 'Non spécifié'; ?></span>
                                    </div>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Pression à chaud:</span>
                                        <span class="tech-data-value"><?php echo isset($tech_data['pression_arriere_chaud']) ? htmlspecialchars($tech_data['pression_arriere_chaud']) . ' bar' : 'Non spécifié'; ?></span>
                                    </div>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Température:</span>
                                        <span class="tech-data-value"><?php echo isset($tech_data['temperature_pneu_arriere']) ? htmlspecialchars($tech_data['temperature_pneu_arriere']) . ' °C' : 'Non spécifié'; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Onglet Électronique -->
                        <div class="tech-tab-pane" id="electronique">
                            <h3>Données Électroniques</h3>
                            
                            <div class="tech-data-grid">
                                <div class="tech-data-section">
                                    <h4>Aides à la conduite</h4>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Contrôle de traction:</span>
                                        <span class="tech-data-value"><?php echo isset($tech_data['traction_control']) ? 'Niveau ' . htmlspecialchars($tech_data['traction_control']) : 'Non spécifié'; ?></span>
                                    </div>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Anti-wheeling:</span>
                                        <span class="tech-data-value"><?php echo isset($tech_data['anti_wheeling']) ? 'Niveau ' . htmlspecialchars($tech_data['anti_wheeling']) : 'Non spécifié'; ?></span>
                                    </div>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Launch Control:</span>
                                        <span class="tech-data-value"><?php echo isset($tech_data['launch_control']) && $tech_data['launch_control'] ? 'Activé' : 'Désactivé'; ?></span>
                                    </div>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">ABS:</span>
                                        <span class="tech-data-value"><?php echo isset($tech_data['abs_active']) && $tech_data['abs_active'] ? 'Activé' : 'Désactivé'; ?></span>
                                    </div>
                                </div>
                                
                                <div class="tech-data-section">
                                    <h4>Moteur</h4>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Mode moteur:</span>
                                        <span class="tech-data-value"><?php echo isset($tech_data['mode_moteur']) ? htmlspecialchars($tech_data['mode_moteur']) : 'Non spécifié'; ?></span>
                                    </div>
                                    <div class="tech-data-item">
                                        <span class="tech-data-label">Frein moteur:</span>
                                        <span class="tech-data-value"><?php echo isset($tech_data['frein_moteur']) ? 'Niveau ' . htmlspecialchars($tech_data['frein_moteur']) : 'Non spécifié'; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Onglet Remarques Pilote -->
            <div class="tab-pane" id="remarques">
                <div class="remarques-container">
                    <h3>Remarques du Pilote</h3>
                    
                    <div class="add-remarque-form mb-3">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add_remarque">
                            <div class="form-group">
                                <label for="remarque">Nouvelle remarque:</label>
                                <textarea id="remarque" name="remarque" rows="3" required placeholder="Ex: Moto dribble au virage 3 en sortie"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Ajouter la remarque</button>
                        </form>
                    </div>
                    
                    <div class="remarques-list">
                        <?php if ($remarques && $remarques->num_rows > 0): ?>
                            <?php while ($remarque = $remarques->fetch_assoc()): ?>
                                <div class="remarque-item">
                                    <div class="remarque-header">
                                        <span class="remarque-date"><?php echo date('d/m/Y H:i', strtotime($remarque['created_at'])); ?></span>
                                    </div>
                                    <div class="remarque-content">
                                        <?php echo nl2br(htmlspecialchars($remarque['remarque'])); ?>
                                    </div>
                                    <div class="remarque-actions">
                                        <a href="<?php echo url('chatgpt/index.php?session_id=' . $id . '&probleme=' . urlencode($remarque['remarque'])); ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-robot"></i> Demander conseil à l'IA
                                        </a>
                                        <a href="<?php echo url('experts/poser_question.php?session_id=' . $id . '&question=' . urlencode($remarque['remarque'])); ?>" class="btn btn-sm">
                                            <i class="fas fa-user-tie"></i> Consulter un expert
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Aucune remarque du pilote pour cette session.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Onglet Recommandations IA -->
            <div class="tab-pane" id="ia">
                <div class="ia-container">
                    <h3>Recommandations IA</h3>
                    
                    <div class="ia-actions mb-3">
                        <a href="<?php echo url('chatgpt/index.php?session_id=' . $id); ?>" class="btn btn-primary">
                            <i class="fas fa-robot"></i> Demander une nouvelle recommandation
                        </a>
                        <a href="<?php echo url('experts/poser_question.php?session_id=' . $id); ?>" class="btn">
                            <i class="fas fa-user-tie"></i> Poser une question aux experts
                        </a>
                    </div>
                    
                    <div class="recommandations-list">
                        <?php if ($recommandations && $recommandations->num_rows > 0): ?>
                            <?php while ($recommandation = $recommandations->fetch_assoc()): ?>
                                <?php
                                $validationClass = '';
                                $validationBadge = '';
                                
                                if ($recommandation['validation'] === 'positif') {
                                    $validationClass = 'validation-positive';
                                    $validationBadge = '<span class="validation-badge positive">✅ Efficace</span>';
                                } else if ($recommandation['validation'] === 'negatif') {
                                    $validationClass = 'validation-negative';
                                    $validationBadge = '<span class="validation-badge negative">❌ Inefficace</span>';
                                } else if ($recommandation['validation'] === 'neutre') {
                                    $validationClass = 'validation-neutral';
                                    $validationBadge = '<span class="validation-badge neutral">⚠️ Résultat mitigé</span>';
                                }
                                
                                $sourceLabel = $recommandation['source'] === 'chatgpt' ? 'IA (ChatGPT)' : 'Expert';
                                $sourceIcon = $recommandation['source'] === 'chatgpt' ? 'fas fa-robot' : 'fas fa-user-tie';
                                ?>
                                
                                <div class="recommandation-item <?php echo $validationClass; ?>">
                                    <div class="recommandation-header">
                                        <span class="recommandation-source"><i class="<?php echo $sourceIcon; ?>"></i> <?php echo $sourceLabel; ?></span>
                                        <span class="recommandation-date"><?php echo date('d/m/Y H:i', strtotime($recommandation['created_at'])); ?></span>
                                    </div>
                                    <div class="recommandation-problem">
                                        <strong>Problème :</strong> <?php echo htmlspecialchars($recommandation['probleme']); ?>
                                    </div>
                                    <div class="recommandation-solution">
                                        <strong>Solution :</strong> <?php echo nl2br(htmlspecialchars($recommandation['solution'])); ?>
                                    </div>
                                    <div class="recommandation-actions">
                                        <?php if ($recommandation['validation']): ?>
                                            <div class="recommandation-validation">
                                                <?php echo $validationBadge; ?>
                                            </div>
                                        <?php else: ?>
                                            <form method="POST" action="/telemoto/api/valider_recommandation.php" class="d-inline">
                                                <input type="hidden" name="session_id" value="<?php echo $id; ?>">
                                                <input type="hidden" name="probleme" value="<?php echo htmlspecialchars($recommandation['probleme']); ?>">
                                                <input type="hidden" name="solution" value="<?php echo htmlspecialchars($recommandation['solution']); ?>">
                                                <button type="submit" name="validation" value="positif" class="action-btn action-positive">✅ Positif</button>
                                                <button type="submit" name="validation" value="neutre" class="action-btn action-neutral">⚠️ Neutre</button>
                                                <button type="submit" name="validation" value="negatif" class="action-btn action-negative">❌ Négatif</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Aucune recommandation IA pour cette session.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Onglet Détails Circuit -->
            <div class="tab-pane" id="circuit">
                <div class="circuit-container">
                    <h3>Détails du Circuit</h3>
                    
                    <div class="circuit-info">
                        <div class="circuit-name">
                            <h4><?php echo htmlspecialchars($session['circuit_nom']); ?></h4>
                            <p>
                                <?php echo !empty($session['circuit_pays']) ? htmlspecialchars($session['circuit_pays']) : ''; ?>
                                <?php echo !empty($session['circuit_longueur']) ? ' - ' . htmlspecialchars($session['circuit_longueur']) . ' km' : ''; ?>
                            </p>
                        </div>
                        
                        <div class="circuit-map">
                            <div class="circuit-map-placeholder">
                                <i class="fas fa-map"></i>
                                <p>Tracé du circuit</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="circuit-virages mt-3">
                        <h4>Détails des Virages</h4>
                        <?php if (!empty($session['details_virages'])): ?>
                            <div class="virages-content">
                                <?php 
                                $virages = explode("\n", $session['details_virages']);
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
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Aucun détail de virage spécifié pour ce circuit.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.session-header {
    margin-bottom: 1.5rem;
}
.session-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 0.5rem;
    margin-top: 0.5rem;
}
.detail-item {
    display: flex;
    align-items: baseline;
}
.detail-label {
    font-weight: bold;
    color: var(--primary-color);
    margin-right: 0.5rem;
    min-width: 80px;
}
.session-tabs {
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
.chronos-container, .reglages-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}
.chart-container, .reglages-content {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: var(--border-radius);
    padding: 1rem;
    border: 1px solid var(--light-gray);
    height: 300px;
    position: relative;
}
.chart-placeholder {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    color: var(--light-gray);
}
.chart-placeholder i {
    font-size: 3rem;
    margin-bottom: 1rem;
}
.reglages-content pre {
    white-space: pre-wrap;
    font-family: inherit;
    margin: 0;
}
.form-inline {
    display: flex;
    align-items: center;
}
.mr-1 {
    margin-right: 0.25rem;
}
.mr-2 {
    margin-right: 0.5rem;
}
.best-time {
    color: var(--success-color);
    font-weight: bold;
}
.good-time {
    color: var(--primary-color);
}
.bad-time {
    color: var(--danger-color);
}
.remarque-item, .recommandation-item {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: var(--border-radius);
    padding: 1rem;
    margin-bottom: 1rem;
    border: 1px solid var(--light-gray);
}
.remarque-header, .recommandation-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    color: var(--dark-gray);
}
.remarque-content, .recommandation-problem {
    margin-bottom: 0.5rem;
}
.recommandation-solution {
    margin-bottom: 0.5rem;
    color: var(--primary-color);
}
.remarque-actions, .recommandation-actions {
    margin-top: 1rem;
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}
.action-btn {
    background: none;
    border: 1px solid var(--light-gray);
    padding: 0.25rem 0.5rem;
    border-radius: var(--border-radius);
    cursor: pointer;
    font-size: 0.9rem;
}
.action-positive {
    color: var(--success-color);
}
.action-negative {
    color: var(--danger-color);
}
.action-neutral {
    color: var(--warning-color);
}
.validation-badge {
    display: inline-block;
    padding: 0.2rem 0.5rem;
    border-radius: var(--border-radius);
    font-size: 0.8rem;
}
.validation-badge.positive {
    background-color: rgba(0, 200, 83, 0.2);
    color: var(--success-color);
}
.validation-badge.negative {
    background-color: rgba(255, 61, 0, 0.2);
    color: var(--danger-color);
}
.validation-badge.neutral {
    background-color: rgba(255, 193, 7, 0.2);
    color: var(--warning-color);
}
.validation-positive {
    border-left: 3px solid var(--success-color);
}
.validation-negative {
    border-left: 3px solid var(--danger-color);
}
.validation-neutral {
    border-left: 3px solid var(--warning-color);
}
.circuit-info {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 1.5rem;
}
.circuit-map {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: var(--border-radius);
    border: 1px solid var(--light-gray);
    height: 200px;
    position: relative;
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
    margin-bottom: 0.5rem;
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
.tech-data-tabs {
    display: flex;
    border-bottom: 1px solid var(--light-gray);
    margin-bottom: 1rem;
    overflow-x: auto;
}
.tech-tab-button {
    background: none;
    border: none;
    padding: 0.5rem 1rem;
    cursor: pointer;
    color: var(--text-color);
    font-weight: bold;
    position: relative;
    white-space: nowrap;
}
.tech-tab-button.active {
    color: var(--primary-color);
}
.tech-tab-button.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background-color: var(--primary-color);
}
.tech-tab-pane {
    display: none;
    padding: 1rem 0;
}
.tech-tab-pane.active {
    display: block;
}
.tech-data-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}
.tech-data-section {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: var(--border-radius);
    padding: 1rem;
    border: 1px solid var(--light-gray);
}
.tech-data-section h4 {
    margin-top: 0;
    margin-bottom: 1rem;
    color: var(--primary-color);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding-bottom: 0.5rem;
}
.tech-data-item {
    margin-bottom: 0.5rem;
    display: flex;
    justify-content: space-between;
}
.tech-data-label {
    font-weight: bold;
    color: var(--dark-gray);
}
.tech-data-value {
    color: var(--text-color);
}
.mb-3 {
    margin-bottom: 1rem;
}
.mt-3 {
    margin-top: 1rem;
}
@media (max-width: 768px) {
    .chronos-container, .reglages-container, .tech-data-grid, .circuit-info {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des onglets principaux
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
    
    // Gestion des onglets techniques
    const techTabButtons = document.querySelectorAll('.tech-tab-button');
    const techTabPanes = document.querySelectorAll('.tech-tab-pane');
    
    techTabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tech-tab');
            
            // Désactiver tous les onglets
            techTabButtons.forEach(btn => btn.classList.remove('active'));
            techTabPanes.forEach(pane => pane.classList.remove('active'));
            
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
