<?php
class PiloteController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create() {
        // Vérification de l'authentification
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = $_POST['nom'] ?? '';
            $prenom = $_POST['prenom'] ?? '';
            $poids = $_POST['poids'] ?? null;
            $taille = $_POST['taille'] ?? null;
            $age = $_POST['age'] ?? null;
            $experience = $_POST['experience'] ?? '';

            try {
                $stmt = $this->db->prepare("
                    INSERT INTO pilotes (nom, prenom, poids, taille, age, experience) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([$nom, $prenom, $poids, $taille, $age, $experience]);
                $success = 'Pilote ajouté avec succès';
                
            } catch (PDOException $e) {
                $error = 'Erreur lors de l\'ajout du pilote';
            }
        }

        $pageTitle = "Ajouter un pilote - Télémétrie IA";
        require_once APP_PATH . 'views/templates/header.php';
        require_once APP_PATH . 'views/pilote/create.php';
        require_once APP_PATH . 'views/templates/footer.php';
    }

    public function edit() {
        // Similaire à create() mais pour la modification
    }

    public function delete() {
        // Suppression d'un pilote
    }
} 