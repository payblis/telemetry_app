<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../api/chatgpt.php';

// Vérifier la connexion à la base de données
$conn = getDBConnection();

// Traitement du formulaire
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question'])) {
    $question = trim($_POST['question']);
    $session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : null;
    
    // Validation des données
    if (empty($question)) {
        $message = "Veuillez saisir une question";
        $messageType = "warning";
    } else if (!$session_id) {
        $message = "Session non spécifiée";
        $messageType = "danger";
    } else {
        // Insérer la question dans la base de données
        $sql = "INSERT INTO questions_experts (session_id, question) VALUES (?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $session_id, $question);
        
        if ($stmt->execute()) {
            $message = "Votre question a été soumise aux experts. Vous recevrez une notification lorsqu'une réponse sera disponible.";
            $messageType = "success";
        } else {
            $message = "Erreur lors de l'enregistrement de la question : " . $conn->error;
            $messageType = "danger";
        }
    }
}

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <h2 class="card-title">Poser une Question aux Experts</h2>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div class="mb-3">
        <p>Posez une question technique à notre communauté d'experts en réglage moto. Nos experts vous répondront dans les meilleurs délais.</p>
    </div>
    
    <form method="POST" action="" class="needs-validation">
        <div class="form-group">
            <label for="session_id">Session concernée *</label>
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
            <label for="question">Votre question technique *</label>
            <textarea id="question" name="question" rows="5" required placeholder="Ex: Quelle est la meilleure solution pour régler une moto qui dribble en sortie de virage rapide ?"><?php echo htmlspecialchars($_POST['question'] ?? ''); ?></textarea>
            <small class="form-text">Soyez précis dans votre question pour obtenir une réponse adaptée à votre situation.</small>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Soumettre la question
            </button>
        </div>
    </form>
    
    <div class="mt-4">
        <h3>Vos Questions Récentes</h3>
        
        <?php
        // Récupérer les questions récentes de l'utilisateur
        // Note: Dans une version réelle, on filtrerait par user_id
        $sql = "SELECT q.*, s.date, p.nom as pilote_nom, p.prenom as pilote_prenom, 
                m.marque as moto_marque, m.modele as moto_modele, c.nom as circuit_nom
                FROM questions_experts q
                JOIN sessions s ON q.session_id = s.id
                JOIN pilotes p ON s.pilote_id = p.id
                JOIN motos m ON s.moto_id = m.id
                JOIN circuits c ON s.circuit_id = c.id
                ORDER BY q.created_at DESC
                LIMIT 5";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            echo '<div class="user-questions">';
            
            while ($row = $result->fetch_assoc()) {
                $statusClass = $row['reponse'] ? 'question-answered' : 'question-pending';
                $statusText = $row['reponse'] ? 'Répondue' : 'En attente';
                
                echo '<div class="question-item ' . $statusClass . '">';
                echo '<div class="question-header">';
                echo '<span class="question-date">' . date('d/m/Y H:i', strtotime($row['created_at'])) . '</span>';
                echo '<span class="question-status">' . $statusText . '</span>';
                echo '</div>';
                echo '<div class="question-context">' . htmlspecialchars($row['pilote_prenom'] . ' ' . $row['pilote_nom']) . ' - ';
                echo htmlspecialchars($row['moto_marque'] . ' ' . $row['moto_modele']) . ' - ';
                echo htmlspecialchars($row['circuit_nom']) . '</div>';
                echo '<div class="question-content"><strong>Question :</strong> ' . nl2br(htmlspecialchars($row['question'])) . '</div>';
                
                if ($row['reponse']) {
                    echo '<div class="answer-content"><strong>Réponse :</strong> ' . nl2br(htmlspecialchars($row['reponse'])) . '</div>';
                }
                
                echo '</div>';
            }
            
            echo '</div>';
        } else {
            echo '<div class="alert alert-info">';
            echo '<i class="fas fa-info-circle"></i> Vous n\'avez pas encore posé de question.';
            echo '</div>';
        }
        ?>
    </div>
    
    <div class="mt-4">
        <h3>Recommandations Communautaires Populaires</h3>
        
        <?php
        // Récupérer les recommandations communautaires les plus utiles
        $sql = "SELECT r.*, s.date, p.nom as pilote_nom, p.prenom as pilote_prenom, 
                m.marque as moto_marque, m.modele as moto_modele, c.nom as circuit_nom,
                (SELECT COUNT(*) FROM recommandations WHERE probleme LIKE r.probleme AND validation = 'positif') as efficacite_count
                FROM recommandations r
                JOIN sessions s ON r.session_id = s.id
                JOIN pilotes p ON s.pilote_id = p.id
                JOIN motos m ON s.moto_id = m.id
                JOIN circuits c ON s.circuit_id = c.id
                WHERE r.source = 'communautaire' AND r.validation = 'positif'
                GROUP BY r.probleme
                ORDER BY efficacite_count DESC
                LIMIT 5";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            echo '<div class="popular-recommendations">';
            
            while ($row = $result->fetch_assoc()) {
                $efficacitePercent = min(100, round(($row['efficacite_count'] / ($row['efficacite_count'] + 1)) * 100));
                
                echo '<div class="recommendation-item">';
                echo '<div class="recommendation-header">';
                echo '<span class="recommendation-efficacite">Efficacité: ' . $efficacitePercent . '%</span>';
                echo '<span class="recommendation-context">' . htmlspecialchars($row['moto_marque'] . ' ' . $row['moto_modele']) . '</span>';
                echo '</div>';
                echo '<div class="recommendation-problem"><strong>Problème :</strong> ' . htmlspecialchars($row['probleme']) . '</div>';
                echo '<div class="recommendation-solution"><strong>Solution :</strong> ' . nl2br(htmlspecialchars($row['solution'])) . '</div>';
                echo '</div>';
            }
            
            echo '</div>';
        } else {
            echo '<div class="alert alert-info">';
            echo '<i class="fas fa-info-circle"></i> Aucune recommandation communautaire populaire pour le moment.';
            echo '</div>';
        }
        ?>
    </div>
</div>

<style>
.user-questions, .popular-recommendations {
    margin-top: 1rem;
}
.question-item, .recommendation-item {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: var(--border-radius);
    padding: 1rem;
    margin-bottom: 1rem;
    border: 1px solid var(--light-gray);
}
.question-header, .recommendation-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}
.question-context {
    font-size: 0.9rem;
    color: var(--dark-gray);
    margin-bottom: 0.5rem;
}
.question-content, .answer-content, .recommendation-problem, .recommendation-solution {
    margin-bottom: 0.5rem;
}
.answer-content, .recommendation-solution {
    color: var(--primary-color);
}
.question-status {
    font-weight: bold;
}
.question-answered {
    border-left: 3px solid var(--success-color);
}
.question-pending {
    border-left: 3px solid var(--warning-color);
}
.recommendation-efficacite {
    font-weight: bold;
    color: var(--success-color);
}
</style>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
