<?php

class EquipementController {
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
                SELECT e.*, COUNT(DISTINCT me.moto_id) as total_motos
                FROM equipements e 
                LEFT JOIN moto_equipement me ON e.id = me.equipement_id
                GROUP BY e.id 
                ORDER BY e.nom
            ");
            $equipements = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $pageTitle = "Liste des équipements";
            require_once 'app/views/equipement/index.php';
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = "Erreur lors de la récupération des équipements.";
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
                    INSERT INTO equipements (nom, description, type, fabricant, date_creation) 
                    VALUES (?, ?, ?, ?, NOW())
                ");
                
                $stmt->execute([
                    $_POST['nom'],
                    $_POST['description'],
                    $_POST['type'],
                    $_POST['fabricant']
                ]);

                $_SESSION['flash_message'] = "Équipement ajouté avec succès.";
                $_SESSION['flash_type'] = "success";
                header('Location: index.php?route=equipements');
                exit;
            } catch (PDOException $e) {
                $_SESSION['flash_message'] = "Erreur lors de l'ajout de l'équipement.";
                $_SESSION['flash_type'] = "danger";
            }
        }

        $pageTitle = "Ajouter un équipement";
        require_once 'app/views/equipement/create.php';
    }

    public function edit($id) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $stmt = $this->db->prepare("
                    UPDATE equipements 
                    SET nom = ?, description = ?, type = ?, fabricant = ?
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $_POST['nom'],
                    $_POST['description'],
                    $_POST['type'],
                    $_POST['fabricant'],
                    $id
                ]);

                $_SESSION['flash_message'] = "Équipement modifié avec succès.";
                $_SESSION['flash_type'] = "success";
                header('Location: index.php?route=equipements');
                exit;
            }

            $stmt = $this->db->prepare("SELECT * FROM equipements WHERE id = ?");
            $stmt->execute([$id]);
            $equipement = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$equipement) {
                throw new Exception("Équipement non trouvé");
            }

            $pageTitle = "Modifier l'équipement";
            require_once 'app/views/equipement/edit.php';
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Erreur lors de la modification de l'équipement.";
            $_SESSION['flash_type'] = "danger";
            header('Location: index.php?route=equipements');
            exit;
        }
    }

    public function delete($id) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        try {
            $this->db->beginTransaction();

            // Suppression des relations avec les motos
            $stmt = $this->db->prepare("DELETE FROM moto_equipement WHERE equipement_id = ?");
            $stmt->execute([$id]);

            // Suppression de l'équipement
            $stmt = $this->db->prepare("DELETE FROM equipements WHERE id = ?");
            $stmt->execute([$id]);

            $this->db->commit();

            $_SESSION['flash_message'] = "Équipement supprimé avec succès.";
            $_SESSION['flash_type'] = "success";
        } catch (PDOException $e) {
            $this->db->rollBack();
            $_SESSION['flash_message'] = "Erreur lors de la suppression de l'équipement.";
            $_SESSION['flash_type'] = "danger";
        }

        header('Location: index.php?route=equipements');
        exit;
    }

    public function view($id) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        try {
            // Récupération des informations de l'équipement
            $stmt = $this->db->prepare("
                SELECT e.*, COUNT(DISTINCT me.moto_id) as total_motos
                FROM equipements e
                LEFT JOIN moto_equipement me ON e.id = me.equipement_id
                WHERE e.id = ?
                GROUP BY e.id
            ");
            $stmt->execute([$id]);
            $equipement = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$equipement) {
                throw new Exception("Équipement non trouvé");
            }

            // Récupération des motos équipées
            $stmt = $this->db->prepare("
                SELECT m.*, COUNT(s.id) as total_sessions
                FROM motos m
                JOIN moto_equipement me ON m.id = me.moto_id
                LEFT JOIN sessions s ON m.id = s.moto_id
                WHERE me.equipement_id = ?
                GROUP BY m.id
                ORDER BY m.marque, m.modele
            ");
            $stmt->execute([$id]);
            $motos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $pageTitle = "Détails de l'équipement";
            require_once 'app/views/equipement/view.php';
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Erreur lors de la récupération des informations de l'équipement.";
            $_SESSION['flash_type'] = "danger";
            header('Location: index.php?route=equipements');
            exit;
        }
    }
} 