<?php
/**
 * Contrôleur pour la gestion des motos
 */
namespace App\Controllers;

use App\Models\MotoModel;
use App\Utils\Validator;
use App\Utils\View;
use App\Utils\FileManager;

class MotoController extends Controller {
    /**
     * Afficher la liste des motos
     */
    public function index() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Récupérer les motos de l'utilisateur
        $motoModel = new MotoModel();
        $motos = $motoModel->getAllByUser($_SESSION['user_id']);
        
        // Afficher la vue
        $this->view('motos/index', [
            'motos' => $motos,
            'title' => 'Mes Motos'
        ]);
    }
    
    /**
     * Afficher les détails d'une moto
     * 
     * @param int $id ID de la moto
     */
    public function view($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Récupérer la moto
        $motoModel = new MotoModel();
        $moto = $motoModel->getWithSettings($id);
        
        // Vérifier si la moto existe et appartient à l'utilisateur
        if (!$moto || !$motoModel->belongsToUser($id, $_SESSION['user_id'])) {
            View::addNotification('error', 'Moto non trouvée ou accès non autorisé.');
            $this->redirect(BASE_URL . '/motos');
        }
        
        // Récupérer les statistiques d'utilisation
        $stats = $motoModel->getUsageStats($id);
        
        // Afficher la vue
        $this->view('motos/view', [
            'moto' => $moto,
            'stats' => $stats,
            'title' => $moto['marque'] . ' ' . $moto['modele']
        ]);
    }
    
    /**
     * Afficher le formulaire de création d'une moto
     */
    public function create() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Générer un jeton CSRF
        $csrfToken = $this->generateCsrfToken();
        
        // Afficher la vue
        $this->view('motos/create', [
            'csrf_token' => $csrfToken,
            'title' => 'Ajouter une Moto'
        ]);
    }
    
    /**
     * Traiter la création d'une moto
     */
    public function store() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Vérifier le jeton CSRF
        if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
            View::addNotification('error', 'Erreur de sécurité. Veuillez réessayer.');
            $this->redirect(BASE_URL . '/motos/create');
        }
        
        // Récupérer les données du formulaire
        $data = [
            'marque' => $this->post('marque'),
            'modele' => $this->post('modele'),
            'annee' => $this->post('annee'),
            'cylindree' => $this->post('cylindree'),
            'poids_sec' => $this->post('poids_sec'),
            'type_moteur' => $this->post('type_moteur'),
            'type_cadre' => $this->post('type_cadre'),
            'notes' => $this->post('notes')
        ];
        
        // Règles de validation
        $rules = [
            'marque' => 'required|max:50',
            'modele' => 'required|max:50',
            'annee' => 'numeric|min:1900|max:' . (date('Y') + 1)
        ];
        
        // Messages personnalisés
        $messages = [
            'marque.required' => 'La marque est obligatoire.',
            'marque.max' => 'La marque ne doit pas dépasser 50 caractères.',
            'modele.required' => 'Le modèle est obligatoire.',
            'modele.max' => 'Le modèle ne doit pas dépasser 50 caractères.',
            'annee.numeric' => 'L\'année doit être un nombre.',
            'annee.min' => 'L\'année doit être supérieure à 1900.',
            'annee.max' => 'L\'année ne peut pas être dans le futur.'
        ];
        
        // Valider les données
        if (!Validator::validate($data, $rules, $messages)) {
            // Stocker les erreurs en session
            $_SESSION['form_errors'] = Validator::getErrors();
            $_SESSION['form_data'] = $data;
            
            // Rediriger vers le formulaire
            $this->redirect(BASE_URL . '/motos/create');
        }
        
        // Traiter l'image si elle existe
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileManager = new FileManager();
            $imagePath = $fileManager->uploadImage($_FILES['image'], 'motos');
            
            if ($imagePath) {
                $data['image_path'] = $imagePath;
            } else {
                View::addNotification('error', 'Une erreur est survenue lors du téléchargement de l\'image. Veuillez réessayer.');
                $_SESSION['form_data'] = $data;
                $this->redirect(BASE_URL . '/motos/create');
            }
        }
        
        // Ajouter l'ID de l'utilisateur
        $data['user_id'] = $_SESSION['user_id'];
        
        // Créer la moto
        $motoModel = new MotoModel();
        $motoId = $motoModel->create($data);
        
        if ($motoId) {
            // Ajouter un message de succès
            View::addNotification('success', 'Moto ajoutée avec succès.');
            
            // Rediriger vers la liste des motos
            $this->redirect(BASE_URL . '/motos/view/' . $motoId);
        } else {
            // Erreur lors de la création de la moto
            View::addNotification('error', 'Une erreur est survenue lors de l\'ajout de la moto. Veuillez réessayer.');
            $_SESSION['form_data'] = $data;
            $this->redirect(BASE_URL . '/motos/create');
        }
    }
    
    /**
     * Afficher le formulaire de modification d'une moto
     * 
     * @param int $id ID de la moto
     */
    public function edit($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Récupérer la moto
        $motoModel = new MotoModel();
        $moto = $motoModel->find($id);
        
        // Vérifier si la moto existe et appartient à l'utilisateur
        if (!$moto || !$motoModel->belongsToUser($id, $_SESSION['user_id'])) {
            View::addNotification('error', 'Moto non trouvée ou accès non autorisé.');
            $this->redirect(BASE_URL . '/motos');
        }
        
        // Générer un jeton CSRF
        $csrfToken = $this->generateCsrfToken();
        
        // Afficher la vue
        $this->view('motos/edit', [
            'moto' => $moto,
            'csrf_token' => $csrfToken,
            'title' => 'Modifier la Moto'
        ]);
    }
    
    /**
     * Traiter la modification d'une moto
     * 
     * @param int $id ID de la moto
     */
    public function update($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Vérifier le jeton CSRF
        if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
            View::addNotification('error', 'Erreur de sécurité. Veuillez réessayer.');
            $this->redirect(BASE_URL . '/motos/edit/' . $id);
        }
        
        // Récupérer la moto
        $motoModel = new MotoModel();
        $moto = $motoModel->find($id);
        
        // Vérifier si la moto existe et appartient à l'utilisateur
        if (!$moto || !$motoModel->belongsToUser($id, $_SESSION['user_id'])) {
            View::addNotification('error', 'Moto non trouvée ou accès non autorisé.');
            $this->redirect(BASE_URL . '/motos');
        }
        
        // Récupérer les données du formulaire
        $data = [
            'marque' => $this->post('marque'),
            'modele' => $this->post('modele'),
            'annee' => $this->post('annee'),
            'cylindree' => $this->post('cylindree'),
            'poids_sec' => $this->post('poids_sec'),
            'type_moteur' => $this->post('type_moteur'),
            'type_cadre' => $this->post('type_cadre'),
            'notes' => $this->post('notes')
        ];
        
        // Règles de validation
        $rules = [
            'marque' => 'required|max:50',
            'modele' => 'required|max:50',
            'annee' => 'numeric|min:1900|max:' . (date('Y') + 1)
        ];
        
        // Messages personnalisés
        $messages = [
            'marque.required' => 'La marque est obligatoire.',
            'marque.max' => 'La marque ne doit pas dépasser 50 caractères.',
            'modele.required' => 'Le modèle est obligatoire.',
            'modele.max' => 'Le modèle ne doit pas dépasser 50 caractères.',
            'annee.numeric' => 'L\'année doit être un nombre.',
            'annee.min' => 'L\'année doit être supérieure à 1900.',
            'annee.max' => 'L\'année ne peut pas être dans le futur.'
        ];
        
        // Valider les données
        if (!Validator::validate($data, $rules, $messages)) {
            // Stocker les erreurs en session
            $_SESSION['form_errors'] = Validator::getErrors();
            $_SESSION['form_data'] = $data;
            
            // Rediriger vers le formulaire
            $this->redirect(BASE_URL . '/motos/edit/' . $id);
        }
        
        // Traiter l'image si elle existe
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileManager = new FileManager();
            $imagePath = $fileManager->uploadImage($_FILES['image'], 'motos');
            
            if ($imagePath) {
                $data['image_path'] = $imagePath;
                
                // Supprimer l'ancienne image si elle existe
                if (!empty($moto['image_path'])) {
                    $fileManager->deleteFile($moto['image_path']);
                }
            } else {
                View::addNotification('error', 'Une erreur est survenue lors du téléchargement de l\'image. Veuillez réessayer.');
                $_SESSION['form_data'] = $data;
                $this->redirect(BASE_URL . '/motos/edit/' . $id);
            }
        }
        
        // Mettre à jour la moto
        if ($motoModel->update($id, $data)) {
            // Ajouter un message de succès
            View::addNotification('success', 'Moto mise à jour avec succès.');
            
            // Rediriger vers les détails de la moto
            $this->redirect(BASE_URL . '/motos/view/' . $id);
        } else {
            // Erreur lors de la mise à jour de la moto
            View::addNotification('error', 'Une erreur est survenue lors de la mise à jour de la moto. Veuillez réessayer.');
            $_SESSION['form_data'] = $data;
            $this->redirect(BASE_URL . '/motos/edit/' . $id);
        }
    }
    
    /**
     * Supprimer une moto
     * 
     * @param int $id ID de la moto
     */
    public function delete($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Vérifier le jeton CSRF
        if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
            View::addNotification('error', 'Erreur de sécurité. Veuillez réessayer.');
            $this->redirect(BASE_URL . '/motos');
        }
        
        // Récupérer la moto
        $motoModel = new MotoModel();
        $moto = $motoModel->find($id);
        
        // Vérifier si la moto existe et appartient à l'utilisateur
        if (!$moto || !$motoModel->belongsToUser($id, $_SESSION['user_id'])) {
            View::addNotification('error', 'Moto non trouvée ou accès non autorisé.');
            $this->redirect(BASE_URL . '/motos');
        }
        
        // Vérifier si la moto a des sessions
        if ($motoModel->countSessions($id) > 0) {
            View::addNotification('error', 'Impossible de supprimer cette moto car elle est associée à des sessions.');
            $this->redirect(BASE_URL . '/motos/view/' . $id);
        }
        
        // Supprimer l'image si elle existe
        if (!empty($moto['image_path'])) {
            $fileManager = new FileManager();
            $fileManager->deleteFile($moto['image_path']);
        }
        
        // Supprimer la moto
        if ($motoModel->delete($id)) {
            // Ajouter un message de succès
            View::addNotification('success', 'Moto supprimée avec succès.');
            
            // Rediriger vers la liste des motos
            $this->redirect(BASE_URL . '/motos');
        } else {
            // Erreur lors de la suppression de la moto
            View::addNotification('error', 'Une erreur est survenue lors de la suppression de la moto. Veuillez réessayer.');
            $this->redirect(BASE_URL . '/motos/view/' . $id);
        }
    }
    
    /**
     * Afficher le formulaire d'ajout de réglage
     * 
     * @param int $id ID de la moto
     */
    public function addReglage($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Récupérer la moto
        $motoModel = new MotoModel();
        $moto = $motoModel->find($id);
        
        // Vérifier si la moto existe et appartient à l'utilisateur
        if (!$moto || !$motoModel->belongsToUser($id, $_SESSION['user_id'])) {
            View::addNotification('error', 'Moto non trouvée ou accès non autorisé.');
            $this->redirect(BASE_URL . '/motos');
        }
        
        // Générer un jeton CSRF
        $csrfToken = $this->generateCsrfToken();
        
        // Afficher la vue
        $this->view('motos/add_reglage', [
            'moto' => $moto,
            'csrf_token' => $csrfToken,
            'title' => 'Ajouter un Réglage'
        ]);
    }
    
    /**
     * Traiter l'ajout d'un réglage
     * 
     * @param int $id ID de la moto
     */
    public function storeReglage($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Vérifier le jeton CSRF
        if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
            View::addNotification('error', 'Erreur de sécurité. Veuillez réessayer.');
            $this->redirect(BASE_URL . '/motos/add-reglage/' . $id);
        }
        
        // Récupérer la moto
        $motoModel = new MotoModel();
        $moto = $motoModel->find($id);
        
        // Vérifier si la moto existe et appartient à l'utilisateur
        if (!$moto || !$motoModel->belongsToUser($id, $_SESSION['user_id'])) {
            View::addNotification('error', 'Moto non trouvée ou accès non autorisé.');
            $this->redirect(BASE_URL . '/motos');
        }
        
        // Récupérer les données du formulaire
        $data = [
            'nom_reglage' => $this->post('nom_reglage'),
            'type_reglage' => $this->post('type_reglage'),
            'valeur_defaut' => $this->post('valeur_defaut'),
            'valeur_min' => $this->post('valeur_min'),
            'valeur_max' => $this->post('valeur_max'),
            'unite' => $this->post('unite'),
            'description' => $this->post('description')
        ];
        
        // Règles de validation
        $rules = [
            'nom_reglage' => 'required|max:50',
            'type_reglage' => 'required|max:50'
        ];
        
        // Messages personnalisés
        $messages = [
            'nom_reglage.required' => 'Le nom du réglage est obligatoire.',
            'nom_reglage.max' => 'Le nom du réglage ne doit pas dépasser 50 caractères.',
            'type_reglage.required' => 'Le type de réglage est obligatoire.',
            'type_reglage.max' => 'Le type de réglage ne doit pas dépasser 50 caractères.'
        ];
        
        // Valider les données
        if (!Validator::validate($data, $rules, $messages)) {
            // Stocker les erreurs en session
            $_SESSION['form_errors'] = Validator::getErrors();
            $_SESSION['form_data'] = $data;
            
            // Rediriger vers le formulaire
            $this->redirect(BASE_URL . '/motos/add-reglage/' . $id);
        }
        
        // Ajouter le réglage
        $reglageId = $motoModel->addReglage($id, $data);
        
        if ($reglageId) {
            // Ajouter un message de succès
            View::addNotification('success', 'Réglage ajouté avec succès.');
            
            // Rediriger vers les détails de la moto
            $this->redirect(BASE_URL . '/motos/view/' . $id);
        } else {
            // Erreur lors de l'ajout du réglage
            View::addNotification('error', 'Une erreur est survenue lors de l\'ajout du réglage. Veuillez réessayer.');
            $_SESSION['form_data'] = $data;
            $this->redirect(BASE_URL . '/motos/add-reglage/' . $id);
        }
    }
    
    /**
     * Afficher le formulaire de création d'une configuration
     * 
     * @param int $id ID de la moto
     */
    public function addConfiguration($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Récupérer la moto avec ses réglages
        $motoModel = new MotoModel();
        $moto = $motoModel->getWithSettings($id);
        
        // Vérifier si la moto existe et appartient à l'utilisateur
        if (!$moto || !$motoModel->belongsToUser($id, $_SESSION['user_id'])) {
            View::addNotification('error', 'Moto non trouvée ou accès non autorisé.');
            $this->redirect(BASE_URL . '/motos');
        }
        
        // Vérifier si la moto a des réglages
        if (empty($moto['reglages'])) {
            View::addNotification('error', 'Vous devez d\'abord ajouter des réglages à cette moto avant de créer une configuration.');
            $this->redirect(BASE_URL . '/motos/view/' . $id);
        }
        
        // Générer un jeton CSRF
        $csrfToken = $this->generateCsrfToken();
        
        // Afficher la vue
        $this->view('motos/add_configuration', [
            'moto' => $moto,
            'csrf_token' => $csrfToken,
            'title' => 'Ajouter une Configuration'
        ]);
    }
    
    /**
     * Traiter la création d'une configuration
     * 
     * @param int $id ID de la moto
     */
    public function storeConfiguration($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Vérifier le jeton CSRF
        if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
            View::addNotification('error', 'Erreur de sécurité. Veuillez réessayer.');
            $this->redirect(BASE_URL . '/motos/add-configuration/' . $id);
        }
        
        // Récupérer la moto
        $motoModel = new MotoModel();
        $moto = $motoModel->find($id);
        
        // Vérifier si la moto existe et appartient à l'utilisateur
        if (!$moto || !$motoModel->belongsToUser($id, $_SESSION['user_id'])) {
            View::addNotification('error', 'Moto non trouvée ou accès non autorisé.');
            $this->redirect(BASE_URL . '/motos');
        }
        
        // Récupérer les données du formulaire
        $configData = [
            'nom' => $this->post('nom'),
            'description' => $this->post('description'),
            'circuit_type' => $this->post('circuit_type'),
            'conditions_meteo' => $this->post('conditions_meteo'),
            'est_configuration_defaut' => $this->post('est_configuration_defaut') ? 1 : 0
        ];
        
        // Récupérer les valeurs des réglages
        $reglages = [];
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'reglage_') === 0) {
                $reglageId = substr($key, 8);
                $reglages[$reglageId] = $value;
            }
        }
        
        // Règles de validation
        $rules = [
            'nom' => 'required|max:50'
        ];
        
        // Messages personnalisés
        $messages = [
            'nom.required' => 'Le nom de la configuration est obligatoire.',
            'nom.max' => 'Le nom de la configuration ne doit pas dépasser 50 caractères.'
        ];
        
        // Valider les données
        if (!Validator::validate($configData, $rules, $messages)) {
            // Stocker les erreurs en session
            $_SESSION['form_errors'] = Validator::getErrors();
            $_SESSION['form_data'] = $configData;
            
            // Rediriger vers le formulaire
            $this->redirect(BASE_URL . '/motos/add-configuration/' . $id);
        }
        
        // Si cette configuration est définie comme par défaut, mettre à jour les autres configurations
        if ($configData['est_configuration_defaut']) {
            $stmt = $motoModel->db->prepare("UPDATE configurations_moto SET est_configuration_defaut = 0 WHERE moto_id = :moto_id");
            $stmt->execute(['moto_id' => $id]);
        }
        
        // Créer la configuration
        $configId = $motoModel->createConfiguration($id, $configData, $reglages);
        
        if ($configId) {
            // Ajouter un message de succès
            View::addNotification('success', 'Configuration ajoutée avec succès.');
            
            // Rediriger vers les détails de la moto
            $this->redirect(BASE_URL . '/motos/view/' . $id);
        } else {
            // Erreur lors de la création de la configuration
            View::addNotification('error', 'Une erreur est survenue lors de l\'ajout de la configuration. Veuillez réessayer.');
            $_SESSION['form_data'] = $configData;
            $this->redirect(BASE_URL . '/motos/add-configuration/' . $id);
        }
    }
}
