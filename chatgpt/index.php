<?php
// Inclure les fichiers de configuration et l'API ChatGPT
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../api/chatgpt.php';

// Vérifier la connexion à la base de données
$conn = getDBConnection();

// Traitement de la demande de suggestion
$message = '';
$messageType = '';
$suggestion = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['probleme'])) {
    $probleme = trim($_POST['probleme']);
    $session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : null;
    
    // Validation des données
    if (empty($probleme)) {
        $message = "Veuillez décrire le problème rencontré";
        $messageType = "warning";
    } else if (!$session_id) {
        $message = "Session non spécifiée";
        $messageType = "danger";
    } else {
        // Récupérer les informations de la session
        $sql = "SELECT s.*, 
                p.nom as pilote_nom, p.prenom as pilote_prenom, p.taille as pilote_taille, p.poids as pilote_poids,
                m.marque as moto_marque, m.modele as moto_modele, m.cylindree as moto_cylindree,
                c.nom as circuit_nom, c.pays as circuit_pays
                FROM sessions s
                JOIN pilotes p ON s.pilote_id = p.id
                JOIN motos m ON s.moto_id = m.id
                JOIN circuits c ON s.circuit_id = c.id
                WHERE s.id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $session_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $session = $result->fetch_assoc();
            
            // Préparer le contexte pour ChatGPT
            $contexte = [
                'pilote' => $session['pilote_prenom'] . ' ' . $session['pilote_nom'] . 
                            ' (Taille: ' . $session['pilote_taille'] . 'm, Poids: ' . $session['pilote_poids'] . 'kg)',
                'moto' => $session['moto_marque'] . ' ' . $session['moto_modele'] . 
                          ' (' . $session['moto_cylindree'] . 'cc)',
                'circuit' => $session['circuit_nom'] . ', ' . $session['circuit_pays'],
                'conditions' => $session['conditions'] ?? 'Non spécifiées',
                'reglages_actuels' => $session['reglages_initiaux'] ?? 'Non spécifiés'
            ];
            
            // Obtenir une suggestion de ChatGPT
            $response = genererSuggestionReglage($probleme, $contexte);
            
            if ($response['success']) {
                $suggestion = $response['message'];
                
                // Enregistrer la recommandation dans la base de données
                $sql = "INSERT INTO recommandations (session_id, probleme, solution, source) 
                        VALUES (?, ?, ?, 'chatgpt')";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iss", $session_id, $probleme, $suggestion);
                
                if (!$stmt->execute()) {
                    $message = "Erreur lors de l'enregistrement de la recommandation : " . $conn->error;
                    $messageType = "danger";
                }
            } else {
                $message = "Erreur lors de la génération de la suggestion : " . ($response['error'] ?? 'Erreur inconnue');
                $messageType = "danger";
            }
        } else {
            $message = "Session introuvable";
            $messageType = "danger";
        }
    }
}

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <h2 class="card-title">Assistant IA - Suggestions de Réglages</h2>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div class="mb-3">
        <p>Décrivez le problème que vous rencontrez avec votre moto et notre IA vous proposera des réglages adaptés.</p>
    </div>
    
    <form method="POST" action="" class="needs-validation">
        <div class="form-group">
            <label for="session_id">Session</label>
            <select id="session_id" name="session_id" required>
                <option value="">Sélectionnez une session</option>
                <?php
                // Récupérer les sessions récentes
                $sql = "SELECT s.id, s.date, s.type, p.nom as pilote_nom, p.prenom as pilote_prenom, 
                        m.marque as moto_marque, m.modele as moto_modele, c.nom as circuit_nom
                        FROM sessions s
                        JOIN pilotes p ON s.pilote_id = p.id
                        JOIN motos m ON s.moto_id = m.id
                        JOIN circuits c ON s.circuit_id = c.id
                        ORDER BY s.date DESC
                        LIMIT 10";
                
                $result = $conn->query($sql);
                
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $selected = (isset($_POST['session_id']) && $_POST['session_id'] == $row['id']) ? 'selected' : '';
                        echo '<option value="' . $row['id'] . '" ' . $selected . '>';
                        echo date('d/m/Y', strtotime($row['date'])) . ' - ';
                        echo ucfirst(str_replace('_', ' ', $row['type'])) . ' - ';
                        echo htmlspecialchars($row['pilote_prenom'] . ' ' . $row['pilote_nom']) . ' - ';
                        echo htmlspecialchars($row['moto_marque'] . ' ' . $row['moto_modele']) . ' - ';
                        echo htmlspecialchars($row['circuit_nom']);
                        echo '</option>';
                    }
                }
                ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="probleme">Problème rencontré *</label>
            <textarea id="probleme" name="probleme" rows="4" required placeholder="Ex: La moto dribble au virage 3 en sortie"><?php echo htmlspecialchars($_POST['probleme'] ?? ''); ?></textarea>
            <small class="form-text">Décrivez précisément le problème, en indiquant si possible le virage concerné et les conditions.</small>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-robot"></i> Obtenir une suggestion
            </button>
        </div>
    </form>
    
    <?php if ($suggestion): ?>
        <div class="ai-recommendations mt-3">
            <h3 class="ai-title"><i class="fas fa-robot"></i> Recommandation IA</h3>
            <div class="recommendation-item">
                <div class="recommendation-problem">
                    <strong>Problème :</strong> <?php echo htmlspecialchars($probleme); ?>
                </div>
                <div class="recommendation-solution">
                    <strong>Solution :</strong> <?php echo nl2br(htmlspecialchars($suggestion)); ?>
                </div>
                <div class="recommendation-actions mt-2">
                    <p>Cette recommandation vous a-t-elle été utile ?</p>
                    <form method="POST" action="<?php echo url('api/valider_recommandation.php'); ?>" class="d-inline">
                        <input type="hidden" name="session_id" value="<?php echo $session_id; ?>">
                        <input type="hidden" name="probleme" value="<?php echo htmlspecialchars($probleme); ?>">
                        <input type="hidden" name="solution" value="<?php echo htmlspecialchars($suggestion); ?>">
                        <button type="submit" name="validation" value="positif" class="action-btn action-positive">✅ Positif</button>
                        <button type="submit" name="validation" value="neutre" class="action-btn action-neutral">⚠️ Neutre</button>
                        <button type="submit" name="validation" value="negatif" class="action-btn action-negative">❌ Négatif</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="mt-3">
        <h3>Recommandations récentes</h3>
        
        <?php
        // Récupérer les recommandations récentes
        $sql = "SELECT r.*, s.date, p.nom as pilote_nom, p.prenom as pilote_prenom, 
                m.marque as moto_marque, m.modele as moto_modele, c.nom as circuit_nom
                FROM recommandations r
                JOIN sessions s ON r.session_id = s.id
                JOIN pilotes p ON s.pilote_id = p.id
                JOIN motos m ON s.moto_id = m.id
                JOIN circuits c ON s.circuit_id = c.id
                WHERE r.source = 'chatgpt'
                ORDER BY r.created_at DESC
                LIMIT 5";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            echo '<div class="recommendations-list">';
            while ($row = $result->fetch_assoc()) {
                $validationClass = '';
                if ($row['validation'] === 'positif') {
                    $validationClass = 'validation-positive';
                } else if ($row['validation'] === 'negatif') {
                    $validationClass = 'validation-negative';
                } else if ($row['validation'] === 'neutre') {
                    $validationClass = 'validation-neutral';
                }
                
                echo '<div class="recommendation-item ' . $validationClass . '">';
                echo '<div class="recommendation-header">';
                echo '<span class="recommendation-date">' . date('d/m/Y H:i', strtotime($row['created_at'])) . '</span>';
                echo '<span class="recommendation-session">' . htmlspecialchars($row['pilote_prenom'] . ' ' . $row['pilote_nom']) . ' - ';
                echo htmlspecialchars($row['moto_marque'] . ' ' . $row['moto_modele']) . ' - ';
                echo htmlspecialchars($row['circuit_nom']) . '</span>';
                echo '</div>';
                echo '<div class="recommendation-problem"><strong>Problème :</strong> ' . htmlspecialchars($row['probleme']) . '</div>';
                echo '<div class="recommendation-solution"><strong>Solution :</strong> ' . nl2br(htmlspecialchars($row['solution'])) . '</div>';
                
                if ($row['validation']) {
                    echo '<div class="recommendation-validation">';
                    if ($row['validation'] === 'positif') {
                        echo '<span class="validation-badge positive">✅ Efficace</span>';
                    } else if ($row['validation'] === 'negatif') {
                        echo '<span class="validation-badge negative">❌ Inefficace</span>';
                    } else {
                        echo '<span class="validation-badge neutral">⚠️ Résultat mitigé</span>';
                    }
                    echo '</div>';
                }
                
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Aucune recommandation récente.
                  </div>';
        }
        ?>
    </div>
</div>

<style>
.recommendations-list {
    margin-top: 1rem;
}
.recommendation-item {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: var(--border-radius);
    padding: 1rem;
    margin-bottom: 1rem;
    border: 1px solid var(--light-gray);
}
.recommendation-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    color: var(--dark-gray);
}
.recommendation-problem {
    margin-bottom: 0.5rem;
}
.recommendation-solution {
    margin-bottom: 0.5rem;
    color: var(--primary-color);
}
.recommendation-validation {
    margin-top: 0.5rem;
    text-align: right;
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
</style>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
