<?php

class PiloteController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        try {
            $stmt = $this->db->query("
                SELECT p.*, COUNT(s.id) as total_sessions 
                FROM pilotes p 
                LEFT JOIN sessions s ON p.id = s.pilote_id 
                GROUP BY p.id 
                ORDER BY p.nom, p.prenom
            ");
            $pilotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $pageTitle = "Liste des pilotes";
            require_once 'app/views/pilote/index.php';
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = "Erreur lors de la récupération des pilotes.";
            $_SESSION['flash_type'] = "danger";
            header('Location: index.php?route=dashboard');
            exit;
        }
    }

    public function create() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $stmt = $this->db->prepare("
                    INSERT INTO pilotes (nom, prenom, poids, taille, age, experience, date_creation) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                
                $stmt->execute([
                    $_POST['nom'],
                    $_POST['prenom'],
                    $_POST['poids'],
                    $_POST['taille'],
                    $_POST['age'],
                    $_POST['experience']
                ]);

                $_SESSION['flash_message'] = "Pilote ajouté avec succès.";
                $_SESSION['flash_type'] = "success";
                header('Location: index.php?route=pilotes');
                exit;
            } catch (PDOException $e) {
                $_SESSION['flash_message'] = "Erreur lors de l'ajout du pilote.";
                $_SESSION['flash_type'] = "danger";
            }
        }

        $pageTitle = "Ajouter un pilote";
        require_once 'app/views/pilote/create.php';
    }

    public function edit($id) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $stmt = $this->db->prepare("
                    UPDATE pilotes 
                    SET nom = ?, prenom = ?, poids = ?, taille = ?, age = ?, experience = ? 
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $_POST['nom'],
                    $_POST['prenom'],
                    $_POST['poids'],
                    $_POST['taille'],
                    $_POST['age'],
                    $_POST['experience'],
                    $id
                ]);

                $_SESSION['flash_message'] = "Pilote modifié avec succès.";
                $_SESSION['flash_type'] = "success";
                header('Location: index.php?route=pilotes');
                exit;
            }

            $stmt = $this->db->prepare("SELECT * FROM pilotes WHERE id = ?");
            $stmt->execute([$id]);
            $pilote = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pilote) {
                throw new Exception("Pilote non trouvé");
            }

            $pageTitle = "Modifier le pilote";
            require_once 'app/views/pilote/edit.php';
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Erreur lors de la modification du pilote.";
            $_SESSION['flash_type'] = "danger";
            header('Location: index.php?route=pilotes');
            exit;
        }
    }

    public function delete($id) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM pilotes WHERE id = ?");
            $stmt->execute([$id]);

            $_SESSION['flash_message'] = "Pilote supprimé avec succès.";
            $_SESSION['flash_type'] = "success";
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = "Erreur lors de la suppression du pilote.";
            $_SESSION['flash_type'] = "danger";
        }

        header('Location: index.php?route=pilotes');
        exit;
    }

    public function view($id) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        try {
            // Récupération des informations du pilote
            $stmt = $this->db->prepare("
                SELECT p.*, 
                       COUNT(DISTINCT s.id) as total_sessions,
                       COUNT(DISTINCT c.id) as total_circuits
                FROM pilotes p
                LEFT JOIN sessions s ON p.id = s.pilote_id
                LEFT JOIN circuits c ON s.circuit_id = c.id
                WHERE p.id = ?
                GROUP BY p.id
            ");
            $stmt->execute([$id]);
            $pilote = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pilote) {
                throw new Exception("Pilote non trouvé");
            }

            // Récupération des dernières sessions
            $stmt = $this->db->prepare("
                SELECT s.*, c.nom as circuit_nom, m.modele as moto_modele
                FROM sessions s
                JOIN circuits c ON s.circuit_id = c.id
                JOIN motos m ON s.moto_id = m.id
                WHERE s.pilote_id = ?
                ORDER BY s.date_session DESC
                LIMIT 5
            ");
            $stmt->execute([$id]);
            $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $pageTitle = "Profil du pilote";
            require_once 'app/views/pilote/view.php';
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Erreur lors de la récupération des informations du pilote.";
            $_SESSION['flash_type'] = "danger";
            header('Location: index.php?route=pilotes');
            exit;
        }
    }
} 