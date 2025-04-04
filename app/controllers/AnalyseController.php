<?php
/**
 * Contrôleur pour la gestion des analyses et recommandations IA
 */
class AnalyseController extends Controller {
    private $recommendationModel;
    private $sessionModel;
    private $tourModel;
    private $telemetrieModel;
    
    /**
     * Constructeur
     */
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        
        $this->recommendationModel = new RecommendationModel();
        $this->sessionModel = new SessionModel();
        $this->tourModel = new TourModel();
        $this->telemetrieModel = new TelemetrieModel();
    }
    
    /**
     * Page d'index des analyses
     */
    public function index() {
        // Récupérer les sessions de l'utilisateur
        $sessions = $this->sessionModel->getByUserId($_SESSION['user_id']);
        
        // Récupérer les recommandations récentes
        $recommendations = $this->recommendationModel->getRecentByUserId($_SESSION['user_id']);
        
        // Afficher la vue
        $this->view->render('analyses/index', [
            'title' => 'Analyses IA',
            'sessions' => $sessions,
            'recommendations' => $recommendations
        ]);
    }
    
    /**
     * Génère des recommandations pour une session
     * 
     * @param int $sessionId ID de la session
     */
    public function generate($sessionId) {
        // Vérifier que la session existe et appartient à l'utilisateur
        $session = $this->sessionModel->getById($sessionId);
        if (!$session || $session['user_id'] != $_SESSION['user_id']) {
            $this->view->setNotification('error', 'Session introuvable ou accès non autorisé');
            $this->redirect('/analyses');
            return;
        }
        
        // Vérifier que la session a des données télémétriques
        $tours = $this->tourModel->getBySessionId($sessionId);
        if (empty($tours)) {
            $this->view->setNotification('error', 'Impossible de générer des recommandations : aucune donnée de tour disponible');
            $this->redirect('/telemetrie/view/' . $sessionId);
            return;
        }
        
        // Générer les recommandations
        $result = $this->recommendationModel->generateRecommendations($sessionId);
        
        if ($result['success']) {
            $this->view->setNotification('success', $result['message']);
            
            // Essayer de générer également des recommandations communautaires
            $communityResult = $this->recommendationModel->generateCommunityRecommendations($sessionId);
            if ($communityResult['success']) {
                $this->view->setNotification('info', $communityResult['message']);
            }
        } else {
            $this->view->setNotification('error', 'Erreur lors de la génération des recommandations : ' . $result['message']);
        }
        
        // Rediriger vers la page de la session
        $this->redirect('/telemetrie/view/' . $sessionId);
    }
    
    /**
     * Affiche les détails d'une analyse pour une session
     * 
     * @param int $sessionId ID de la session
     */
    public function view($sessionId) {
        // Vérifier que la session existe et appartient à l'utilisateur
        $session = $this->sessionModel->getById($sessionId);
        if (!$session || $session['user_id'] != $_SESSION['user_id']) {
            $this->view->setNotification('error', 'Session introuvable ou accès non autorisé');
            $this->redirect('/analyses');
            return;
        }
        
        // Récupérer les recommandations pour cette session
        $recommendations = $this->recommendationModel->getBySessionId($sessionId);
        
        // Récupérer les données télémétriques agrégées
        $telemetrie = $this->telemetrieModel->getAggregatedDataBySessionId($sessionId);
        
        // Récupérer les tours
        $tours = $this->tourModel->getBySessionId($sessionId);
        
        // Afficher la vue
        $this->view->render('analyses/view', [
            'title' => 'Analyse - ' . $session['circuit_nom'],
            'session' => $session,
            'recommendations' => $recommendations,
            'telemetrie' => $telemetrie,
            'tours' => $tours
        ]);
    }
    
    /**
     * Ajoute un feedback à une recommandation
     */
    public function feedback() {
        // Vérifier que la requête est en POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/analyses');
            return;
        }
        
        // Récupérer les données du formulaire
        $recommendationId = $_POST['recommendation_id'] ?? 0;
        $feedback = $_POST['feedback'] ?? '';
        $rating = (int)($_POST['rating'] ?? 0);
        $sessionId = $_POST['session_id'] ?? 0;
        
        // Vérifier que la recommandation existe
        $recommendation = $this->recommendationModel->getById($recommendationId);
        if (!$recommendation) {
            $this->view->setNotification('error', 'Recommandation introuvable');
            $this->redirect('/analyses/view/' . $sessionId);
            return;
        }
        
        // Vérifier que la session appartient à l'utilisateur
        $session = $this->sessionModel->getById($recommendation['session_id']);
        if (!$session || $session['user_id'] != $_SESSION['user_id']) {
            $this->view->setNotification('error', 'Accès non autorisé');
            $this->redirect('/analyses');
            return;
        }
        
        // Ajouter le feedback
        $result = $this->recommendationModel->addFeedback($recommendationId, $feedback, $rating);
        
        if ($result) {
            $this->view->setNotification('success', 'Feedback enregistré avec succès');
            
            // Mettre à jour le statut si nécessaire
            if (isset($_POST['status']) && in_array($_POST['status'], ['applied', 'rejected'])) {
                $this->recommendationModel->updateStatus($recommendationId, $_POST['status']);
            }
        } else {
            $this->view->setNotification('error', 'Erreur lors de l\'enregistrement du feedback');
        }
        
        // Rediriger vers la page d'analyse
        $this->redirect('/analyses/view/' . $session['id']);
    }
    
    /**
     * Affiche la page de comparaison des performances
     */
    public function compare() {
        // Récupérer les sessions de l'utilisateur
        $sessions = $this->sessionModel->getByUserId($_SESSION['user_id']);
        
        // Récupérer les paramètres de comparaison
        $session1Id = $_GET['session1'] ?? 0;
        $session2Id = $_GET['session2'] ?? 0;
        
        $session1 = null;
        $session2 = null;
        $comparison = null;
        
        // Si deux sessions sont sélectionnées, effectuer la comparaison
        if ($session1Id && $session2Id) {
            $session1 = $this->sessionModel->getById($session1Id);
            $session2 = $this->sessionModel->getById($session2Id);
            
            // Vérifier que les sessions appartiennent à l'utilisateur
            if ($session1 && $session2 && 
                $session1['user_id'] == $_SESSION['user_id'] && 
                $session2['user_id'] == $_SESSION['user_id']) {
                
                // Effectuer la comparaison
                $comparison = $this->compareSessionsData($session1Id, $session2Id);
            }
        }
        
        // Afficher la vue
        $this->view->render('analyses/compare', [
            'title' => 'Comparaison de performances',
            'sessions' => $sessions,
            'session1' => $session1,
            'session2' => $session2,
            'comparison' => $comparison
        ]);
    }
    
    /**
     * Compare les données de deux sessions
     * 
     * @param int $session1Id ID de la première session
     * @param int $session2Id ID de la deuxième session
     * @return array Données de comparaison
     */
    private function compareSessionsData($session1Id, $session2Id) {
        // Récupérer les données des sessions
        $session1 = $this->sessionModel->getById($session1Id);
        $session2 = $this->sessionModel->getById($session2Id);
        
        // Récupérer les tours des sessions
        $tours1 = $this->tourModel->getBySessionId($session1Id);
        $tours2 = $this->tourModel->getBySessionId($session2Id);
        
        // Récupérer les données télémétriques agrégées
        $telemetrie1 = $this->telemetrieModel->getAggregatedDataBySessionId($session1Id);
        $telemetrie2 = $this->telemetrieModel->getAggregatedDataBySessionId($session2Id);
        
        // Calculer les différences
        $differences = [
            'meilleur_temps' => $session1['meilleur_temps'] - $session2['meilleur_temps'],
            'temps_moyen' => $session1['temps_moyen'] - $session2['temps_moyen'],
            'vitesse_max' => $session1['vitesse_max'] - $session2['vitesse_max'],
            'vitesse_moyenne' => $session1['vitesse_moyenne'] - $session2['vitesse_moyenne']
        ];
        
        // Préparer les données pour les graphiques
        $lapTimesData = $this->prepareLapTimesComparisonData($tours1, $tours2);
        $speedData = $this->prepareSpeedComparisonData($telemetrie1, $telemetrie2);
        
        // Analyser les forces et faiblesses
        $strengths = [];
        $weaknesses = [];
        
        if ($differences['meilleur_temps'] < 0) {
            $strengths[] = 'Meilleur temps au tour plus rapide de ' . gmdate('i:s.v', abs($differences['meilleur_temps']));
        } else {
            $weaknesses[] = 'Meilleur temps au tour plus lent de ' . gmdate('i:s.v', abs($differences['meilleur_temps']));
        }
        
        if ($differences['vitesse_max'] > 0) {
            $strengths[] = 'Vitesse maximale plus élevée de ' . abs($differences['vitesse_max']) . ' km/h';
        } else {
            $weaknesses[] = 'Vitesse maximale plus basse de ' . abs($differences['vitesse_max']) . ' km/h';
        }
        
        // Comparer les données télémétriques spécifiques
        if (isset($telemetrie1['inclinaison_droite_avg']) && isset($telemetrie2['inclinaison_droite_avg'])) {
            $inclinaisonDiff = $telemetrie1['inclinaison_droite_avg'] - $telemetrie2['inclinaison_droite_avg'];
            if (abs($inclinaisonDiff) > 2) {
                if ($inclinaisonDiff > 0) {
                    $strengths[] = 'Meilleure utilisation de l\'inclinaison dans les virages à droite (+' . round(abs($inclinaisonDiff), 1) . '°)';
                } else {
                    $weaknesses[] = 'Moins bonne utilisation de l\'inclinaison dans les virages à droite (-' . round(abs($inclinaisonDiff), 1) . '°)';
                }
            }
        }
        
        return [
            'differences' => $differences,
            'lapTimesData' => $lapTimesData,
            'speedData' => $speedData,
            'strengths' => $strengths,
            'weaknesses' => $weaknesses
        ];
    }
    
    /**
     * Prépare les données de comparaison des temps au tour
     * 
     * @param array $tours1 Tours de la première session
     * @param array $tours2 Tours de la deuxième session
     * @return array Données formatées pour le graphique
     */
    private function prepareLapTimesComparisonData($tours1, $tours2) {
        $labels = [];
        $data1 = [];
        $data2 = [];
        
        // Déterminer le nombre maximum de tours à comparer
        $maxTours = max(count($tours1), count($tours2));
        
        for ($i = 0; $i < $maxTours; $i++) {
            $labels[] = 'Tour ' . ($i + 1);
            
            // Ajouter les données du tour pour la session 1
            if (isset($tours1[$i])) {
                $data1[] = $tours1[$i]['temps'];
            } else {
                $data1[] = null;
            }
            
            // Ajouter les données du tour pour la session 2
            if (isset($tours2[$i])) {
                $data2[] = $tours2[$i]['temps'];
            } else {
                $data2[] = null;
            }
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Session 1',
                    'data' => $data1,
                    'borderColor' => '#0066cc',
                    'backgroundColor' => 'rgba(0, 102, 204, 0.1)'
                ],
                [
                    'label' => 'Session 2',
                    'data' => $data2,
                    'borderColor' => '#e30613',
                    'backgroundColor' => 'rgba(227, 6, 19, 0.1)'
                ]
            ]
        ];
    }
    
    /**
     * Prépare les données de comparaison des vitesses
     * 
     * @param array $telemetrie1 Données télémétriques de la première session
     * @param array $telemetrie2 Données télémétriques de la deuxième session
     * @return array Données formatées pour le graphique
     */
    private function prepareSpeedComparisonData($telemetrie1, $telemetrie2) {
        // Dans une implémentation réelle, cela utiliserait des données télémétriques détaillées
        // Pour l'exemple, nous utilisons des données simplifiées
        
        $labels = ['Ligne droite', 'Virage à droite', 'Virage à gauche', 'Freinage', 'Accélération'];
        
        $data1 = [
            $telemetrie1['vitesse_ligne_droite_avg'] ?? 0,
            $telemetrie1['vitesse_virage_droite_avg'] ?? 0,
            $telemetrie1['vitesse_virage_gauche_avg'] ?? 0,
            $telemetrie1['vitesse_freinage_avg'] ?? 0,
            $telemetrie1['vitesse_acceleration_avg'] ?? 0
        ];
        
        $data2 = [
            $telemetrie2['vitesse_ligne_droite_avg'] ?? 0,
            $telemetrie2['vitesse_virage_droite_avg'] ?? 0,
            $telemetrie2['vitesse_virage_gauche_avg'] ?? 0,
            $telemetrie2['vitesse_freinage_avg'] ?? 0,
            $telemetrie2['vitesse_acceleration_avg'] ?? 0
        ];
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Session 1',
                    'data' => $data1,
                    'backgroundColor' => 'rgba(0, 102, 204, 0.7)'
                ],
                [
                    'label' => 'Session 2',
                    'data' => $data2,
                    'backgroundColor' => 'rgba(227, 6, 19, 0.7)'
                ]
            ]
        ];
    }
}
