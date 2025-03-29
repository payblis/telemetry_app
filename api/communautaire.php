<?php
// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/database.php';

/**
 * Fonction pour rechercher des recommandations similaires dans la base de connaissances
 * 
 * @param string $probleme Description du problème rencontré
 * @param array $contexte Contexte de la session (moto, circuit)
 * @return array Recommandations similaires
 */
function rechercherRecommandationsSimilaires($conn, $probleme, $contexte = []) {
    // Préparer les mots-clés pour la recherche
    $keywords = explode(' ', $probleme);
    $keywords = array_filter($keywords, function($word) {
        return strlen($word) > 3; // Ignorer les mots trop courts
    });
    
    if (empty($keywords)) {
        return [];
    }
    
    // Construire la requête SQL
    $sql = "SELECT r.*, s.date, p.nom as pilote_nom, p.prenom as pilote_prenom, 
            m.marque as moto_marque, m.modele as moto_modele, c.nom as circuit_nom
            FROM recommandations r
            JOIN sessions s ON r.session_id = s.id
            JOIN pilotes p ON s.pilote_id = p.id
            JOIN motos m ON s.moto_id = m.id
            JOIN circuits c ON s.circuit_id = c.id
            WHERE r.validation = 'positif' AND (";
    
    $conditions = [];
    $params = [];
    $types = '';
    
    // Ajouter des conditions pour chaque mot-clé
    foreach ($keywords as $keyword) {
        $conditions[] = "r.probleme LIKE ?";
        $params[] = "%$keyword%";
        $types .= 's';
    }
    
    $sql .= implode(' OR ', $conditions) . ")";
    
    // Ajouter des filtres de contexte si disponibles
    if (!empty($contexte['moto_id'])) {
        $sql .= " AND m.id = ?";
        $params[] = $contexte['moto_id'];
        $types .= 'i';
    }
    
    if (!empty($contexte['circuit_id'])) {
        $sql .= " AND c.id = ?";
        $params[] = $contexte['circuit_id'];
        $types .= 'i';
    }
    
    $sql .= " ORDER BY 
              CASE 
                WHEN r.source = 'communautaire' THEN 1 
                ELSE 2 
              END,
              r.created_at DESC
              LIMIT 5";
    
    // Exécuter la requête
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $recommendations = [];
    while ($row = $result->fetch_assoc()) {
        $recommendations[] = $row;
    }
    
    return $recommendations;
}

/**
 * Fonction pour enregistrer une recommandation communautaire
 * 
 * @param int $session_id ID de la session
 * @param string $probleme Description du problème
 * @param string $solution Solution proposée
 * @return bool Succès de l'opération
 */
function enregistrerRecommandationCommunautaire($conn, $session_id, $probleme, $solution) {
    $sql = "INSERT INTO recommandations (session_id, probleme, solution, source) 
            VALUES (?, ?, ?, 'communautaire')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $session_id, $probleme, $solution);
    
    return $stmt->execute();
}

/**
 * Fonction pour valider une recommandation
 * 
 * @param int $recommandation_id ID de la recommandation
 * @param string $validation Type de validation (positif, neutre, negatif)
 * @return bool Succès de l'opération
 */
function validerRecommandation($conn, $recommandation_id, $validation) {
    if (!in_array($validation, ['positif', 'neutre', 'negatif'])) {
        return false;
    }
    
    $sql = "UPDATE recommandations SET validation = ? WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $validation, $recommandation_id);
    
    return $stmt->execute();
}

/**
 * Fonction pour obtenir les recommandations les plus efficaces
 * 
 * @param int $limit Nombre de recommandations à retourner
 * @return array Recommandations les plus efficaces
 */
function obtenirRecommandationsEfficaces($conn, $limit = 10) {
    $sql = "SELECT r.*, s.date, p.nom as pilote_nom, p.prenom as pilote_prenom, 
            m.marque as moto_marque, m.modele as moto_modele, c.nom as circuit_nom,
            (SELECT COUNT(*) FROM recommandations WHERE probleme LIKE r.probleme AND validation = 'positif') as efficacite_count
            FROM recommandations r
            JOIN sessions s ON r.session_id = s.id
            JOIN pilotes p ON s.pilote_id = p.id
            JOIN motos m ON s.moto_id = m.id
            JOIN circuits c ON s.circuit_id = c.id
            WHERE r.validation = 'positif'
            GROUP BY r.probleme
            ORDER BY efficacite_count DESC, r.created_at DESC
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $recommendations = [];
    while ($row = $result->fetch_assoc()) {
        $recommendations[] = $row;
    }
    
    return $recommendations;
}

/**
 * Fonction pour poser une question aux experts
 * 
 * @param int $session_id ID de la session
 * @param string $question Question à poser
 * @return bool Succès de l'opération
 */
function poserQuestionExperts($conn, $session_id, $question) {
    $sql = "INSERT INTO questions_experts (session_id, question) VALUES (?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $session_id, $question);
    
    return $stmt->execute();
}

/**
 * Fonction pour répondre à une question d'expert
 * 
 * @param int $question_id ID de la question
 * @param string $reponse Réponse à la question
 * @return bool Succès de l'opération
 */
function repondreQuestionExpert($conn, $question_id, $reponse) {
    $sql = "UPDATE questions_experts SET reponse = ?, updated_at = NOW() WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $reponse, $question_id);
    
    if (!$stmt->execute()) {
        return false;
    }
    
    // Récupérer les informations de la question pour l'ajouter à la base de connaissances
    $sql = "SELECT session_id, question FROM questions_experts WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Ajouter la réponse à la base de connaissances
        return enregistrerRecommandationCommunautaire($conn, $row['session_id'], $row['question'], $reponse);
    }
    
    return true;
}
?>
