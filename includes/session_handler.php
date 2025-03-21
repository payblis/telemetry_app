<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/openai_handler.php';

class SessionHandler {
    private $pdo;
    private $openai;
    
    public function __construct($pdo, $openai) {
        $this->pdo = $pdo;
        $this->openai = $openai;
    }
    
    // Créer une nouvelle session
    public function createSession($data) {
        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("
                INSERT INTO sessions 
                (circuit_id, pilote_id, moto_id, date_session, conditions_meteo, temperature, humidite)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['circuit_id'],
                $data['pilote_id'],
                $data['moto_id'],
                $data['date_session'],
                $data['conditions_meteo'],
                $data['temperature'],
                $data['humidite']
            ]);
            
            $sessionId = $this->pdo->lastInsertId();
            
            // Enregistrer les réglages initiaux
            $this->saveSetup($sessionId, $data['reglages']);
            
            $this->pdo->commit();
            
            logAccess($_SESSION['user_id'], 'create_session', ['session_id' => $sessionId]);
            
            return [
                'success' => true,
                'session_id' => $sessionId
            ];
            
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            logCustomError('Erreur lors de la création de la session', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Une erreur est survenue lors de la création de la session'
            ];
        }
    }
    
    // Sauvegarder les réglages
    private function saveSetup($sessionId, $reglages) {
        $stmt = $this->pdo->prepare("
            INSERT INTO session_reglages 
            (session_id, precharge_avant, precharge_arriere, compression_avant, 
             compression_arriere, detente_avant, detente_arriere, 
             pression_avant, pression_arriere)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $sessionId,
            $reglages['precharge_avant'],
            $reglages['precharge_arriere'],
            $reglages['compression_avant'],
            $reglages['compression_arriere'],
            $reglages['detente_avant'],
            $reglages['detente_arriere'],
            $reglages['pression_avant'],
            $reglages['pression_arriere']
        ]);
    }
    
    // Obtenir les détails d'une session
    public function getSessionDetails($sessionId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT s.*, 
                       c.nom as circuit_nom,
                       p.nom as pilote_nom,
                       m.marque as moto_marque,
                       m.modele as moto_modele
                FROM sessions s
                JOIN circuits c ON s.circuit_id = c.id
                JOIN pilotes p ON s.pilote_id = p.id
                JOIN motos m ON s.moto_id = m.id
                WHERE s.id = ?
            ");
            
            $stmt->execute([$sessionId]);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$session) {
                return [
                    'success' => false,
                    'error' => 'Session non trouvée'
                ];
            }
            
            // Récupérer les réglages
            $session['reglages'] = $this->getSessionSetup($sessionId);
            
            // Récupérer les tours
            $session['tours'] = $this->getSessionLaps($sessionId);
            
            return [
                'success' => true,
                'session' => $session
            ];
            
        } catch (PDOException $e) {
            logCustomError('Erreur lors de la récupération des détails de la session', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Une erreur est survenue'
            ];
        }
    }
    
    // Obtenir les réglages d'une session
    private function getSessionSetup($sessionId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM session_reglages
            WHERE session_id = ?
            ORDER BY created_at DESC
            LIMIT 1
        ");
        
        $stmt->execute([$sessionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Obtenir les tours d'une session
    private function getSessionLaps($sessionId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM tours
            WHERE session_id = ?
            ORDER BY numero_tour ASC
        ");
        
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Ajouter un tour à la session
    public function addLap($sessionId, $data) {
        try {
            $this->pdo->beginTransaction();
            
            // Insérer le tour
            $stmt = $this->pdo->prepare("
                INSERT INTO tours 
                (session_id, numero_tour, temps_tour)
                VALUES (?, ?, ?)
            ");
            
            $stmt->execute([
                $sessionId,
                $data['numero_tour'],
                $data['temps_tour']
            ]);
            
            $tourId = $this->pdo->lastInsertId();
            
            // Sauvegarder les données télémétriques
            if (isset($data['telemetry'])) {
                foreach ($data['telemetry'] as $telemetryData) {
                    $telemetryData['tour_id'] = $tourId;
                    saveTelemetryData($tourId, $telemetryData);
                }
            }
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'tour_id' => $tourId
            ];
            
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            logCustomError('Erreur lors de l\'ajout du tour', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Une erreur est survenue lors de l\'ajout du tour'
            ];
        }
    }
    
    // Analyser un problème et obtenir des recommandations
    public function analyzeProblem($sessionId, $problem) {
        try {
            // Récupérer le contexte complet
            $sessionDetails = $this->getSessionDetails($sessionId);
            
            if (!$sessionDetails['success']) {
                throw new Exception('Impossible de récupérer les détails de la session');
            }
            
            // Vérifier d'abord les suggestions internes
            $internalSuggestions = $this->openai->getInternalSuggestions($problem);
            
            // Si des suggestions internes pertinentes existent
            if (!empty($internalSuggestions)) {
                return [
                    'success' => true,
                    'source' => 'internal',
                    'suggestions' => $internalSuggestions
                ];
            }
            
            // Sinon, utiliser l'API OpenAI
            $context = [
                'moto' => [
                    'marque' => $sessionDetails['session']['moto_marque'],
                    'modele' => $sessionDetails['session']['moto_modele']
                ],
                'reglages' => $sessionDetails['session']['reglages'],
                'conditions' => [
                    'temperature' => $sessionDetails['session']['temperature'],
                    'humidite' => $sessionDetails['session']['humidite'],
                    'etat_piste' => $sessionDetails['session']['conditions_meteo']
                ]
            ];
            
            $response = $this->openai->getSetupRecommendations($problem, $context);
            
            if ($response['success']) {
                return [
                    'success' => true,
                    'source' => 'openai',
                    'recommendations' => $response['response']
                ];
            }
            
            throw new Exception($response['error']);
            
        } catch (Exception $e) {
            logCustomError('Erreur lors de l\'analyse du problème', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Une erreur est survenue lors de l\'analyse'
            ];
        }
    }
    
    // Enregistrer le feedback sur une recommandation
    public function saveFeedback($sessionId, $feedback) {
        try {
            // Enrichir la base de connaissances interne
            $this->openai->enrichKnowledgeBase([
                'categorie' => $feedback['categorie'],
                'probleme' => $feedback['probleme'],
                'solution' => $feedback['solution'],
                'confiance' => $feedback['succes'] ? 1.0 : 0.0
            ]);
            
            // Enregistrer le feedback dans l'historique
            $stmt = $this->pdo->prepare("
                INSERT INTO session_feedback 
                (session_id, probleme, solution, succes, commentaire)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $sessionId,
                $feedback['probleme'],
                $feedback['solution'],
                $feedback['succes'],
                $feedback['commentaire']
            ]);
            
            return [
                'success' => true
            ];
            
        } catch (Exception $e) {
            logCustomError('Erreur lors de l\'enregistrement du feedback', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Une erreur est survenue lors de l\'enregistrement du feedback'
            ];
        }
    }
}

// Créer une instance de la classe SessionHandler
$sessionHandler = new SessionHandler($pdo, $openai); 