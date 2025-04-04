<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/database.php';

// Vérifier la connexion à la base de données
$conn = getDBConnection();

// Inclure l'en-tête
include_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <h2 class="card-title">IA Communautaire - Base de Connaissances Experts</h2>
    
    <div class="mb-3">
        <p>Cette section permet aux experts de contribuer à la base de connaissances communautaire en répondant à des questions techniques sur les réglages moto.</p>
    </div>
    
    <?php
    // Vérifier si l'utilisateur est connecté et a le rôle d'expert
    $isExpert = true; // À remplacer par une vérification réelle du rôle utilisateur
    
    if ($isExpert) {
        // Récupérer les questions sans réponse
        $sql = "SELECT q.*, s.date, p.nom as pilote_nom, p.prenom as pilote_prenom, 
                m.marque as moto_marque, m.modele as moto_modele, c.nom as circuit_nom
                FROM questions_experts q
                LEFT JOIN sessions s ON q.session_id = s.id
                LEFT JOIN pilotes p ON s.pilote_id = p.id
                LEFT JOIN motos m ON s.moto_id = m.id
                LEFT JOIN circuits c ON s.circuit_id = c.id
                WHERE q.reponse IS NULL
                ORDER BY q.created_at DESC
                LIMIT 10";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            echo '<div class="expert-questions">';
            echo '<h3>Questions en attente de réponse</h3>';
            
            while ($row = $result->fetch_assoc()) {
                echo '<div class="question-item">';
                echo '<div class="question-header">';
                echo '<span class="question-date">' . date('d/m/Y H:i', strtotime($row['created_at'])) . '</span>';
                echo '<span class="question-context">' . htmlspecialchars($row['pilote_prenom'] . ' ' . $row['pilote_nom']) . ' - ';
                echo htmlspecialchars($row['moto_marque'] . ' ' . $row['moto_modele']) . ' - ';
                echo htmlspecialchars($row['circuit_nom']) . '</span>';
                echo '</div>';
                echo '<div class="question-content">' . nl2br(htmlspecialchars($row['question'])) . '</div>';
                echo '<div class="question-actions">';
                echo '<a href="/telemoto/experts/repondre.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm">Répondre</a>';
                echo '</div>';
                echo '</div>';
            }
            
            echo '</div>';
        } else {
            echo '<div class="alert alert-info">';
            echo '<i class="fas fa-info-circle"></i> Aucune question en attente de réponse.';
            echo '</div>';
        }
        
        // Récupérer les questions récemment répondues
        $sql = "SELECT q.*, s.date, p.nom as pilote_nom, p.prenom as pilote_prenom, 
                m.marque as moto_marque, m.modele as moto_modele, c.nom as circuit_nom
                FROM questions_experts q
                LEFT JOIN sessions s ON q.session_id = s.id
                LEFT JOIN pilotes p ON s.pilote_id = p.id
                LEFT JOIN motos m ON s.moto_id = m.id
                LEFT JOIN circuits c ON s.circuit_id = c.id
                WHERE q.reponse IS NOT NULL
                ORDER BY q.updated_at DESC
                LIMIT 5";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            echo '<div class="expert-answers mt-4">';
            echo '<h3>Réponses récentes</h3>';
            
            while ($row = $result->fetch_assoc()) {
                echo '<div class="answer-item">';
                echo '<div class="answer-header">';
                echo '<span class="answer-date">' . date('d/m/Y H:i', strtotime($row['updated_at'])) . '</span>';
                echo '<span class="answer-context">' . htmlspecialchars($row['pilote_prenom'] . ' ' . $row['pilote_nom']) . ' - ';
                echo htmlspecialchars($row['moto_marque'] . ' ' . $row['moto_modele']) . ' - ';
                echo htmlspecialchars($row['circuit_nom']) . '</span>';
                echo '</div>';
                echo '<div class="question-content"><strong>Question :</strong> ' . nl2br(htmlspecialchars($row['question'])) . '</div>';
                echo '<div class="answer-content"><strong>Réponse :</strong> ' . nl2br(htmlspecialchars($row['reponse'])) . '</div>';
                echo '</div>';
            }
            
            echo '</div>';
        }
    } else {
        echo '<div class="alert alert-warning">';
        echo '<i class="fas fa-exclamation-triangle"></i> Seuls les utilisateurs avec le rôle d\'expert peuvent accéder à cette section.';
        echo '</div>';
    }
    ?>
    
    <div class="mt-4">
        <h3>Base de Connaissances Communautaire</h3>
        
        <?php
        // Récupérer les recommandations communautaires validées
        $sql = "SELECT r.*, s.date, p.nom as pilote_nom, p.prenom as pilote_prenom, 
                m.marque as moto_marque, m.modele as moto_modele, c.nom as circuit_nom
                FROM recommandations r
                JOIN sessions s ON r.session_id = s.id
                JOIN pilotes p ON s.pilote_id = p.id
                JOIN motos m ON s.moto_id = m.id
                JOIN circuits c ON s.circuit_id = c.id
                WHERE r.source = 'communautaire' AND r.validation = 'positif'
                ORDER BY r.created_at DESC
                LIMIT 10";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            echo '<div class="community-knowledge">';
            
            while ($row = $result->fetch_assoc()) {
                echo '<div class="knowledge-item">';
                echo '<div class="knowledge-header">';
                echo '<span class="knowledge-date">' . date('d/m/Y', strtotime($row['created_at'])) . '</span>';
                echo '<span class="knowledge-context">' . htmlspecialchars($row['pilote_prenom'] . ' ' . $row['pilote_nom']) . ' - ';
                echo htmlspecialchars($row['moto_marque'] . ' ' . $row['moto_modele']) . ' - ';
                echo htmlspecialchars($row['circuit_nom']) . '</span>';
                echo '</div>';
                echo '<div class="knowledge-problem"><strong>Problème :</strong> ' . htmlspecialchars($row['probleme']) . '</div>';
                echo '<div class="knowledge-solution"><strong>Solution :</strong> ' . nl2br(htmlspecialchars($row['solution'])) . '</div>';
                echo '</div>';
            }
            
            echo '</div>';
        } else {
            echo '<div class="alert alert-info">';
            echo '<i class="fas fa-info-circle"></i> Aucune recommandation communautaire validée pour le moment.';
            echo '</div>';
        }
        ?>
    </div>
</div>

<style>
.expert-questions, .expert-answers, .community-knowledge {
    margin-top: 1rem;
}
.question-item, .answer-item, .knowledge-item {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: var(--border-radius);
    padding: 1rem;
    margin-bottom: 1rem;
    border: 1px solid var(--light-gray);
}
.question-header, .answer-header, .knowledge-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    color: var(--dark-gray);
}
.question-content, .answer-content, .knowledge-problem, .knowledge-solution {
    margin-bottom: 0.5rem;
}
.question-actions {
    margin-top: 1rem;
    text-align: right;
}
.answer-content, .knowledge-solution {
    color: var(--primary-color);
}
</style>

<?php
// Inclure le pied de page
include_once __DIR__ . '/../includes/footer.php';
?>
