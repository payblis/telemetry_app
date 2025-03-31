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

// Récupérer les sessions de l'utilisateur
$user_id = $_SESSION['user_id'];
$sql = "SELECT s.id, s.date, s.type, 
        p.nom as pilote_nom, p.prenom as pilote_prenom,
        m.marque as moto_marque, m.modele as moto_modele,
        c.nom as circuit_nom
        FROM sessions s
        JOIN pilotes p ON s.pilote_id = p.id
        JOIN motos m ON s.moto_id = m.id
        JOIN circuits c ON s.circuit_id = c.id
        ORDER BY s.date DESC, s.created_at DESC";

$result = $conn->query($sql);
$sessions = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sessions[] = $row;
    }
}

// Traitement de la comparaison
$session1 = null;
$session2 = null;
$session1_data = null;
$session2_data = null;
$chronos1 = [];
$chronos2 = [];
$remarques1 = [];
$remarques2 = [];
$recommandations1 = [];
$recommandations2 = [];

if (isset($_GET['session1']) && isset($_GET['session2'])) {
    $session1_id = intval($_GET['session1']);
    $session2_id = intval($_GET['session2']);
    
    // Récupérer les données de la session 1
    $sql = "SELECT s.*, 
            p.nom as pilote_nom, p.prenom as pilote_prenom,
            m.marque as moto_marque, m.modele as moto_modele,
            c.nom as circuit_nom
            FROM sessions s
            JOIN pilotes p ON s.pilote_id = p.id
            JOIN motos m ON s.moto_id = m.id
            JOIN circuits c ON s.circuit_id = c.id
            WHERE s.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $session1_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $session1 = $result->fetch_assoc();
        
        // Récupérer les données techniques
        $sql = "SELECT * FROM donnees_techniques_session WHERE session_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $session1_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $session1_data = $result->fetch_assoc();
        }
        
        // Récupérer les chronos
        $sql = "SELECT * FROM chronos WHERE session_id = ? ORDER BY tour_numero";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $session1_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $chronos1[] = $row;
            }
        }
        
        // Récupérer les remarques
        $sql = "SELECT * FROM remarques_pilote WHERE session_id = ? ORDER BY created_at";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $session1_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $remarques1[] = $row;
            }
        }
        
        // Récupérer les recommandations
        $sql = "SELECT * FROM recommandations WHERE session_id = ? ORDER BY created_at";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $session1_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $recommandations1[] = $row;
            }
        }
    }
    
    // Récupérer les données de la session 2
    $sql = "SELECT s.*, 
            p.nom as pilote_nom, p.prenom as pilote_prenom,
            m.marque as moto_marque, m.modele as moto_modele,
            c.nom as circuit_nom
            FROM sessions s
            JOIN pilotes p ON s.pilote_id = p.id
            JOIN motos m ON s.moto_id = m.id
            JOIN circuits c ON s.circuit_id = c.id
            WHERE s.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $session2_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $session2 = $result->fetch_assoc();
        
        // Récupérer les données techniques
        $sql = "SELECT * FROM donnees_techniques_session WHERE session_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $session2_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $session2_data = $result->fetch_assoc();
        }
        
        // Récupérer les chronos
        $sql = "SELECT * FROM chronos WHERE session_id = ? ORDER BY tour_numero";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $session2_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $chronos2[] = $row;
            }
        }
        
        // Récupérer les remarques
        $sql = "SELECT * FROM remarques_pilote WHERE session_id = ? ORDER BY created_at";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $session2_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $remarques2[] = $row;
            }
        }
        
        // Récupérer les recommandations
        $sql = "SELECT * FROM recommandations WHERE session_id = ? ORDER BY created_at";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $session2_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $recommandations2[] = $row;
            }
        }
    }
}

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="comparaison-container">
    <h1 class="comparaison-title">Mode Comparaison de Sessions</h1>
    
    <div class="comparaison-intro">
        <p>Comparez deux sessions pour analyser les différences de réglages, de chronos et de ressentis. Sélectionnez deux sessions à comparer ci-dessous.</p>
    </div>
    
    <div class="comparaison-form">
        <form method="GET" action="<?php echo url('comparaison/'); ?>" class="form-inline">
            <div class="form-group">
                <label for="session1">Session 1:</label>
                <select name="session1" id="session1" class="form-control" required>
                    <option value="">Sélectionner une session</option>
                    <?php foreach ($sessions as $session): ?>
                        <option value="<?php echo $session['id']; ?>" <?php echo (isset($_GET['session1']) && $_GET['session1'] == $session['id']) ? 'selected' : ''; ?>>
                            <?php echo date('d/m/Y', strtotime($session['date'])) . ' - ' . $session['circuit_nom'] . ' - ' . $session['moto_marque'] . ' ' . $session['moto_modele']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="session2">Session 2:</label>
                <select name="session2" id="session2" class="form-control" required>
                    <option value="">Sélectionner une session</option>
                    <?php foreach ($sessions as $session): ?>
                        <option value="<?php echo $session['id']; ?>" <?php echo (isset($_GET['session2']) && $_GET['session2'] == $session['id']) ? 'selected' : ''; ?>>
                            <?php echo date('d/m/Y', strtotime($session['date'])) . ' - ' . $session['circuit_nom'] . ' - ' . $session['moto_marque'] . ' ' . $session['moto_modele']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Comparer</button>
        </form>
    </div>
    
    <?php if ($session1 && $session2): ?>
        <div class="comparaison-results">
            <div class="comparaison-header">
                <div class="comparaison-session">
                    <h3><?php echo date('d/m/Y', strtotime($session1['date'])); ?> - <?php echo $session1['circuit_nom']; ?></h3>
                    <p><?php echo $session1['moto_marque'] . ' ' . $session1['moto_modele']; ?> - <?php echo ucfirst(str_replace('_', ' ', $session1['type'])); ?></p>
                    <p>Conditions: <?php echo $session1['conditions'] ?? 'Non spécifiées'; ?></p>
                </div>
                
                <div class="comparaison-vs">VS</div>
                
                <div class="comparaison-session">
                    <h3><?php echo date('d/m/Y', strtotime($session2['date'])); ?> - <?php echo $session2['circuit_nom']; ?></h3>
                    <p><?php echo $session2['moto_marque'] . ' ' . $session2['moto_modele']; ?> - <?php echo ucfirst(str_replace('_', ' ', $session2['type'])); ?></p>
                    <p>Conditions: <?php echo $session2['conditions'] ?? 'Non spécifiées'; ?></p>
                </div>
            </div>
            
            <div class="comparaison-tabs">
                <button class="tab-button active" data-tab="chronos">Chronos</button>
                <button class="tab-button" data-tab="reglages">Réglages</button>
                <button class="tab-button" data-tab="remarques">Remarques</button>
                <button class="tab-button" data-tab="recommandations">Recommandations</button>
            </div>
            
            <div class="tab-content active" id="chronos">
                <h2>Comparaison des Chronos</h2>
                
                <div class="chronos-chart-container">
                    <canvas id="chronosChart"></canvas>
                </div>
                
                <div class="chronos-table-container">
                    <table class="comparaison-table">
                        <thead>
                            <tr>
                                <th>Tour</th>
                                <th>Session 1</th>
                                <th>Session 2</th>
                                <th>Différence</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $max_tours = max(count($chronos1), count($chronos2));
                            for ($i = 0; $i < $max_tours; $i++): 
                                $temps1 = isset($chronos1[$i]) ? $chronos1[$i]['temps'] : '-';
                                $temps2 = isset($chronos2[$i]) ? $chronos2[$i]['temps'] : '-';
                                $diff = '';
                                
                                if (isset($chronos1[$i]) && isset($chronos2[$i])) {
                                    $diff_sec = $chronos1[$i]['temps_secondes'] - $chronos2[$i]['temps_secondes'];
                                    $diff = number_format($diff_sec, 3) . 's';
                                    $diff_class = $diff_sec > 0 ? 'negative' : ($diff_sec < 0 ? 'positive' : '');
                                }
                            ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo $temps1; ?></td>
                                    <td><?php echo $temps2; ?></td>
                                    <td class="diff <?php echo $diff_class ?? ''; ?>"><?php echo $diff; ?></td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="tab-content" id="reglages">
                <h2>Comparaison des Réglages</h2>
                
                <?php if ($session1_data && $session2_data): ?>
                    <div class="reglages-section">
                        <h3>Suspension</h3>
                        <table class="comparaison-table">
                            <thead>
                                <tr>
                                    <th>Réglage</th>
                                    <th>Session 1</th>
                                    <th>Session 2</th>
                                    <th>Différence</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $suspension_fields = [
                                    'precharge_avant' => 'Précharge avant',
                                    'precharge_arriere' => 'Précharge arrière',
                                    'compression_basse_avant' => 'Compression basse avant',
                                    'compression_haute_avant' => 'Compression haute avant',
                                    'compression_basse_arriere' => 'Compression basse arrière',
                                    'compression_haute_arriere' => 'Compression haute arrière',
                                    'detente_avant' => 'Détente avant',
                                    'detente_arriere' => 'Détente arrière',
                                    'hauteur_fourche' => 'Hauteur fourche',
                                    'hauteur_arriere' => 'Hauteur arrière'
                                ];
                                
                                foreach ($suspension_fields as $field => $label):
                                    $val1 = $session1_data[$field] ?? '-';
                                    $val2 = $session2_data[$field] ?? '-';
                                    $diff = '';
                                    $diff_class = '';
                                    
                                    if (is_numeric($val1) && is_numeric($val2)) {
                                        $diff = $val1 - $val2;
                                        $diff_class = $diff > 0 ? 'positive' : ($diff < 0 ? 'negative' : '');
                                        $diff = ($diff > 0 ? '+' : '') . $diff;
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo $label; ?></td>
                                        <td><?php echo $val1; ?></td>
                                        <td><?php echo $val2; ?></td>
                                        <td class="diff <?php echo $diff_class; ?>"><?php echo $diff; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="reglages-section">
                        <h3>Châssis / Géométrie</h3>
                        <table class="comparaison-table">
                            <thead>
                                <tr>
                                    <th>Réglage</th>
                                    <th>Session 1</th>
                                    <th>Session 2</th>
                                    <th>Différence</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $chassis_fields = [
                                    'empattement' => 'Empattement',
                                    'angle_chasse' => 'Angle de chasse',
                                    'chasse' => 'Chasse',
                                    'offset_tes_fourche' => 'Offset tés de fourche'
                                ];
                                
                                foreach ($chassis_fields as $field => $label):
                                    $val1 = $session1_data[$field] ?? '-';
                                    $val2 = $session2_data[$field] ?? '-';
                                    $diff = '';
                                    $diff_class = '';
                                    
                                    if (is_numeric($val1) && is_numeric($val2)) {
                                        $diff = $val1 - $val2;
                                        $diff_class = $diff > 0 ? 'positive' : ($diff < 0 ? 'negative' : '');
                                        $diff = ($diff > 0 ? '+' : '') . $diff;
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo $label; ?></td>
                                        <td><?php echo $val1; ?></td>
                                        <td><?php echo $val2; ?></td>
                                        <td class="diff <?php echo $diff_class; ?>"><?php echo $diff; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="reglages-section">
                        <h3>Pneumatiques</h3>
                        <table class="comparaison-table">
                            <thead>
                                <tr>
                                    <th>Réglage</th>
                                    <th>Session 1</th>
                                    <th>Session 2</th>
                                    <th>Différence</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $pneus_fields = [
                                    'pression_avant_froid' => 'Pression avant (froid)',
                                    'pression_avant_chaud' => 'Pression avant (chaud)',
                                    'pression_arriere_froid' => 'Pression arrière (froid)',
                                    'pression_arriere_chaud' => 'Pression arrière (chaud)'
                                ];
                                
                                foreach ($pneus_fields as $field => $label):
                                    $val1 = $session1_data[$field] ?? '-';
                                    $val2 = $session2_data[$field] ?? '-';
                                    $diff = '';
                                    $diff_class = '';
                                    
                                    if (is_numeric($val1) && is_numeric($val2)) {
                                        $diff = $val1 - $val2;
                                        $diff_class = $diff > 0 ? 'positive' : ($diff < 0 ? 'negative' : '');
                                        $diff = ($diff > 0 ? '+' : '') . $diff;
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo $label; ?></td>
                                        <td><?php echo $val1; ?></td>
                                        <td><?php echo $val2; ?></td>
                                        <td class="diff <?php echo $diff_class; ?>"><?php echo $diff; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Données techniques non disponibles pour une ou les deux sessions.
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="tab-content" id="remarques">
                <h2>Comparaison des Remarques</h2>
                
                <div class="remarques-container">
                    <div class="remarques-column">
                        <h3>Session 1</h3>
                        <?php if (!empty($remarques1)): ?>
                            <?php foreach ($remarques1 as $remarque): ?>
                                <div class="remarque-item">
                                    <div class="remarque-date"><?php echo date('d/m/Y H:i', strtotime($remarque['created_at'])); ?></div>
                                    <div class="remarque-text"><?php echo nl2br(htmlspecialchars($remarque['remarque'])); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">Aucune remarque pour cette session.</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="remarques-column">
                        <h3>Session 2</h3>
                        <?php if (!empty($remarques2)): ?>
                            <?php foreach ($remarques2 as $remarque): ?>
                                <div class="remarque-item">
                                    <div class="remarque-date"><?php echo date('d/m/Y H:i', strtotime($remarque['created_at'])); ?></div>
                                    <div class="remarque-text"><?php echo nl2br(htmlspecialchars($remarque['remarque'])); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">Aucune remarque pour cette session.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="tab-content" id="recommandations">
                <h2>Comparaison des Recommandations</h2>
                
                <div class="recommandations-container">
                    <div class="recommandations-column">
                        <h3>Session 1</h3>
                        <?php if (!empty($recommandations1)): ?>
                            <?php foreach ($recommandations1 as $recommandation): ?>
                                <div class="recommandation-item">
                                    <div class="recommandation-header">
                                        <span class="recommandation-source"><?php echo ucfirst($recommandation['source']); ?></span>
                                        <span class="recommandation-validation <?php echo $recommandation['validation'] ?? ''; ?>"><?php echo ucfirst($recommandation['validation'] ?? 'Non validée'); ?></span>
                                    </div>
                                    <div class="recommandation-probleme"><strong>Problème :</strong> <?php echo htmlspecialchars($recommandation['probleme']); ?></div>
                                    <div class="recommandation-solution"><strong>Solution :</strong> <?php echo nl2br(htmlspecialchars($recommandation['solution'])); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">Aucune recommandation pour cette session.</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="recommandations-column">
                        <h3>Session 2</h3>
                        <?php if (!empty($recommandations2)): ?>
                            <?php foreach ($recommandations2 as $recommandation): ?>
                                <div class="recommandation-item">
                                    <div class="recommandation-header">
                                        <span class="recommandation-source"><?php echo ucfirst($recommandation['source']); ?></span>
                                        <span class="recommandation-validation <?php echo $recommandation['validation'] ?? ''; ?>"><?php echo ucfirst($recommandation['validation'] ?? 'Non validée'); ?></span>
                                    </div>
                                    <div class="recommandation-probleme"><strong>Problème :</strong> <?php echo htmlspecialchars($recommandation['probleme']); ?></div>
                                    <div class="recommandation-solution"><strong>Solution :</strong> <?php echo nl2br(htmlspecialchars($recommandation['solution'])); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">Aucune recommandation pour cette session.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion des onglets
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Retirer la classe active de tous les boutons et contenus
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Ajouter la classe active au bouton cliqué
                    this.classList.add('active');
                    
                    // Afficher le contenu correspondant
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });
            
            // Graphique des chronos
            const chronosCtx = document.getElementById('chronosChart').getContext('2d');
            
            // Préparer les données pour le graphique
            const chronos1Data = <?php echo json_encode(array_map(function($chrono) { return $chrono['temps_secondes']; }, $chronos1)); ?>;
            const chronos2Data = <?php echo json_encode(array_map(function($chrono) { return $chrono['temps_secondes']; }, $chronos2)); ?>;
            const labels = [];
            
            const maxTours = Math.max(chronos1Data.length, chronos2Data.length);
            for (let i = 0; i < maxTours; i++) {
                labels.push('Tour ' + (i + 1));
            }
            
            new Chart(chronosCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Session 1',
                            data: chronos1Data,
                            borderColor: '#00a8ff',
                            backgroundColor: 'rgba(0, 168, 255, 0.1)',
                            tension: 0.4,
                            fill: false
                        },
                        {
                            label: 'Session 2',
                            data: chronos2Data,
                            borderColor: '#ff3e3e',
                            backgroundColor: 'rgba(255, 62, 62, 0.1)',
                            tension: 0.4,
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: '#e0e0e0'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    const minutes = Math.floor(value / 60);
                                    const seconds = (value % 60).toFixed(3);
                                    return context.dataset.label + ': ' + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            },
                            ticks: {
                                color: '#e0e0e0'
                            }
                        },
                        y: {
                            reverse: true,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            },
                            ticks: {
                                color: '#e0e0e0',
                                callback: function(value) {
                                    const minutes = Math.floor(value / 60);
                                    const seconds = (value % 60).toFixed(1);
                                    return minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
                                }
                            }
                        }
                    }
                }
            });
        });
        </script>
    <?php else: ?>
        <?php if (isset($_GET['session1']) || isset($_GET['session2'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> Erreur lors de la récupération des données des sessions. Veuillez réessayer.
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Veuillez sélectionner deux sessions à comparer.
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.comparaison-container {
    padding: 1rem 0;
}

.comparaison-title {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--primary-color);
    padding-bottom: 0.5rem;
}

.comparaison-intro {
    margin-bottom: 2rem;
    font-size: 1.1rem;
    line-height: 1.6;
}

.comparaison-form {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid var(--light-gray);
}

.form-inline {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: flex-end;
}

.form-group {
    flex: 1;
    min-width: 250px;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
}

.form-control {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid var(--light-gray);
    border-radius: var(--border-radius);
    background-color: rgba(255, 255, 255, 0.05);
    color: var(--text-color);
}

.comparaison-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    border: 1px solid var(--light-gray);
}

.comparaison-session {
    flex: 1;
}

.comparaison-session h3 {
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.comparaison-vs {
    font-size: 2rem;
    font-weight: bold;
    color: var(--secondary-color);
    margin: 0 2rem;
}

.comparaison-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 2rem;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 1rem;
}

.tab-button {
    background-color: var(--card-background);
    border: 1px solid var(--light-gray);
    color: var(--text-color);
    padding: 0.8rem 1.5rem;
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: all 0.3s;
    font-weight: bold;
}

.tab-button:hover {
    background-color: rgba(0, 168, 255, 0.1);
    border-color: var(--primary-color);
}

.tab-button.active {
    background-color: var(--primary-color);
    color: #000;
    border-color: var(--primary-color);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.tab-content h2 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 0.5rem;
}

.chronos-chart-container {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid var(--light-gray);
    height: 400px;
}

.chronos-table-container {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid var(--light-gray);
    overflow-x: auto;
}

.comparaison-table {
    width: 100%;
    border-collapse: collapse;
}

.comparaison-table th, .comparaison-table td {
    padding: 0.8rem;
    text-align: left;
    border-bottom: 1px solid var(--light-gray);
}

.comparaison-table th {
    background-color: rgba(58, 66, 85, 0.5);
    font-weight: bold;
    color: var(--primary-color);
}

.comparaison-table tr:hover {
    background-color: rgba(255, 255, 255, 0.05);
}

.diff.positive {
    color: #00c853;
}

.diff.negative {
    color: #ff3d00;
}

.reglages-section {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid var(--light-gray);
}

.reglages-section h3 {
    color: var(--primary-color);
    margin-bottom: 1rem;
    border-bottom: 1px solid var(--light-gray);
    padding-bottom: 0.5rem;
}

.remarques-container, .recommandations-container {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
}

@media (min-width: 768px) {
    .remarques-container, .recommandations-container {
        grid-template-columns: 1fr 1fr;
    }
}

.remarques-column h3, .recommandations-column h3 {
    color: var(--primary-color);
    margin-bottom: 1rem;
    text-align: center;
}

.remarque-item, .recommandation-item {
    background-color: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 1.5rem;
    margin-bottom: 1rem;
    border: 1px solid var(--light-gray);
}

.remarque-date {
    font-size: 0.9rem;
    color: var(--dark-gray);
    margin-bottom: 0.5rem;
}

.remarque-text {
    line-height: 1.6;
}

.recommandation-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.recommandation-source {
    font-weight: bold;
    color: var(--primary-color);
}

.recommandation-validation {
    padding: 0.2rem 0.5rem;
    border-radius: 20px;
    font-size: 0.8rem;
}

.recommandation-validation.positif {
    background-color: var(--success-color);
    color: #000;
}

.recommandation-validation.neutre {
    background-color: var(--warning-color);
    color: #000;
}

.recommandation-validation.negatif {
    background-color: var(--danger-color);
    color: white;
}

.recommandation-probleme, .recommandation-solution {
    margin-bottom: 0.5rem;
    line-height: 1.6;
}
</style>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
