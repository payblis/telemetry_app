<?php
/**
 * Contrôleur pour la gestion des circuits
 */
namespace App\Controllers;

use App\Models\CircuitModel;
use App\Utils\Validator;
use App\Utils\View;
use App\Utils\FileManager;

class CircuitController extends Controller {
    /**
     * Afficher la liste des circuits
     */
    public function index() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Récupérer tous les circuits
        $circuitModel = new CircuitModel();
        $circuits = $circuitModel->getAll();
        
        // Récupérer les circuits créés par l'utilisateur
        $userCircuits = $circuitModel->getByUser($_SESSION['user_id']);
        
        // Afficher la vue
        $this->view('circuits/index', [
            'circuits' => $circuits,
            'userCircuits' => $userCircuits,
            'title' => 'Circuits'
        ]);
    }
    
    /**
     * Afficher les détails d'un circuit
     * 
     * @param int $id ID du circuit
     */
    public function view($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Récupérer le circuit
        $circuitModel = new CircuitModel();
        $circuit = $circuitModel->getWithDetails($id);
        
        // Vérifier si le circuit existe
        if (!$circuit) {
            View::addNotification('error', 'Circuit non trouvé.');
            $this->redirect(BASE_URL . '/circuits');
        }
        
        // Récupérer les statistiques du circuit
        $stats = $circuitModel->getStats($id);
        
        // Récupérer les meilleurs temps par secteur
        $bestSectorTimes = $circuitModel->getBestSectorTimes($id);
        
        // Récupérer les records par pilote
        $recordsByPilote = $circuitModel->getRecordsByPilote($id);
        
        // Vérifier si l'utilisateur est le créateur du circuit
        $isCreator = $circuit['created_by'] == $_SESSION['user_id'];
        
        // Afficher la vue
        $this->view('circuits/view', [
            'circuit' => $circuit,
            'stats' => $stats,
            'bestSectorTimes' => $bestSectorTimes,
            'recordsByPilote' => $recordsByPilote,
            'isCreator' => $isCreator,
            'title' => $circuit['nom']
        ]);
    }
    
    /**
     * Afficher le formulaire de création d'un circuit
     */
    public function create() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Générer un jeton CSRF
        $csrfToken = $this->generateCsrfToken();
        
        // Afficher la vue
        $this->view('circuits/create', [
            'csrf_token' => $csrfToken,
            'title' => 'Ajouter un Circuit'
        ]);
    }
    
    /**
     * Traiter la création d'un circuit
     */
    public function store() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Vérifier le jeton CSRF
        if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
            View::addNotification('error', 'Erreur de sécurité. Veuillez réessayer.');
            $this->redirect(BASE_URL . '/circuits/create');
        }
        
        // Récupérer les données du formulaire
        $data = [
            'nom' => $this->post('nom'),
            'pays' => $this->post('pays'),
            'ville' => $this->post('ville'),
            'longueur' => $this->post('longueur'),
            'largeur' => $this->post('largeur'),
            'nombre_virages' => $this->post('nombre_virages'),
            'altitude' => $this->post('altitude'),
            'coordonnees_gps' => $this->post('coordonnees_gps'),
            'description' => $this->post('description'),
            'source' => $this->post('source')
        ];
        
        // Règles de validation
        $rules = [
            'nom' => 'required|max:100',
            'pays' => 'required|max:50',
            'ville' => 'required|max:50',
            'longueur' => 'numeric|min:0'
        ];
        
        // Messages personnalisés
        $messages = [
            'nom.required' => 'Le nom du circuit est obligatoire.',
            'nom.max' => 'Le nom du circuit ne doit pas dépasser 100 caractères.',
            'pays.required' => 'Le pays est obligatoire.',
            'pays.max' => 'Le pays ne doit pas dépasser 50 caractères.',
            'ville.required' => 'La ville est obligatoire.',
            'ville.max' => 'La ville ne doit pas dépasser 50 caractères.',
            'longueur.numeric' => 'La longueur doit être un nombre.',
            'longueur.min' => 'La longueur doit être positive.'
        ];
        
        // Valider les données
        if (!Validator::validate($data, $rules, $messages)) {
            // Stocker les erreurs en session
            $_SESSION['form_errors'] = Validator::getErrors();
            $_SESSION['form_data'] = $data;
            
            // Rediriger vers le formulaire
            $this->redirect(BASE_URL . '/circuits/create');
        }
        
        // Traiter l'image si elle existe
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileManager = new FileManager();
            $imagePath = $fileManager->uploadImage($_FILES['image'], 'circuits');
            
            if ($imagePath) {
                $data['image_path'] = $imagePath;
            } else {
                View::addNotification('error', 'Une erreur est survenue lors du téléchargement de l\'image. Veuillez réessayer.');
                $_SESSION['form_data'] = $data;
                $this->redirect(BASE_URL . '/circuits/create');
            }
        }
        
        // Ajouter l'ID de l'utilisateur créateur
        $data['created_by'] = $_SESSION['user_id'];
        
        // Créer le circuit
        $circuitModel = new CircuitModel();
        $circuitId = $circuitModel->create($data);
        
        if ($circuitId) {
            // Ajouter un message de succès
            View::addNotification('success', 'Circuit ajouté avec succès.');
            
            // Rediriger vers les détails du circuit
            $this->redirect(BASE_URL . '/circuits/view/' . $circuitId);
        } else {
            // Erreur lors de la création du circuit
            View::addNotification('error', 'Une erreur est survenue lors de l\'ajout du circuit. Veuillez réessayer.');
            $_SESSION['form_data'] = $data;
            $this->redirect(BASE_URL . '/circuits/create');
        }
    }
    
    /**
     * Afficher le formulaire de modification d'un circuit
     * 
     * @param int $id ID du circuit
     */
    public function edit($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Récupérer le circuit
        $circuitModel = new CircuitModel();
        $circuit = $circuitModel->find($id);
        
        // Vérifier si le circuit existe et si l'utilisateur est le créateur
        if (!$circuit || !$circuitModel->belongsToUser($id, $_SESSION['user_id'])) {
            View::addNotification('error', 'Circuit non trouvé ou accès non autorisé.');
            $this->redirect(BASE_URL . '/circuits');
        }
        
        // Générer un jeton CSRF
        $csrfToken = $this->generateCsrfToken();
        
        // Afficher la vue
        $this->view('circuits/edit', [
            'circuit' => $circuit,
            'csrf_token' => $csrfToken,
            'title' => 'Modifier le Circuit'
        ]);
    }
    
    /**
     * Traiter la modification d'un circuit
     * 
     * @param int $id ID du circuit
     */
    public function update($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Vérifier le jeton CSRF
        if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
            View::addNotification('error', 'Erreur de sécurité. Veuillez réessayer.');
            $this->redirect(BASE_URL . '/circuits/edit/' . $id);
        }
        
        // Récupérer le circuit
        $circuitModel = new CircuitModel();
        $circuit = $circuitModel->find($id);
        
        // Vérifier si le circuit existe et si l'utilisateur est le créateur
        if (!$circuit || !$circuitModel->belongsToUser($id, $_SESSION['user_id'])) {
            View::addNotification('error', 'Circuit non trouvé ou accès non autorisé.');
            $this->redirect(BASE_URL . '/circuits');
        }
        
        // Récupérer les données du formulaire
        $data = [
            'nom' => $this->post('nom'),
            'pays' => $this->post('pays'),
            'ville' => $this->post('ville'),
            'longueur' => $this->post('longueur'),
            'largeur' => $this->post('largeur'),
            'nombre_virages' => $this->post('nombre_virages'),
            'altitude' => $this->post('altitude'),
            'coordonnees_gps' => $this->post('coordonnees_gps'),
            'description' => $this->post('description'),
            'source' => $this->post('source')
        ];
        
        // Règles de validation
        $rules = [
            'nom' => 'required|max:100',
            'pays' => 'required|max:50',
            'ville' => 'required|max:50',
            'longueur' => 'numeric|min:0'
        ];
        
        // Messages personnalisés
        $messages = [
            'nom.required' => 'Le nom du circuit est obligatoire.',
            'nom.max' => 'Le nom du circuit ne doit pas dépasser 100 caractères.',
            'pays.required' => 'Le pays est obligatoire.',
            'pays.max' => 'Le pays ne doit pas dépasser 50 caractères.',
            'ville.required' => 'La ville est obligatoire.',
            'ville.max' => 'La ville ne doit pas dépasser 50 caractères.',
            'longueur.numeric' => 'La longueur doit être un nombre.',
            'longueur.min' => 'La longueur doit être positive.'
        ];
        
        // Valider les données
        if (!Validator::validate($data, $rules, $messages)) {
            // Stocker les erreurs en session
            $_SESSION['form_errors'] = Validator::getErrors();
            $_SESSION['form_data'] = $data;
            
            // Rediriger vers le formulaire
            $this->redirect(BASE_URL . '/circuits/edit/' . $id);
        }
        
        // Traiter l'image si elle existe
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileManager = new FileManager();
            $imagePath = $fileManager->uploadImage($_FILES['image'], 'circuits');
            
            if ($imagePath) {
                $data['image_path'] = $imagePath;
                
                // Supprimer l'ancienne image si elle existe
                if (!empty($circuit['image_path'])) {
                    $fileManager->deleteFile($circuit['image_path']);
                }
            } else {
                View::addNotification('error', 'Une erreur est survenue lors du téléchargement de l\'image. Veuillez réessayer.');
                $_SESSION['form_data'] = $data;
                $this->redirect(BASE_URL . '/circuits/edit/' . $id);
            }
        }
        
        // Mettre à jour le circuit
        if ($circuitModel->update($id, $data)) {
            // Ajouter un message de succès
            View::addNotification('success', 'Circuit mis à jour avec succès.');
            
            // Rediriger vers les détails du circuit
            $this->redirect(BASE_URL . '/circuits/view/' . $id);
        } else {
            // Erreur lors de la mise à jour du circuit
            View::addNotification('error', 'Une erreur est survenue lors de la mise à jour du circuit. Veuillez réessayer.');
            $_SESSION['form_data'] = $data;
            $this->redirect(BASE_URL . '/circuits/edit/' . $id);
        }
    }
    
    /**
     * Supprimer un circuit
     * 
     * @param int $id ID du circuit
     */
    public function delete($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Vérifier le jeton CSRF
        if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
            View::addNotification('error', 'Erreur de sécurité. Veuillez réessayer.');
            $this->redirect(BASE_URL . '/circuits');
        }
        
        // Récupérer le circuit
        $circuitModel = new CircuitModel();
        $circuit = $circuitModel->find($id);
        
        // Vérifier si le circuit existe et si l'utilisateur est le créateur
        if (!$circuit || !$circuitModel->belongsToUser($id, $_SESSION['user_id'])) {
            View::addNotification('error', 'Circuit non trouvé ou accès non autorisé.');
            $this->redirect(BASE_URL . '/circuits');
        }
        
        // Vérifier si le circuit a des sessions
        if ($circuitModel->countSessions($id) > 0) {
            View::addNotification('error', 'Impossible de supprimer ce circuit car il est associé à des sessions.');
            $this->redirect(BASE_URL . '/circuits/view/' . $id);
        }
        
        // Supprimer l'image si elle existe
        if (!empty($circuit['image_path'])) {
            $fileManager = new FileManager();
            $fileManager->deleteFile($circuit['image_path']);
        }
        
        // Supprimer le circuit
        if ($circuitModel->delete($id)) {
            // Ajouter un message de succès
            View::addNotification('success', 'Circuit supprimé avec succès.');
            
            // Rediriger vers la liste des circuits
            $this->redirect(BASE_URL . '/circuits');
        } else {
            // Erreur lors de la suppression du circuit
            View::addNotification('error', 'Une erreur est survenue lors de la suppression du circuit. Veuillez réessayer.');
            $this->redirect(BASE_URL . '/circuits/view/' . $id);
        }
    }
    
    /**
     * Afficher le formulaire d'ajout de virage
     * 
     * @param int $id ID du circuit
     */
    public function addVirage($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Récupérer le circuit
        $circuitModel = new CircuitModel();
        $circuit = $circuitModel->find($id);
        
        // Vérifier si le circuit existe et si l'utilisateur est le créateur
        if (!$circuit || !$circuitModel->belongsToUser($id, $_SESSION['user_id'])) {
            View::addNotification('error', 'Circuit non trouvé ou accès non autorisé.');
            $this->redirect(BASE_URL . '/circuits');
        }
        
        // Générer un jeton CSRF
        $csrfToken = $this->generateCsrfToken();
        
        // Afficher la vue
        $this->view('circuits/add_virage', [
            'circuit' => $circuit,
            'csrf_token' => $csrfToken,
            'title' => 'Ajouter un Virage'
        ]);
    }
    
    /**
     * Traiter l'ajout d'un virage
     * 
     * @param int $id ID du circuit
     */
    public function storeVirage($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Vérifier le jeton CSRF
        if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
            View::addNotification('error', 'Erreur de sécurité. Veuillez réessayer.');
            $this->redirect(BASE_URL . '/circuits/add-virage/' . $id);
        }
        
        // Récupérer le circuit
        $circuitModel = new CircuitModel();
        $circuit = $circuitModel->find($id);
        
        // Vérifier si le circuit existe et si l'utilisateur est le créateur
        if (!$circuit || !$circuitModel->belongsToUser($id, $_SESSION['user_id'])) {
            View::addNotification('error', 'Circuit non trouvé ou accès non autorisé.');
            $this->redirect(BASE_URL . '/circuits');
        }
        
        // Récupérer les données du formulaire
        $data = [
            'numero_virage' => $this->post('numero_virage'),
            'nom' => $this->post('nom'),
            'type' => $this->post('type'),
            'angle' => $this->post('angle'),
            'rayon' => $this->post('rayon'),
            'longueur' => $this->post('longueur'),
            'description' => $this->post('description')
        ];
        
        // Règles de validation
        $rules = [
            'numero_virage' => 'required|numeric|min:1',
            'type' => 'required|in:gauche,droite,chicane,epingle'
        ];
        
        // Messages personnalisés
        $messages = [
            'numero_virage.required' => 'Le numéro du virage est obligatoire.',
            'numero_virage.numeric' => 'Le numéro du virage doit être un nombre.',
            'numero_virage.min' => 'Le numéro du virage doit être supérieur à 0.',
            'type.required' => 'Le type de virage est obligatoire.',
            'type.in' => 'Le type de virage doit être l\'une des valeurs suivantes : gauche, droite, chicane, épingle.'
        ];
        
        // Valider les données
        if (!Validator::validate($data, $rules, $messages)) {
            // Stocker les erreurs en session
            $_SESSION['form_errors'] = Validator::getErrors();
            $_SESSION['form_data'] = $data;
            
            // Rediriger vers le formulaire
            $this->redirect(BASE_URL . '/circuits/add-virage/' . $id);
        }
        
        // Ajouter le virage
        $virageId = $circuitModel->addVirage($id, $data);
        
        if ($virageId) {
            // Ajouter un message de succès
            View::addNotification('success', 'Virage ajouté avec succès.');
            
            // Rediriger vers les détails du circuit
            $this->redirect(BASE_URL . '/circuits/view/' . $id);
        } else {
            // Erreur lors de l'ajout du virage
            View::addNotification('error', 'Une erreur est survenue lors de l\'ajout du virage. Veuillez réessayer.');
            $_SESSION['form_data'] = $data;
            $this->redirect(BASE_URL . '/circuits/add-virage/' . $id);
        }
    }
    
    /**
     * Afficher le formulaire d'ajout de secteur
     * 
     * @param int $id ID du circuit
     */
    public function addSecteur($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Récupérer le circuit avec ses virages
        $circuitModel = new CircuitModel();
        $circuit = $circuitModel->getWithDetails($id);
        
        // Vérifier si le circuit existe et si l'utilisateur est le créateur
        if (!$circuit || !$circuitModel->belongsToUser($id, $_SESSION['user_id'])) {
            View::addNotification('error', 'Circuit non trouvé ou accès non autorisé.');
            $this->redirect(BASE_URL . '/circuits');
        }
        
        // Vérifier si le circuit a des virages
        if (empty($circuit['virages'])) {
            View::addNotification('error', 'Vous devez d\'abord ajouter des virages à ce circuit avant de créer un secteur.');
            $this->redirect(BASE_URL . '/circuits/view/' . $id);
        }
        
        // Générer un jeton CSRF
        $csrfToken = $this->generateCsrfToken();
        
        // Afficher la vue
        $this->view('circuits/add_secteur', [
            'circuit' => $circuit,
            'csrf_token' => $csrfToken,
            'title' => 'Ajouter un Secteur'
        ]);
    }
    
    /**
     * Traiter l'ajout d'un secteur
     * 
     * @param int $id ID du circuit
     */
    public function storeSecteur($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Vérifier le jeton CSRF
        if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
            View::addNotification('error', 'Erreur de sécurité. Veuillez réessayer.');
            $this->redirect(BASE_URL . '/circuits/add-secteur/' . $id);
        }
        
        // Récupérer le circuit
        $circuitModel = new CircuitModel();
        $circuit = $circuitModel->find($id);
        
        // Vérifier si le circuit existe et si l'utilisateur est le créateur
        if (!$circuit || !$circuitModel->belongsToUser($id, $_SESSION['user_id'])) {
            View::addNotification('error', 'Circuit non trouvé ou accès non autorisé.');
            $this->redirect(BASE_URL . '/circuits');
        }
        
        // Récupérer les données du formulaire
        $data = [
            'nom' => $this->post('nom'),
            'numero_debut' => $this->post('numero_debut'),
            'numero_fin' => $this->post('numero_fin'),
            'longueur' => $this->post('longueur'),
            'description' => $this->post('description')
        ];
        
        // Règles de validation
        $rules = [
            'nom' => 'required|max:50',
            'numero_debut' => 'required|numeric|min:1',
            'numero_fin' => 'required|numeric|min:1'
        ];
        
        // Messages personnalisés
        $messages = [
            'nom.required' => 'Le nom du secteur est obligatoire.',
            'nom.max' => 'Le nom du secteur ne doit pas dépasser 50 caractères.',
            'numero_debut.required' => 'Le numéro de début est obligatoire.',
            'numero_debut.numeric' => 'Le numéro de début doit être un nombre.',
            'numero_debut.min' => 'Le numéro de début doit être supérieur à 0.',
            'numero_fin.required' => 'Le numéro de fin est obligatoire.',
            'numero_fin.numeric' => 'Le numéro de fin doit être un nombre.',
            'numero_fin.min' => 'Le numéro de fin doit être supérieur à 0.'
        ];
        
        // Valider les données
        if (!Validator::validate($data, $rules, $messages)) {
            // Stocker les erreurs en session
            $_SESSION['form_errors'] = Validator::getErrors();
            $_SESSION['form_data'] = $data;
            
            // Rediriger vers le formulaire
            $this->redirect(BASE_URL . '/circuits/add-secteur/' . $id);
        }
        
        // Vérifier que le numéro de fin est supérieur au numéro de début
        if ($data['numero_fin'] <= $data['numero_debut']) {
            $_SESSION['form_errors'] = ['numero_fin' => ['Le numéro de fin doit être supérieur au numéro de début.']];
            $_SESSION['form_data'] = $data;
            $this->redirect(BASE_URL . '/circuits/add-secteur/' . $id);
        }
        
        // Ajouter le secteur
        $secteurId = $circuitModel->addSecteur($id, $data);
        
        if ($secteurId) {
            // Ajouter un message de succès
            View::addNotification('success', 'Secteur ajouté avec succès.');
            
            // Rediriger vers les détails du circuit
            $this->redirect(BASE_URL . '/circuits/view/' . $id);
        } else {
            // Erreur lors de l'ajout du secteur
            View::addNotification('error', 'Une erreur est survenue lors de l\'ajout du secteur. Veuillez réessayer.');
            $_SESSION['form_data'] = $data;
            $this->redirect(BASE_URL . '/circuits/add-secteur/' . $id);
        }
    }
    
    /**
     * Rechercher des circuits
     */
    public function search() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Récupérer le terme de recherche
        $query = $this->get('q');
        
        if (empty($query)) {
            $this->redirect(BASE_URL . '/circuits');
        }
        
        // Rechercher les circuits
        $circuitModel = new CircuitModel();
        $results = $circuitModel->search($query);
        
        // Afficher la vue
        $this->view('circuits/search', [
            'results' => $results,
            'query' => $query,
            'title' => 'Recherche de Circuits'
        ]);
    }
}
