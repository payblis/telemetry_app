<?php
/**
 * Fonctions de base de données pour l'application de télémétrie moto
 */

// Fonction pour exécuter une requête SQL et retourner tous les résultats
function query($sql, $params = []) {
    $db = connectDB();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Fonction pour exécuter une requête SQL et retourner un seul résultat
function querySingle($sql, $params = []) {
    $db = connectDB();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

// Fonction pour exécuter une requête SQL sans retourner de résultat
function execute($sql, $params = []) {
    $db = connectDB();
    $stmt = $db->prepare($sql);
    return $stmt->execute($params);
}

// Fonction pour insérer des données et retourner l'ID inséré
function insert($sql, $params = []) {
    $db = connectDB();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $db->lastInsertId();
}

// Fonction pour obtenir un utilisateur par son ID
function getUserById($id) {
    return querySingle("SELECT * FROM users WHERE id = ?", [$id]);
}

// Fonction pour obtenir un utilisateur par son email
function getUserByEmail($email) {
    return querySingle("SELECT * FROM users WHERE email = ?", [$email]);
}

// Fonction pour authentifier un utilisateur
function authenticateUser($email, $password) {
    $user = getUserByEmail($email);
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

// Fonction pour créer un utilisateur
function createUser($email, $password, $nom, $prenom, $role = 'user') {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    return insert(
        "INSERT INTO users (email, password, nom, prenom, role, date_creation) VALUES (?, ?, ?, ?, ?, NOW())",
        [$email, $hashedPassword, $nom, $prenom, $role]
    );
}

// Fonction pour mettre à jour un utilisateur
function updateUser($id, $email, $nom, $prenom, $role = null) {
    $params = [$email, $nom, $prenom, $id];
    $sql = "UPDATE users SET email = ?, nom = ?, prenom = ?";
    
    if ($role !== null) {
        $sql .= ", role = ?";
        $params = [$email, $nom, $prenom, $role, $id];
    }
    
    $sql .= " WHERE id = ?";
    return execute($sql, $params);
}

// Fonction pour obtenir tous les pilotes d'un utilisateur
function getPilotesByUserId($userId) {
    return query("SELECT * FROM pilotes WHERE user_id = ? ORDER BY nom, prenom", [$userId]);
}

// Fonction pour obtenir un pilote par son ID
function getPiloteById($id) {
    return querySingle("SELECT * FROM pilotes WHERE id = ?", [$id]);
}

// Fonction pour créer un pilote
function createPilote($userId, $nom, $prenom, $dateNaissance = null, $nationalite = null, $taille = null, $poids = null, $experience = null, $categorie = null, $niveau = 'intermediaire', $notes = null) {
    return insert(
        "INSERT INTO pilotes (user_id, nom, prenom, date_naissance, nationalite, taille, poids, experience, categorie, niveau, notes, date_creation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
        [$userId, $nom, $prenom, $dateNaissance, $nationalite, $taille, $poids, $experience, $categorie, $niveau, $notes]
    );
}

// Fonction pour mettre à jour un pilote
function updatePilote($id, $nom, $prenom, $dateNaissance = null, $nationalite = null, $taille = null, $poids = null, $experience = null, $categorie = null, $niveau = null, $notes = null) {
    return execute(
        "UPDATE pilotes SET nom = ?, prenom = ?, date_naissance = ?, nationalite = ?, taille = ?, poids = ?, experience = ?, categorie = ?, niveau = ?, notes = ?, date_modification = NOW() WHERE id = ?",
        [$nom, $prenom, $dateNaissance, $nationalite, $taille, $poids, $experience, $categorie, $niveau, $notes, $id]
    );
}

// Fonction pour supprimer un pilote
function deletePilote($id) {
    return execute("DELETE FROM pilotes WHERE id = ?", [$id]);
}

// Fonction pour obtenir toutes les motos d'un utilisateur
function getMotosByUserId($userId) {
    return query("SELECT * FROM motos WHERE user_id = ? ORDER BY marque, modele", [$userId]);
}

// Fonction pour obtenir une moto par son ID
function getMotoById($id) {
    return querySingle("SELECT * FROM motos WHERE id = ?", [$id]);
}

// Fonction pour créer une moto
function createMoto($userId, $marque, $modele, $annee = null, $cylindree = null, $puissance = null, $poids = null, $type = 'sportive', $configuration = null, $notes = null) {
    return insert(
        "INSERT INTO motos (user_id, marque, modele, annee, cylindree, puissance, poids, type, configuration, notes, date_creation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
        [$userId, $marque, $modele, $annee, $cylindree, $puissance, $poids, $type, $configuration, $notes]
    );
}

// Fonction pour mettre à jour une moto
function updateMoto($id, $marque, $modele, $annee = null, $cylindree = null, $puissance = null, $poids = null, $type = null, $configuration = null, $notes = null) {
    return execute(
        "UPDATE motos SET marque = ?, modele = ?, annee = ?, cylindree = ?, puissance = ?, poids = ?, type = ?, configuration = ?, notes = ?, date_modification = NOW() WHERE id = ?",
        [$marque, $modele, $annee, $cylindree, $puissance, $poids, $type, $configuration, $notes, $id]
    );
}

// Fonction pour supprimer une moto
function deleteMoto($id) {
    return execute("DELETE FROM motos WHERE id = ?", [$id]);
}

// Fonction pour obtenir tous les circuits
function getAllCircuits() {
    return query("SELECT * FROM circuits ORDER BY nom");
}

// Fonction pour obtenir un circuit par son ID
function getCircuitById($id) {
    return querySingle("SELECT * FROM circuits WHERE id = ?", [$id]);
}

// Fonction pour créer un circuit
function createCircuit($nom, $pays, $ville, $longueur = null, $nombreVirages = null, $latitude = null, $longitude = null, $altitude = null, $traceGps = null, $description = null) {
    return insert(
        "INSERT INTO circuits (nom, pays, ville, longueur, nombre_virages, latitude, longitude, altitude, trace_gps, description, date_creation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
        [$nom, $pays, $ville, $longueur, $nombreVirages, $latitude, $longitude, $altitude, $traceGps, $description]
    );
}

// Fonction pour mettre à jour un circuit
function updateCircuit($id, $nom, $pays, $ville, $longueur = null, $nombreVirages = null, $latitude = null, $longitude = null, $altitude = null, $traceGps = null, $description = null) {
    return execute(
        "UPDATE circuits SET nom = ?, pays = ?, ville = ?, longueur = ?, nombre_virages = ?, latitude = ?, longitude = ?, altitude = ?, trace_gps = ?, description = ?, date_modification = NOW() WHERE id = ?",
        [$nom, $pays, $ville, $longueur, $nombreVirages, $latitude, $longitude, $altitude, $traceGps, $description, $id]
    );
}

// Fonction pour supprimer un circuit
function deleteCircuit($id) {
    return execute("DELETE FROM circuits WHERE id = ?", [$id]);
}

// Fonction pour obtenir toutes les sessions d'un utilisateur
function getSessionsByUserId($userId) {
    return query("
        SELECT s.*, p.nom as pilote_nom, p.prenom as pilote_prenom, m.marque as moto_marque, m.modele as moto_modele, c.nom as circuit_nom
        FROM sessions s
        JOIN pilotes p ON s.pilote_id = p.id
        JOIN motos m ON s.moto_id = m.id
        JOIN circuits c ON s.circuit_id = c.id
        WHERE s.user_id = ?
        ORDER BY s.date_session DESC, s.heure_debut DESC
    ", [$userId]);
}

// Fonction pour obtenir une session par son ID
function getSessionById($id) {
    return querySingle("
        SELECT s.*, p.nom as pilote_nom, p.prenom as pilote_prenom, m.marque as moto_marque, m.modele as moto_modele, c.nom as circuit_nom
        FROM sessions s
        JOIN pilotes p ON s.pilote_id = p.id
        JOIN motos m ON s.moto_id = m.id
        JOIN circuits c ON s.circuit_id = c.id
        WHERE s.id = ?
    ", [$id]);
}

// Fonction pour créer une session
function createSession($userId, $piloteId, $motoId, $circuitId, $dateSession, $heureDebut, $dureeTotale = null, $conditionsMeteo = null, $temperature = null, $humidite = null, $pressionAtm = null, $ventVitesse = null, $ventDirection = null, $reglages = null, $notes = null, $problemes = null) {
    return insert(
        "INSERT INTO sessions (user_id, pilote_id, moto_id, circuit_id, date_session, heure_debut, duree_totale, conditions_meteo, temperature, humidite, pression_atm, vent_vitesse, vent_direction, reglages, notes, problemes, date_creation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
        [$userId, $piloteId, $motoId, $circuitId, $dateSession, $heureDebut, $dureeTotale, $conditionsMeteo, $temperature, $humidite, $pressionAtm, $ventVitesse, $ventDirection, $reglages, $notes, $problemes]
    );
}

// Fonction pour mettre à jour une session
function updateSession($id, $piloteId, $motoId, $circuitId, $dateSession, $heureDebut, $dureeTotale = null, $conditionsMeteo = null, $temperature = null, $humidite = null, $pressionAtm = null, $ventVitesse = null, $ventDirection = null, $reglages = null, $notes = null, $problemes = null) {
    return execute(
        "UPDATE sessions SET pilote_id = ?, moto_id = ?, circuit_id = ?, date_session = ?, heure_debut = ?, duree_totale = ?, conditions_meteo = ?, temperature = ?, humidite = ?, pression_atm = ?, vent_vitesse = ?, vent_direction = ?, reglages = ?, notes = ?, problemes = ?, date_modification = NOW() WHERE id = ?",
        [$piloteId, $motoId, $circuitId, $dateSession, $heureDebut, $dureeTotale, $conditionsMeteo, $temperature, $humidite, $pressionAtm, $ventVitesse, $ventDirection, $reglages, $notes, $problemes, $id]
    );
}

// Fonction pour supprimer une session
function deleteSession($id) {
    return execute("DELETE FROM sessions WHERE id = ?", [$id]);
}

// Fonction pour obtenir les tours d'une session
function getToursBySessionId($sessionId) {
    return query("SELECT * FROM tours WHERE session_id = ? ORDER BY numero_tour", [$sessionId]);
}

// Fonction pour obtenir les données télémétriques d'une session
function getTelemetrieBySessionId($sessionId, $limit = 1000) {
    return query("SELECT * FROM telemetrie_points WHERE session_id = ? ORDER BY timestamp LIMIT ?", [$sessionId, $limit]);
}

// Fonction pour importer des données Sensor Logger
function importSensorLoggerData($sessionId, $jsonData) {
    $data = json_decode($jsonData, true);
    if (!$data) {
        return false;
    }
    
    $db = connectDB();
    $db->beginTransaction();
    
    try {
        // Traiter les données de localisation
        if (isset($data['location']) && is_array($data['location'])) {
            $stmt = $db->prepare("
                INSERT INTO telemetrie_points (session_id, timestamp, latitude, longitude, altitude, vitesse)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($data['location'] as $point) {
                $timestamp = date('Y-m-d H:i:s', strtotime($point['time']));
                $stmt->execute([
                    $sessionId,
                    $timestamp,
                    $point['latitude'] ?? null,
                    $point['longitude'] ?? null,
                    $point['altitude'] ?? null,
                    $point['speed'] ?? null
                ]);
            }
        }
        
        // Mettre à jour les statistiques de la session
        updateSessionStats($sessionId);
        
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        if (DEBUG_MODE) {
            die('Erreur lors de l\'importation des données: ' . $e->getMessage());
        }
        return false;
    }
}

// Fonction pour mettre à jour les statistiques d'une session
function updateSessionStats($sessionId) {
    $telemetrie = getTelemetrieBySessionId($sessionId);
    
    if (empty($telemetrie)) {
        return false;
    }
    
    // Calculer les statistiques de base
    $vitesseMax = 0;
    $vitesseTotal = 0;
    $count = count($telemetrie);
    
    foreach ($telemetrie as $point) {
        if ($point['vitesse'] > $vitesseMax) {
            $vitesseMax = $point['vitesse'];
        }
        $vitesseTotal += $point['vitesse'];
    }
    
    $vitesseMoyenne = $count > 0 ? $vitesseTotal / $count : 0;
    
    // Mettre à jour la session
    return execute(
        "UPDATE sessions SET vitesse_max = ?, vitesse_moyenne = ?, date_modification = NOW() WHERE id = ?",
        [$vitesseMax, $vitesseMoyenne, $sessionId]
    );
}

// Fonction pour obtenir les recommandations d'une session
function getRecommendationsBySessionId($sessionId) {
    return query("SELECT * FROM recommandations WHERE session_id = ? ORDER BY date_creation DESC", [$sessionId]);
}

// Fonction pour créer une recommandation
function createRecommendation($sessionId, $titre, $texte, $actionRecommandee = null, $impactAttendu = null, $source = 'systeme', $confiance = null, $referenceSessionId = null) {
    return insert(
        "INSERT INTO recommandations (session_id, titre, texte, action_recommandee, impact_attendu, source, confiance, reference_session_id, date_creation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
        [$sessionId, $titre, $texte, $actionRecommandee, $impactAttendu, $source, $confiance, $referenceSessionId]
    );
}

// Fonction pour mettre à jour le statut d'une recommandation
function updateRecommendationStatus($id, $statut) {
    return execute(
        "UPDATE recommandations SET statut = ? WHERE id = ?",
        [$statut, $id]
    );
}

// Fonction pour ajouter un feedback à une recommandation
function addRecommendationFeedback($id, $feedback, $note) {
    return execute(
        "UPDATE recommandations SET feedback_utilisateur = ?, note_utilisateur = ?, date_feedback = NOW() WHERE id = ?",
        [$feedback, $note, $id]
    );
}
