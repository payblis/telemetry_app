<?php

class MotoController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function index() {
        error_log("MotoController::index() called");
        
        if (!isset($_SESSION['user_id'])) {
            error_log("User not logged in, redirecting to login");
            header('Location: index.php?route=login');
            exit;
        }

        try {
            error_log("Executing motos query");
            $stmt = $this->db->query("
                SELECT m.*, COUNT(s.id) as total_sessions,
                       GROUP_CONCAT(DISTINCT CONCAT(e.marque, ' ', e.modele) SEPARATOR ', ') as equipements
                FROM motos m 
                LEFT JOIN sessions s ON m.id = s.moto_id
                LEFT JOIN moto_equipement me ON m.id = me.moto_id
                LEFT JOIN equipements e ON me.equipement_id = e.id
                GROUP BY m.id 
                ORDER BY m.marque, m.modele
            ");
            $motos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Found " . count($motos) . " motos");
            
            $pageTitle = "Liste des motos";
            error_log("Loading view file: " . APP_PATH . 'views/moto/index.php');
            
            if (!file_exists(APP_PATH . 'views/moto/index.php')) {
                error_log("View file not found: " . APP_PATH . 'views/moto/index.php');
                throw new Exception("View file not found");
            }
            
            require_once APP_PATH . 'views/moto/index.php';
        } catch (PDOException $e) {
            error_log("Database error in MotoController::index(): " . $e->getMessage());
            $_SESSION['flash_message'] = "Erreur lors de la récupération des motos.";
            $_SESSION['flash_type'] = "danger";
            header('Location: index.php?route=dashboard');
            exit;
        } catch (Exception $e) {
            error_log("General error in MotoController::index(): " . $e->getMessage());
            $_SESSION['flash_message'] = "Erreur lors de l'affichage des motos.";
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
                $this->db->beginTransaction();

                $stmt = $this->db->prepare("
                    INSERT INTO motos (marque, modele, annee, cylindree, poids, puissance) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $_POST['marque'],
                    $_POST['modele'],
                    $_POST['annee'],
                    $_POST['cylindree'],
                    $_POST['poids'],
                    $_POST['puissance']
                ]);

                $motoId = $this->db->lastInsertId();

                // Gestion des équipements
                if (isset($_POST['equipements']) && is_array($_POST['equipements'])) {
                    $stmt = $this->db->prepare("
                        INSERT INTO moto_equipement (moto_id, equipement_id) 
                        VALUES (?, ?)
                    ");
                    
                    foreach ($_POST['equipements'] as $equipementId) {
                        $stmt->execute([$motoId, $equipementId]);
                    }
                }

                $this->db->commit();

                $_SESSION['flash_message'] = "Moto ajoutée avec succès.";
                $_SESSION['flash_type'] = "success";
                header('Location: index.php?route=motos');
                exit;
            } catch (PDOException $e) {
                $this->db->rollBack();
                $_SESSION['flash_message'] = "Erreur lors de l'ajout de la moto.";
                $_SESSION['flash_type'] = "danger";
            }
        }

        // Récupération de la liste des équipements pour le formulaire
        try {
            $stmt = $this->db->query("SELECT * FROM equipements ORDER BY nom");
            $equipements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $equipements = [];
        }

        $pageTitle = "Ajouter une moto";
        require_once APP_PATH . 'views/moto/create.php';
    }

    public function edit($id) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->db->beginTransaction();

                $stmt = $this->db->prepare("
                    UPDATE motos 
                    SET marque = ?, modele = ?, annee = ?, cylindree = ?, poids = ?, puissance = ?
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $_POST['marque'],
                    $_POST['modele'],
                    $_POST['annee'],
                    $_POST['cylindree'],
                    $_POST['poids'],
                    $_POST['puissance'],
                    $id
                ]);

                // Mise à jour des équipements
                $stmt = $this->db->prepare("DELETE FROM moto_equipement WHERE moto_id = ?");
                $stmt->execute([$id]);

                if (isset($_POST['equipements']) && is_array($_POST['equipements'])) {
                    $stmt = $this->db->prepare("
                        INSERT INTO moto_equipement (moto_id, equipement_id) 
                        VALUES (?, ?)
                    ");
                    
                    foreach ($_POST['equipements'] as $equipementId) {
                        $stmt->execute([$id, $equipementId]);
                    }
                }

                $this->db->commit();

                $_SESSION['flash_message'] = "Moto modifiée avec succès.";
                $_SESSION['flash_type'] = "success";
                header('Location: index.php?route=motos');
                exit;
            }

            // Récupération des informations de la moto
            $stmt = $this->db->prepare("
                SELECT m.*, GROUP_CONCAT(me.equipement_id) as equipement_ids
                FROM motos m
                LEFT JOIN moto_equipement me ON m.id = me.moto_id
                WHERE m.id = ?
                GROUP BY m.id
            ");
            $stmt->execute([$id]);
            $moto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$moto) {
                throw new Exception("Moto non trouvée");
            }

            // Récupération de la liste des équipements
            $stmt = $this->db->query("SELECT * FROM equipements ORDER BY nom");
            $equipements = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $moto['equipement_ids'] = $moto['equipement_ids'] ? explode(',', $moto['equipement_ids']) : [];

            $pageTitle = "Modifier la moto";
            require_once APP_PATH . 'views/moto/edit.php';
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Erreur lors de la modification de la moto.";
            $_SESSION['flash_type'] = "danger";
            header('Location: index.php?route=motos');
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

            // Suppression des relations avec les équipements
            $stmt = $this->db->prepare("DELETE FROM moto_equipement WHERE moto_id = ?");
            $stmt->execute([$id]);

            // Suppression de la moto
            $stmt = $this->db->prepare("DELETE FROM motos WHERE id = ?");
            $stmt->execute([$id]);

            $this->db->commit();

            $_SESSION['flash_message'] = "Moto supprimée avec succès.";
            $_SESSION['flash_type'] = "success";
        } catch (PDOException $e) {
            $this->db->rollBack();
            $_SESSION['flash_message'] = "Erreur lors de la suppression de la moto.";
            $_SESSION['flash_type'] = "danger";
        }

        header('Location: index.php?route=motos');
        exit;
    }

    public function view($id) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        try {
            // Récupération des informations de la moto
            $stmt = $this->db->prepare("
                SELECT m.*, 
                       COUNT(DISTINCT s.id) as total_sessions,
                       COUNT(DISTINCT s.pilote_id) as total_pilotes,
                       GROUP_CONCAT(DISTINCT e.nom ORDER BY e.nom SEPARATOR ', ') as equipements
                FROM motos m
                LEFT JOIN sessions s ON m.id = s.moto_id
                LEFT JOIN moto_equipement me ON m.id = me.moto_id
                LEFT JOIN equipements e ON me.equipement_id = e.id
                WHERE m.id = ?
                GROUP BY m.id
            ");
            $stmt->execute([$id]);
            $moto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$moto) {
                throw new Exception("Moto non trouvée");
            }

            // Récupération des dernières sessions
            $stmt = $this->db->prepare("
                SELECT s.*, p.nom as pilote_nom, p.prenom as pilote_prenom, c.nom as circuit_nom
                FROM sessions s
                JOIN pilotes p ON s.pilote_id = p.id
                JOIN circuits c ON s.circuit_id = c.id
                WHERE s.moto_id = ?
                ORDER BY s.date_session DESC
                LIMIT 5
            ");
            $stmt->execute([$id]);
            $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $pageTitle = "Profil de la moto";
            require_once APP_PATH . 'views/moto/view.php';
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Erreur lors de la récupération des informations de la moto.";
            $_SESSION['flash_type'] = "danger";
            header('Location: index.php?route=motos');
            exit;
        }
    }

    public function specs() {
        if (!isset($_SESSION['user_id'])) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['success' => false, 'message' => 'Non autorisé']);
            exit;
        }

        // Vérifier si c'est une requête POST avec du JSON
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || !isset($data['marque']) || !isset($data['modele']) || !isset($data['annee'])) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['success' => false, 'message' => 'Données invalides']);
            exit;
        }

        require_once APP_PATH . 'helpers/ChatGPTHelper.php';
        $chatGPT = new ChatGPTHelper('votre_cle_api_ici');

        $specs = $chatGPT->getMotoSpecs($data['marque'], $data['modele'], $data['annee']);

        if ($specs === null) {
            echo json_encode([
                'success' => false,
                'message' => 'Impossible de récupérer les spécifications'
            ]);
            exit;
        }

        echo json_encode([
            'success' => true,
            'specs' => $specs
        ]);
        exit;
    }
} 