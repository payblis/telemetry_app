<?php
/**
 * Contrôleur pour la gestion des sessions et des données télémétriques
 */
namespace App\Controllers;

use App\Models\SessionModel;
use App\Models\TelemetrieModel;
use App\Models\PiloteModel;
use App\Models\MotoModel;
use App\Models\CircuitModel;
use App\Utils\Validator;
use App\Utils\View;
use App\Utils\FileManager;

class TelemetrieController extends Controller {
    /**
     * Afficher la liste des sessions
     */
    public function index() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Récupérer les sessions de l'utilisateur
        $sessionModel = new SessionModel();
        $sessions = $sessionModel->getAllByUser($_SESSION['user_id']);
        
        // Afficher la vue
        $this->view('telemetrie/index', [
            'sessions' => $sessions,
            'title' => 'Mes Sessions'
        ]);
    }
    
    /**
     * Afficher le formulaire de création d'une session
     */
    public function create() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Récupérer les pilotes de l'utilisateur
        $piloteModel = new PiloteModel();
        $pilotes = $piloteModel->getAllByUser($_SESSION['user_id']);
        
        // Récupérer les motos de l'utilisateur
        $motoModel = new MotoModel();
        $motos = $motoModel->getAllByUser($_SESSION['user_id']);
        
        // Récupérer tous les circuits
        $circuitModel = new CircuitModel();
        $circuits = $circuitModel->getAll();
        
        // Vérifier si l'utilisateur a des pilotes et des motos
        if (empty($pilotes)) {
            View::addNotification('error', 'Vous devez d\'abord créer un pilote avant de pouvoir créer une session.');
            $this->redirect(BASE_URL . '/pilotes/create');
        }
        
        if (empty($motos)) {
            View::addNotification('error', 'Vous devez d\'abord créer une moto avant de pouvoir créer une session.');
            $this->redirect(BASE_URL . '/motos/create');
        }
        
        // Générer un jeton CSRF
        $csrfToken = $this->generateCsrfToken();
        
        // Afficher la vue
        $this->view('telemetrie/create', [
            'pilotes' => $pilotes,
            'motos' => $motos,
            'circuits' => $circuits,
            'csrf_token' => $csrfToken,
            'title' => 'Créer une Session'
        ]);
    }
    
    /**
     * Traiter la création d'une session
     */
    public function store() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Vérifier le jeton CSRF
        if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
            View::addNotification('error', 'Erreur de sécurité. Veuillez réessayer.');
            $this->redirect(BASE_URL . '/telemetrie/create');
        }
        
        // Récupérer les données du formulaire
        $data = [
            'pilote_id' => $this->post('pilote_id'),
            'moto_id' => $this->post('moto_id'),
            'circuit_id' => $this->post('circuit_id'),
            'date_session' => $this->post('date_session'),
            'heure_debut' => $this->post('heure_debut'),
            'heure_fin' => $this->post('heure_fin'),
            'conditions_meteo' => $this->post('conditions_meteo'),
            'temperature_air' => $this->post('temperature_air'),
            'temperature_piste' => $this->post('temperature_piste'),
            'humidite' => $this->post('humidite'),
            'vent' => $this->post('vent'),
            'notes' => $this->post('notes')
        ];
        
        // Règles de validation
        $rules = [
            'pilote_id' => 'required|numeric',
            'moto_id' => 'required|numeric',
            'circuit_id' => 'required|numeric',
            'date_session' => 'required|date'
        ];
        
        // Messages personnalisés
        $messages = [
            'pilote_id.required' => 'Le pilote est obligatoire.',
            'pilote_id.numeric' => 'Le pilote sélectionné est invalide.',
            'moto_id.required' => 'La moto est obligatoire.',
            'moto_id.numeric' => 'La moto sélectionnée est invalide.',
            'circuit_id.required' => 'Le circuit est obligatoire.',
            'circuit_id.numeric' => 'Le circuit sélectionné est invalide.',
            'date_session.required' => 'La date de la session est obligatoire.',
            'date_session.date' => 'La date de la session est invalide.'
        ];
        
        // Valider les données
        if (!Validator::validate($data, $rules, $messages)) {
            // Stocker les erreurs en session
            $_SESSION['form_errors'] = Validator::getErrors();
            $_SESSION['form_data'] = $data;
            
            // Rediriger vers le formulaire
            $this->redirect(BASE_URL . '/telemetrie/create');
        }
        
        // Vérifier que le pilote appartient à l'utilisateur
        $piloteModel = new PiloteModel();
        if (!$piloteModel->belongsToUser($data['pilote_id'], $_SESSION['user_id'])) {
            View::addNotification('error', 'Le pilote sélectionné n\'existe pas ou ne vous appartient pas.');
            $_SESSION['form_data'] = $data;
            $this->redirect(BASE_URL . '/telemetrie/create');
        }
        
        // Vérifier que la moto appartient à l'utilisateur
        $motoModel = new MotoModel();
        if (!$motoModel->belongsToUser($data['moto_id'], $_SESSION['user_id'])) {
            View::addNotification('error', 'La moto sélectionnée n\'existe pas ou ne vous appartient pas.');
            $_SESSION['form_data'] = $data;
            $this->redirect(BASE_URL . '/telemetrie/create');
        }
        
        // Ajouter l'ID de l'utilisateur
        $data['user_id'] = $_SESSION['user_id'];
        
        // Créer la session
        $sessionModel = new SessionModel();
        $sessionId = $sessionModel->create($data);
        
        if ($sessionId) {
            // Ajouter un message de succès
            View::addNotification('success', 'Session créée avec succès. Vous pouvez maintenant importer des données télémétriques.');
            
            // Rediriger vers la page d'importation
            $this->redirect(BASE_URL . '/telemetrie/import/' . $sessionId);
        } else {
            // Erreur lors de la création de la session
            View::addNotification('error', 'Une erreur est survenue lors de la création de la session. Veuillez réessayer.');
            $_SESSION['form_data'] = $data;
            $this->redirect(BASE_URL . '/telemetrie/create');
        }
    }
    
    /**
     * Afficher les détails d'une session
     * 
     * @param int $id ID de la session
     */
    public function view($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Récupérer la session
        $sessionModel = new SessionModel();
        $session = $sessionModel->getWithDetails($id);
        
        // Vérifier si la session existe et appartient à l'utilisateur
        if (!$session || $session['user_id'] != $_SESSION['user_id']) {
            View::addNotification('error', 'Session non trouvée ou accès non autorisé.');
            $this->redirect(BASE_URL . '/telemetrie');
        }
        
        // Récupérer les données télémétriques
        $telemetrieModel = new TelemetrieModel();
        $telemetrieData = $telemetrieModel->getSessionData($id);
        
        // Afficher la vue
        $this->view('telemetrie/view', [
            'session' => $session,
            'telemetrie' => $telemetrieData,
            'title' => 'Session du ' . date('d/m/Y', strtotime($session['date_session']))
        ]);
    }
    
    /**
     * Afficher les détails d'un tour
     * 
     * @param int $id ID du tour
     */
    public function viewTour($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Récupérer les données du tour
        $telemetrieModel = new TelemetrieModel();
        $tourData = $telemetrieModel->getTourData($id);
        
        if (!$tourData) {
            View::addNotification('error', 'Tour non trouvé.');
            $this->redirect(BASE_URL . '/telemetrie');
        }
        
        // Récupérer la session
        $sessionModel = new SessionModel();
        $session = $sessionModel->find($tourData['tour']['session_id']);
        
        // Vérifier si la session appartient à l'utilisateur
        if (!$session || $session['user_id'] != $_SESSION['user_id']) {
            View::addNotification('error', 'Accès non autorisé.');
            $this->redirect(BASE_URL . '/telemetrie');
        }
        
        // Afficher la vue
        $this->view('telemetrie/view_tour', [
            'tour' => $tourData['tour'],
            'telemetrie' => $tourData['telemetrie'],
            'agregation' => $tourData['agregation'],
            'session' => $session,
            'title' => 'Tour ' . $tourData['tour']['numero_tour'] . ' - Session du ' . date('d/m/Y', strtotime($session['date_session']))
        ]);
    }
    
    /**
     * Afficher le formulaire d'importation de données télémétriques
     * 
     * @param int $id ID de la session
     */
    public function import($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Récupérer la session
        $sessionModel = new SessionModel();
        $session = $sessionModel->getWithDetails($id);
        
        // Vérifier si la session existe et appartient à l'utilisateur
        if (!$session || $session['user_id'] != $_SESSION['user_id']) {
            View::addNotification('error', 'Session non trouvée ou accès non autorisé.');
            $this->redirect(BASE_URL . '/telemetrie');
        }
        
        // Générer un jeton CSRF
        $csrfToken = $this->generateCsrfToken();
        
        // Afficher la vue
        $this->view('telemetrie/import', [
            'session' => $session,
            'csrf_token' => $csrfToken,
            'title' => 'Importer des données télémétriques'
        ]);
    }
    
    /**
     * Traiter l'importation de données télémétriques
     * 
     * @param int $id ID de la session
     */
    public function processImport($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Vérifier le jeton CSRF
        if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
            View::addNotification('error', 'Erreur de sécurité. Veuillez réessayer.');
            $this->redirect(BASE_URL . '/telemetrie/import/' . $id);
        }
        
        // Récupérer la session
        $sessionModel = new SessionModel();
        $session = $sessionModel->find($id);
        
        // Vérifier si la session existe et appartient à l'utilisateur
        if (!$session || $session['user_id'] != $_SESSION['user_id']) {
            View::addNotification('error', 'Session non trouvée ou accès non autorisé.');
            $this->redirect(BASE_URL . '/telemetrie');
        }
        
        // Vérifier si un fichier a été téléchargé
        if (!isset($_FILES['telemetrie_file']) || $_FILES['telemetrie_file']['error'] !== UPLOAD_ERR_OK) {
            View::addNotification('error', 'Aucun fichier téléchargé ou erreur lors du téléchargement.');
            $this->redirect(BASE_URL . '/telemetrie/import/' . $id);
        }
        
        // Vérifier le type de fichier
        $fileType = $_FILES['telemetrie_file']['type'];
        if ($fileType !== 'application/json' && $fileType !== 'text/plain') {
            View::addNotification('error', 'Le fichier doit être au format JSON.');
            $this->redirect(BASE_URL . '/telemetrie/import/' . $id);
        }
        
        // Déplacer le fichier téléchargé
        $uploadDir = STORAGE_PATH . '/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filename = 'telemetrie_' . $id . '_' . date('Ymd_His') . '.json';
        $filePath = $uploadDir . '/' . $filename;
        
        if (!move_uploaded_file($_FILES['telemetrie_file']['tmp_name'], $filePath)) {
            View::addNotification('error', 'Erreur lors du déplacement du fichier téléchargé.');
            $this->redirect(BASE_URL . '/telemetrie/import/' . $id);
        }
        
        // Importer les données
        $telemetrieModel = new TelemetrieModel();
        $result = $telemetrieModel->importSensorLoggerData($filePath, $id);
        
        if ($result['success']) {
            // Mettre à jour le statut de la session
            $sessionModel->update($id, ['has_telemetry' => 1]);
            
            // Ajouter un message de succès
            View::addNotification('success', $result['message']);
            
            // Rediriger vers les détails de la session
            $this->redirect(BASE_URL . '/telemetrie/view/' . $id);
        } else {
            // Erreur lors de l'importation
            View::addNotification('error', $result['message']);
            $this->redirect(BASE_URL . '/telemetrie/import/' . $id);
        }
    }
    
    /**
     * Exporter les données télémétriques d'une session
     * 
     * @param int $id ID de la session
     */
    public function export($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Récupérer la session
        $sessionModel = new SessionModel();
        $session = $sessionModel->find($id);
        
        // Vérifier si la session existe et appartient à l'utilisateur
        if (!$session || $session['user_id'] != $_SESSION['user_id']) {
            View::addNotification('error', 'Session non trouvée ou accès non autorisé.');
            $this->redirect(BASE_URL . '/telemetrie');
        }
        
        // Exporter les données
        $telemetrieModel = new TelemetrieModel();
        $filePath = $telemetrieModel->exportSessionData($id);
        
        if ($filePath) {
            // Télécharger le fichier
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            // Erreur lors de l'exportation
            View::addNotification('error', 'Une erreur est survenue lors de l\'exportation des données.');
            $this->redirect(BASE_URL . '/telemetrie/view/' . $id);
        }
    }
    
    /**
     * Afficher le graphique des données télémétriques d'un tour
     * 
     * @param int $id ID du tour
     * @param string $type Type de données à afficher
     */
    public function graph($id, $type = 'vitesse') {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Récupérer les données du tour
        $telemetrieModel = new TelemetrieModel();
        $tourData = $telemetrieModel->getTourData($id);
        
        if (!$tourData) {
            View::addNotification('error', 'Tour non trouvé.');
            $this->redirect(BASE_URL . '/telemetrie');
        }
        
        // Récupérer la session
        $sessionModel = new SessionModel();
        $session = $sessionModel->find($tourData['tour']['session_id']);
        
        // Vérifier si la session appartient à l'utilisateur
        if (!$session || $session['user_id'] != $_SESSION['user_id']) {
            View::addNotification('error', 'Accès non autorisé.');
            $this->redirect(BASE_URL . '/telemetrie');
        }
        
        // Préparer les données pour le graphique
        $graphData = $this->prepareGraphData($tourData['telemetrie'], $type);
        
        // Afficher la vue
        $this->view('telemetrie/graph', [
            'tour' => $tourData['tour'],
            'graphData' => $graphData,
            'type' => $type,
            'session' => $session,
            'title' => 'Graphique ' . ucfirst($type) . ' - Tour ' . $tourData['tour']['numero_tour']
        ]);
    }
    
    /**
     * Préparer les données pour un graphique
     * 
     * @param array $telemetrie Données télémétriques
     * @param string $type Type de données à afficher
     * @return array Données formatées pour le graphique
     */
    private function prepareGraphData($telemetrie, $type) {
        $data = [
            'labels' => [],
            'datasets' => []
        ];
        
        $values = [];
        $timestamps = [];
        
        foreach ($telemetrie as $point) {
            $timestamp = strtotime($point['timestamp']);
            $timestamps[] = $timestamp;
            
            switch ($type) {
                case 'vitesse':
                    $values[] = floatval($point['vitesse']);
                    break;
                case 'acceleration':
                    $values[] = sqrt(
                        pow(floatval($point['acceleration_x']), 2) +
                        pow(floatval($point['acceleration_y']), 2) +
                        pow(floatval($point['acceleration_z']), 2)
                    );
                    break;
                case 'inclinaison':
                    $values[] = floatval($point['inclinaison']);
                    break;
                case 'regime_moteur':
                    $values[] = floatval($point['regime_moteur']);
                    break;
                case 'freinage':
                    $values[] = floatval($point['force_freinage']);
                    break;
                default:
                    $values[] = floatval($point['vitesse']);
            }
        }
        
        // Normaliser les timestamps
        $startTime = min($timestamps);
        foreach ($timestamps as $i => $timestamp) {
            $data['labels'][] = round(($timestamp - $startTime), 1);
        }
        
        // Ajouter le dataset
        $data['datasets'][] = [
            'label' => ucfirst($type),
            'data' => $values,
            'borderColor' => '#0066cc',
            'backgroundColor' => 'rgba(0, 102, 204, 0.1)',
            'borderWidth' => 2,
            'pointRadius' => 1
        ];
        
        return $data;
    }
    
    /**
     * Comparer deux tours
     * 
     * @param int $tour1Id ID du premier tour
     * @param int $tour2Id ID du deuxième tour
     * @param string $type Type de données à comparer
     */
    public function compare($tour1Id, $tour2Id, $type = 'vitesse') {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Récupérer les données des tours
        $telemetrieModel = new TelemetrieModel();
        $tour1Data = $telemetrieModel->getTourData($tour1Id);
        $tour2Data = $telemetrieModel->getTourData($tour2Id);
        
        if (!$tour1Data || !$tour2Data) {
            View::addNotification('error', 'Un ou plusieurs tours non trouvés.');
            $this->redirect(BASE_URL . '/telemetrie');
        }
        
        // Récupérer les sessions
        $sessionModel = new SessionModel();
        $session1 = $sessionModel->find($tour1Data['tour']['session_id']);
        $session2 = $sessionModel->find($tour2Data['tour']['session_id']);
        
        // Vérifier si les sessions appartiennent à l'utilisateur
        if (!$session1 || !$session2 || 
            $session1['user_id'] != $_SESSION['user_id'] || 
            $session2['user_id'] != $_SESSION['user_id']) {
            View::addNotification('error', 'Accès non autorisé.');
            $this->redirect(BASE_URL . '/telemetrie');
        }
        
        // Préparer les données pour le graphique
        $graphData = $this->prepareComparisonData($tour1Data['telemetrie'], $tour2Data['telemetrie'], $type);
        
        // Afficher la vue
        $this->view('telemetrie/compare', [
            'tour1' => $tour1Data['tour'],
            'tour2' => $tour2Data['tour'],
            'graphData' => $graphData,
            'type' => $type,
            'session1' => $session1,
            'session2' => $session2,
            'title' => 'Comparaison de Tours'
        ]);
    }
    
    /**
     * Préparer les données pour une comparaison
     * 
     * @param array $telemetrie1 Données télémétriques du premier tour
     * @param array $telemetrie2 Données télémétriques du deuxième tour
     * @param string $type Type de données à comparer
     * @return array Données formatées pour le graphique
     */
    private function prepareComparisonData($telemetrie1, $telemetrie2, $type) {
        $data = [
            'labels' => [],
            'datasets' => []
        ];
        
        // Préparer les données du premier tour
        $values1 = [];
        $timestamps1 = [];
        
        foreach ($telemetrie1 as $point) {
            $timestamp = strtotime($point['timestamp']);
            $timestamps1[] = $timestamp;
            
            switch ($type) {
                case 'vitesse':
                    $values1[] = floatval($point['vitesse']);
                    break;
                case 'acceleration':
                    $values1[] = sqrt(
                        pow(floatval($point['acceleration_x']), 2) +
                        pow(floatval($point['acceleration_y']), 2) +
                        pow(floatval($point['acceleration_z']), 2)
                    );
                    break;
                case 'inclinaison':
                    $values1[] = floatval($point['inclinaison']);
                    break;
                case 'regime_moteur':
                    $values1[] = floatval($point['regime_moteur']);
                    break;
                case 'freinage':
                    $values1[] = floatval($point['force_freinage']);
                    break;
                default:
                    $values1[] = floatval($point['vitesse']);
            }
        }
        
        // Préparer les données du deuxième tour
        $values2 = [];
        $timestamps2 = [];
        
        foreach ($telemetrie2 as $point) {
            $timestamp = strtotime($point['timestamp']);
            $timestamps2[] = $timestamp;
            
            switch ($type) {
                case 'vitesse':
                    $values2[] = floatval($point['vitesse']);
                    break;
                case 'acceleration':
                    $values2[] = sqrt(
                        pow(floatval($point['acceleration_x']), 2) +
                        pow(floatval($point['acceleration_y']), 2) +
                        pow(floatval($point['acceleration_z']), 2)
                    );
                    break;
                case 'inclinaison':
                    $values2[] = floatval($point['inclinaison']);
                    break;
                case 'regime_moteur':
                    $values2[] = floatval($point['regime_moteur']);
                    break;
                case 'freinage':
                    $values2[] = floatval($point['force_freinage']);
                    break;
                default:
                    $values2[] = floatval($point['vitesse']);
            }
        }
        
        // Normaliser les timestamps
        $startTime1 = min($timestamps1);
        $startTime2 = min($timestamps2);
        
        $normalizedTimestamps1 = [];
        foreach ($timestamps1 as $timestamp) {
            $normalizedTimestamps1[] = round(($timestamp - $startTime1), 1);
        }
        
        $normalizedTimestamps2 = [];
        foreach ($timestamps2 as $timestamp) {
            $normalizedTimestamps2[] = round(($timestamp - $startTime2), 1);
        }
        
        // Utiliser le plus grand ensemble de timestamps comme labels
        $data['labels'] = count($normalizedTimestamps1) > count($normalizedTimestamps2) ? 
                          $normalizedTimestamps1 : $normalizedTimestamps2;
        
        // Ajouter les datasets
        $data['datasets'][] = [
            'label' => 'Tour 1',
            'data' => $values1,
            'borderColor' => '#0066cc',
            'backgroundColor' => 'rgba(0, 102, 204, 0.1)',
            'borderWidth' => 2,
            'pointRadius' => 1
        ];
        
        $data['datasets'][] = [
            'label' => 'Tour 2',
            'data' => $values2,
            'borderColor' => '#cc0000',
            'backgroundColor' => 'rgba(204, 0, 0, 0.1)',
            'borderWidth' => 2,
            'pointRadius' => 1
        ];
        
        return $data;
    }
}
