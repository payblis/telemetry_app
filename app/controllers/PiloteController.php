<?php
/**
 * Contrôleur pour la gestion des pilotes
 */
namespace App\Controllers;

use App\Models\PiloteModel;
use App\Utils\Validator;
use App\Utils\View;
use App\Utils\FileManager;

class PiloteController extends Controller {
    /**
     * Afficher la liste des pilotes
     */
    public function index() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Récupérer les pilotes de l'utilisateur
        $piloteModel = new PiloteModel();
        $pilotes = $piloteModel->getAllByUser($_SESSION['user_id']);
        
        // Afficher la vue
        $this->view('pilotes/index', [
            'pilotes' => $pilotes,
            'title' => 'Mes Pilotes'
        ]);
    }
    
    /**
     * Afficher les détails d'un pilote
     * 
     * @param int $id ID du pilote
     */
    public function view($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Récupérer le pilote
        $piloteModel = new PiloteModel();
        $pilote = $piloteModel->getWithStats($id);
        
        // Vérifier si le pilote existe et appartient à l'utilisateur
        if (!$pilote || !$piloteModel->belongsToUser($id, $_SESSION['user_id'])) {
            View::addNotification('error', 'Pilote non trouvé ou accès non autorisé.');
            $this->redirect(BASE_URL . '/pilotes');
        }
        
        // Récupérer les sessions récentes
        $sessions = $piloteModel->getRecentSessions($id);
        
        // Récupérer les performances par circuit
        $performances = $piloteModel->getPerformancesByCircuit($id);
        
        // Afficher la vue
        $this->view('pilotes/view', [
            'pilote' => $pilote,
            'sessions' => $sessions,
            'performances' => $performances,
            'title' => $pilote['prenom'] . ' ' . $pilote['nom']
        ]);
    }
    
    /**
     * Afficher le formulaire de création d'un pilote
     */
    public function create() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Générer un jeton CSRF
        $csrfToken = $this->generateCsrfToken();
        
        // Afficher la vue
        $this->view('pilotes/create', [
            'csrf_token' => $csrfToken,
            'title' => 'Ajouter un Pilote'
        ]);
    }
    
    /**
     * Traiter la création d'un pilote
     */
    public function store() {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Vérifier le jeton CSRF
        if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
            View::addNotification('error', 'Erreur de sécurité. Veuillez réessayer.');
            $this->redirect(BASE_URL . '/pilotes/create');
        }
        
        // Récupérer les données du formulaire
        $data = [
            'nom' => $this->post('nom'),
            'prenom' => $this->post('prenom'),
            'taille' => $this->post('taille'),
            'poids' => $this->post('poids'),
            'championnat' => $this->post('championnat'),
            'niveau_experience' => $this->post('niveau_experience'),
            'style_pilotage' => $this->post('style_pilotage'),
            'notes' => $this->post('notes')
        ];
        
        // Règles de validation
        $rules = [
            'nom' => 'required|max:50',
            'prenom' => 'required|max:50',
            'niveau_experience' => 'required|in:debutant,intermediaire,avance,expert'
        ];
        
        // Messages personnalisés
        $messages = [
            'nom.required' => 'Le nom est obligatoire.',
            'nom.max' => 'Le nom ne doit pas dépasser 50 caractères.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'prenom.max' => 'Le prénom ne doit pas dépasser 50 caractères.',
            'niveau_experience.required' => 'Le niveau d\'expérience est obligatoire.',
            'niveau_experience.in' => 'Le niveau d\'expérience doit être l\'une des valeurs suivantes : débutant, intermédiaire, avancé, expert.'
        ];
        
        // Valider les données
        if (!Validator::validate($data, $rules, $messages)) {
            // Stocker les erreurs en session
            $_SESSION['form_errors'] = Validator::getErrors();
            $_SESSION['form_data'] = $data;
            
            // Rediriger vers le formulaire
            $this->redirect(BASE_URL . '/pilotes/create');
        }
        
        // Ajouter l'ID de l'utilisateur
        $data['user_id'] = $_SESSION['user_id'];
        
        // Créer le pilote
        $piloteModel = new PiloteModel();
        $piloteId = $piloteModel->create($data);
        
        if ($piloteId) {
            // Ajouter un message de succès
            View::addNotification('success', 'Pilote ajouté avec succès.');
            
            // Rediriger vers la liste des pilotes
            $this->redirect(BASE_URL . '/pilotes/view/' . $piloteId);
        } else {
            // Erreur lors de la création du pilote
            View::addNotification('error', 'Une erreur est survenue lors de l\'ajout du pilote. Veuillez réessayer.');
            $_SESSION['form_data'] = $data;
            $this->redirect(BASE_URL . '/pilotes/create');
        }
    }
    
    /**
     * Afficher le formulaire de modification d'un pilote
     * 
     * @param int $id ID du pilote
     */
    public function edit($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Récupérer le pilote
        $piloteModel = new PiloteModel();
        $pilote = $piloteModel->find($id);
        
        // Vérifier si le pilote existe et appartient à l'utilisateur
        if (!$pilote || !$piloteModel->belongsToUser($id, $_SESSION['user_id'])) {
            View::addNotification('error', 'Pilote non trouvé ou accès non autorisé.');
            $this->redirect(BASE_URL . '/pilotes');
        }
        
        // Générer un jeton CSRF
        $csrfToken = $this->generateCsrfToken();
        
        // Afficher la vue
        $this->view('pilotes/edit', [
            'pilote' => $pilote,
            'csrf_token' => $csrfToken,
            'title' => 'Modifier le Pilote'
        ]);
    }
    
    /**
     * Traiter la modification d'un pilote
     * 
     * @param int $id ID du pilote
     */
    public function update($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Vérifier le jeton CSRF
        if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
            View::addNotification('error', 'Erreur de sécurité. Veuillez réessayer.');
            $this->redirect(BASE_URL . '/pilotes/edit/' . $id);
        }
        
        // Récupérer le pilote
        $piloteModel = new PiloteModel();
        $pilote = $piloteModel->find($id);
        
        // Vérifier si le pilote existe et appartient à l'utilisateur
        if (!$pilote || !$piloteModel->belongsToUser($id, $_SESSION['user_id'])) {
            View::addNotification('error', 'Pilote non trouvé ou accès non autorisé.');
            $this->redirect(BASE_URL . '/pilotes');
        }
        
        // Récupérer les données du formulaire
        $data = [
            'nom' => $this->post('nom'),
            'prenom' => $this->post('prenom'),
            'taille' => $this->post('taille'),
            'poids' => $this->post('poids'),
            'championnat' => $this->post('championnat'),
            'niveau_experience' => $this->post('niveau_experience'),
            'style_pilotage' => $this->post('style_pilotage'),
            'notes' => $this->post('notes')
        ];
        
        // Règles de validation
        $rules = [
            'nom' => 'required|max:50',
            'prenom' => 'required|max:50',
            'niveau_experience' => 'required|in:debutant,intermediaire,avance,expert'
        ];
        
        // Messages personnalisés
        $messages = [
            'nom.required' => 'Le nom est obligatoire.',
            'nom.max' => 'Le nom ne doit pas dépasser 50 caractères.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'prenom.max' => 'Le prénom ne doit pas dépasser 50 caractères.',
            'niveau_experience.required' => 'Le niveau d\'expérience est obligatoire.',
            'niveau_experience.in' => 'Le niveau d\'expérience doit être l\'une des valeurs suivantes : débutant, intermédiaire, avancé, expert.'
        ];
        
        // Valider les données
        if (!Validator::validate($data, $rules, $messages)) {
            // Stocker les erreurs en session
            $_SESSION['form_errors'] = Validator::getErrors();
            $_SESSION['form_data'] = $data;
            
            // Rediriger vers le formulaire
            $this->redirect(BASE_URL . '/pilotes/edit/' . $id);
        }
        
        // Mettre à jour le pilote
        if ($piloteModel->update($id, $data)) {
            // Ajouter un message de succès
            View::addNotification('success', 'Pilote mis à jour avec succès.');
            
            // Rediriger vers les détails du pilote
            $this->redirect(BASE_URL . '/pilotes/view/' . $id);
        } else {
            // Erreur lors de la mise à jour du pilote
            View::addNotification('error', 'Une erreur est survenue lors de la mise à jour du pilote. Veuillez réessayer.');
            $_SESSION['form_data'] = $data;
            $this->redirect(BASE_URL . '/pilotes/edit/' . $id);
        }
    }
    
    /**
     * Supprimer un pilote
     * 
     * @param int $id ID du pilote
     */
    public function delete($id) {
        // Vérifier si l'utilisateur est connecté
        if (!$this->isLoggedIn(true)) {
            return;
        }
        
        // Vérifier le jeton CSRF
        if (!$this->verifyCsrfToken($this->post('csrf_token'))) {
            View::addNotification('error', 'Erreur de sécurité. Veuillez réessayer.');
            $this->redirect(BASE_URL . '/pilotes');
        }
        
        // Récupérer le pilote
        $piloteModel = new PiloteModel();
        $pilote = $piloteModel->find($id);
        
        // Vérifier si le pilote existe et appartient à l'utilisateur
        if (!$pilote || !$piloteModel->belongsToUser($id, $_SESSION['user_id'])) {
            View::addNotification('error', 'Pilote non trouvé ou accès non autorisé.');
            $this->redirect(BASE_URL . '/pilotes');
        }
        
        // Vérifier si le pilote a des sessions
        if ($piloteModel->countSessions($id) > 0) {
            View::addNotification('error', 'Impossible de supprimer ce pilote car il est associé à des sessions.');
            $this->redirect(BASE_URL . '/pilotes/view/' . $id);
        }
        
        // Supprimer le pilote
        if ($piloteModel->delete($id)) {
            // Ajouter un message de succès
            View::addNotification('success', 'Pilote supprimé avec succès.');
            
            // Rediriger vers la liste des pilotes
            $this->redirect(BASE_URL . '/pilotes');
        } else {
            // Erreur lors de la suppression du pilote
            View::addNotification('error', 'Une erreur est survenue lors de la suppression du pilote. Veuillez réessayer.');
            $this->redirect(BASE_URL . '/pilotes/view/' . $id);
        }
    }
}
