<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/database.php';

// Vérifier la connexion à la base de données
$conn = getDBConnection();

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: /telemoto/experts/index.php?error=1");
    exit;
}

$id = intval($_GET['id']);

// Récupérer les données de la question
$sql = "SELECT q.*, s.date, s.conditions, s.reglages_initiaux,
        p.nom as pilote_nom, p.prenom as pilote_prenom, p.taille as pilote_taille, p.poids as pilote_poids,
        m.marque as moto_marque, m.modele as moto_modele, m.cylindree as moto_cylindree, m.reglages_standards,
        c.nom as circuit_nom, c.pays as circuit_pays, c.details_virages
        FROM questions_experts q
        LEFT JOIN sessions s ON q.session_id = s.id
        LEFT JOIN pilotes p ON s.pilote_id = p.id
        LEFT JOIN motos m ON s.moto_id = m.id
        LEFT JOIN circuits c ON s.circuit_id = c.id
        WHERE q.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: /telemoto/experts/index.php?error=2");
    exit;
}

$question = $result->fetch_assoc();

// Traitement du formulaire
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $reponse = trim($_POST['reponse'] ?? '');
    
    // Validation des données
    if (empty($reponse)) {
        $message = "La réponse est obligatoire";
        $messageType = "danger";
    } else {
        // Mettre à jour la question avec la réponse
        $sql = "UPDATE questions_experts SET reponse = ?, updated_at = NOW() WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $reponse, $id);
        
        if ($stmt->execute()) {
            // Ajouter la réponse à la base de connaissances communautaire
            $sql = "INSERT INTO recommandations (session_id, probleme, solution, source) 
                    VALUES (?, ?, ?, 'communautaire')";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $question['session_id'], $question['question'], $reponse);
            $stmt->execute();
            
            // Rediriger vers la liste des questions avec un message de succès
            header("Location: /telemoto/experts/index.php?success=1");
            exit;
        } else {
            $message = "Erreur lors de l'enregistrement de la réponse : " . $conn->error;
            $messageType = "danger";
        }
    }
}

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <h2 class="card-title">Répondre à la Question</h2>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div class="mb-3">
        <a href="/telemoto/experts/index.php" class="btn">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
    </div>
    
    <div class="question-details mb-4">
        <h3>Contexte de la Question</h3>
        <div class="context-grid">
            <div class="context-item">
                <label>Date :</label>
                <p><?php echo date('d/m/Y', strtotime($question['date'])); ?></p>
            </div>
            <div class="context-item">
                <label>Pilote :</label>
                <p><?php echo htmlspecialchars($question['pilote_prenom'] . ' ' . $question['pilote_nom']); ?> 
                   (<?php echo $question['pilote_taille']; ?>m, <?php echo $question['pilote_poids']; ?>kg)</p>
            </div>
            <div class="context-item">
                <label>Moto :</label>
                <p><?php echo htmlspecialchars($question['moto_marque'] . ' ' . $question['moto_modele']); ?> 
                   (<?php echo $question['moto_cylindree']; ?>cc)</p>
            </div>
            <div class="context-item">
                <label>Circuit :</label>
                <p><?php echo htmlspecialchars($question['circuit_nom'] . ', ' . $question['circuit_pays']); ?></p>
            </div>
            <div class="context-item">
                <label>Conditions :</label>
                <p><?php echo !empty($question['conditions']) ? htmlspecialchars($question['conditions']) : 'Non spécifiées'; ?></p>
            </div>
        </div>
        
        <?php if (!empty($question['reglages_initiaux'])): ?>
        <div class="reglages-details mt-3">
            <h4>Réglages Initiaux</h4>
            <div class="reglages-content">
                <pre><?php echo htmlspecialchars($question['reglages_initiaux']); ?></pre>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($question['reglages_standards'])): ?>
        <div class="reglages-details mt-3">
            <h4>Réglages Standards de la Moto</h4>
            <div class="reglages-content">
                <pre><?php echo htmlspecialchars($question['reglages_standards']); ?></pre>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="question-content mb-4">
        <h3>Question</h3>
        <div class="question-box">
            <?php echo nl2br(htmlspecialchars($question['question'])); ?>
        </div>
    </div>
    
    <form method="POST" action="" class="needs-validation">
        <div class="form-group">
            <label for="reponse">Votre Réponse d'Expert *</label>
            <textarea id="reponse" name="reponse" rows="6" required><?php echo htmlspecialchars($_POST['reponse'] ?? ''); ?></textarea>
            <small class="form-text">Fournissez une réponse technique précise et détaillée. Votre expertise sera partagée avec la communauté.</small>
        </div>
        
        <div class="form-actions">
            <a href="/telemoto/experts/index.php" class="btn">Annuler</a>
            <button type="submit" class="btn btn-primary">Soumettre la Réponse</button>
        </div>
    </form>
</div>

<style>
.context-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}
.context-item label {
    font-weight: bold;
    color: var(--primary-color);
    margin-bottom: 0.2rem;
}
.reglages-content {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: var(--border-radius);
    padding: 1rem;
    border: 1px solid var(--light-gray);
    margin-top: 0.5rem;
}
.reglages-content pre {
    white-space: pre-wrap;
    font-family: inherit;
    margin: 0;
}
.question-box {
    background-color: rgba(0, 168, 255, 0.1);
    border: 1px solid var(--primary-color);
    border-radius: var(--border-radius);
    padding: 1.5rem;
}
</style>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
