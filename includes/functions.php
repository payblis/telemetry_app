<?php
/**
 * Fonctions utilitaires pour l'application de télémétrie moto
 */

// Fonction pour télécharger un fichier
function uploadFile($file, $targetDir, $allowedTypes = [], $maxSize = 5242880) {
    // Vérifier si le fichier existe
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return [
            'success' => false,
            'error' => 'Aucun fichier n\'a été téléchargé'
        ];
    }
    
    // Vérifier les erreurs de téléchargement
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille maximale autorisée par PHP',
            UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille maximale autorisée par le formulaire',
            UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement téléchargé',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été téléchargé',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
            UPLOAD_ERR_CANT_WRITE => 'Échec de l\'écriture du fichier sur le disque',
            UPLOAD_ERR_EXTENSION => 'Une extension PHP a arrêté le téléchargement du fichier'
        ];
        
        return [
            'success' => false,
            'error' => $errorMessages[$file['error']] ?? 'Erreur inconnue lors du téléchargement'
        ];
    }
    
    // Vérifier la taille du fichier
    if ($file['size'] > $maxSize) {
        return [
            'success' => false,
            'error' => 'Le fichier est trop volumineux (maximum ' . formatBytes($maxSize) . ')'
        ];
    }
    
    // Vérifier le type de fichier
    if (!empty($allowedTypes)) {
        $fileType = mime_content_type($file['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            return [
                'success' => false,
                'error' => 'Type de fichier non autorisé. Types autorisés: ' . implode(', ', $allowedTypes)
            ];
        }
    }
    
    // Créer le répertoire cible s'il n'existe pas
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    // Générer un nom de fichier unique
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $uniqueName = uniqid() . '_' . time() . '.' . $fileExtension;
    $targetPath = $targetDir . '/' . $uniqueName;
    
    // Déplacer le fichier
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [
            'success' => true,
            'path' => $targetPath,
            'name' => $uniqueName,
            'original_name' => $file['name'],
            'type' => $fileType ?? mime_content_type($targetPath),
            'size' => $file['size']
        ];
    } else {
        return [
            'success' => false,
            'error' => 'Échec du déplacement du fichier téléchargé'
        ];
    }
}

// Fonction pour formater la taille en octets
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Fonction pour valider un email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Fonction pour valider une date
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Fonction pour valider un nombre
function validateNumber($number, $min = null, $max = null) {
    if (!is_numeric($number)) {
        return false;
    }
    
    if ($min !== null && $number < $min) {
        return false;
    }
    
    if ($max !== null && $number > $max) {
        return false;
    }
    
    return true;
}

// Fonction pour valider une chaîne
function validateString($string, $minLength = null, $maxLength = null) {
    $length = strlen($string);
    
    if ($minLength !== null && $length < $minLength) {
        return false;
    }
    
    if ($maxLength !== null && $length > $maxLength) {
        return false;
    }
    
    return true;
}

// Fonction pour générer un token aléatoire
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// Fonction pour formater une date
function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

// Fonction pour formater une heure
function formatTime($time, $format = 'H:i') {
    return date($format, strtotime($time));
}

// Fonction pour formater un temps en secondes
function formatSeconds($seconds) {
    if ($seconds < 60) {
        return number_format($seconds, 2) . ' s';
    } elseif ($seconds < 3600) {
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        return $minutes . ' min ' . number_format($remainingSeconds, 0) . ' s';
    } else {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;
        return $hours . ' h ' . $minutes . ' min ' . number_format($remainingSeconds, 0) . ' s';
    }
}

// Fonction pour formater un temps au tour
function formatLapTime($seconds) {
    if (!$seconds) {
        return '--:--';
    }
    
    $minutes = floor($seconds / 60);
    $remainingSeconds = $seconds % 60;
    return $minutes . ':' . str_pad(number_format($remainingSeconds, 3), 6, '0', STR_PAD_LEFT);
}

// Fonction pour analyser un fichier JSON Sensor Logger
function parseSensorLoggerJson($filePath) {
    if (!file_exists($filePath)) {
        return [
            'success' => false,
            'error' => 'Le fichier n\'existe pas'
        ];
    }
    
    $content = file_get_contents($filePath);
    $data = json_decode($content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'error' => 'Erreur de parsing JSON: ' . json_last_error_msg()
        ];
    }
    
    // Vérifier la structure minimale requise
    if (!isset($data['session']) || !isset($data['location']) || empty($data['location'])) {
        return [
            'success' => false,
            'error' => 'Format de fichier Sensor Logger invalide ou incomplet'
        ];
    }
    
    return [
        'success' => true,
        'data' => $data
    ];
}

// Fonction pour détecter les tours dans les données télémétriques
function detectLaps($sessionId, $circuitId) {
    // Cette fonction est simplifiée pour la version sans routage
    // Dans une implémentation réelle, elle utiliserait des algorithmes plus complexes
    
    $telemetrie = getTelemetrieBySessionId($sessionId);
    $circuit = getCircuitById($circuitId);
    
    if (empty($telemetrie) || !$circuit) {
        return false;
    }
    
    // Simulation simple de détection de tours
    // Dans une version réelle, on utiliserait les coordonnées GPS pour détecter le franchissement de la ligne de départ/arrivée
    
    $db = connectDB();
    $db->beginTransaction();
    
    try {
        // Supprimer les tours existants
        execute("DELETE FROM tours WHERE session_id = ?", [$sessionId]);
        
        // Créer un tour de démonstration
        $startTime = $telemetrie[0]['timestamp'];
        $endTime = $telemetrie[count($telemetrie) - 1]['timestamp'];
        
        $startTimestamp = strtotime($startTime);
        $endTimestamp = strtotime($endTime);
        $lapTime = $endTimestamp - $startTimestamp;
        
        // Calculer la vitesse maximale et moyenne
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
        
        // Insérer le tour
        $tourId = insert(
            "INSERT INTO tours (session_id, numero_tour, temps, heure_debut, heure_fin, vitesse_max, vitesse_moyenne, valide, meilleur_tour) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$sessionId, 1, $lapTime, $startTime, $endTime, $vitesseMax, $vitesseMoyenne, 1, 1]
        );
        
        // Mettre à jour la session
        execute(
            "UPDATE sessions SET nombre_tours = 1, meilleur_temps = ?, temps_moyen = ?, vitesse_max = ?, vitesse_moyenne = ? WHERE id = ?",
            [$lapTime, $lapTime, $vitesseMax, $vitesseMoyenne, $sessionId]
        );
        
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        if (DEBUG_MODE) {
            die('Erreur lors de la détection des tours: ' . $e->getMessage());
        }
        return false;
    }
}

// Fonction pour générer des recommandations basées sur les données télémétriques
function generateRecommendations($sessionId) {
    $session = getSessionById($sessionId);
    $tours = getToursBySessionId($sessionId);
    
    if (!$session || empty($tours)) {
        return false;
    }
    
    // Générer des recommandations simples basées sur les données
    $recommendations = [];
    
    // Recommandation sur la vitesse
    if ($session['vitesse_max'] < 100) {
        $recommendations[] = [
            'titre' => 'Amélioration de la vitesse maximale',
            'texte' => 'Votre vitesse maximale est relativement basse. Essayez d\'exploiter davantage la puissance de votre moto dans les lignes droites.',
            'action' => 'Travailler sur la position du corps pour réduire la traînée aérodynamique et utiliser plus efficacement l\'accélérateur.',
            'impact' => 'Augmentation potentielle de la vitesse maximale de 10-15%.'
        ];
    }
    
    // Recommandation sur le temps au tour
    if (count($tours) > 0) {
        $recommendations[] = [
            'titre' => 'Constance dans les temps au tour',
            'texte' => 'Travailler sur la régularité de vos temps au tour pour améliorer votre performance globale.',
            'action' => 'Concentrez-vous sur la répétition des mêmes trajectoires et points de freinage à chaque tour.',
            'impact' => 'Réduction de l\'écart entre vos meilleurs et moins bons tours.'
        ];
    }
    
    // Ajouter les recommandations à la base de données
    $db = connectDB();
    $db->beginTransaction();
    
    try {
        foreach ($recommendations as $rec) {
            createRecommendation(
                $sessionId,
                $rec['titre'],
                $rec['texte'],
                $rec['action'],
                $rec['impact'],
                'systeme',
                70
            );
        }
        
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        if (DEBUG_MODE) {
            die('Erreur lors de la génération des recommandations: ' . $e->getMessage());
        }
        return false;
    }
}
