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
                SELECT m.*, 
                       COUNT(s.id) as total_sessions,
                       CONCAT(
                           COALESCE(e.marque, ''), ' ',
                           COALESCE(e.modele, ''),
                           ' / ',
                           COALESCE(f.etrier_avant_marque, ''), ' ',
                           COALESCE(f.etrier_avant_modele, ''),
                           ' / ',
                           COALESCE(t.chaine_marque, ''), ' ',
                           COALESCE(t.chaine_type, '')
                       ) as equipements_principaux
                FROM motos m 
                LEFT JOIN sessions s ON m.id = s.moto_id
                LEFT JOIN echappements e ON m.id = e.moto_id
                LEFT JOIN freins f ON m.id = f.moto_id
                LEFT JOIN transmissions t ON m.id = t.moto_id
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

                // 1. Insertion des informations générales de la moto
                $stmt = $this->db->prepare("
                    INSERT INTO motos (
                        marque, modele, annee, cylindree, type_moto, puissance_moteur,
                        couple_moteur, poids_sec, poids_ordre_marche
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $_POST['marque'],
                    $_POST['modele'],
                    $_POST['annee'],
                    $_POST['cylindree'],
                    $_POST['type_moto'],
                    $_POST['puissance_moteur'],
                    $_POST['couple_moteur'] ?: null,
                    $_POST['poids_sec'] ?: null,
                    $_POST['poids_ordre_marche'] ?: null
                ]);

                $motoId = $this->db->lastInsertId();

                // 2. Insertion des suspensions
                $stmt = $this->db->prepare("
                    INSERT INTO suspensions (
                        moto_id, fourche_marque, fourche_modele, fourche_precharge,
                        fourche_compression, fourche_detente, amortisseur_marque,
                        amortisseur_modele, amortisseur_precharge, amortisseur_compression_bv,
                        amortisseur_compression_hv, amortisseur_detente, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $motoId,
                    $_POST['fourche_marque'],
                    $_POST['fourche_modele'] ?: null,
                    $_POST['fourche_precharge'] ?: null,
                    $_POST['fourche_compression'] ?: null,
                    $_POST['fourche_detente'] ?: null,
                    $_POST['amortisseur_marque'],
                    $_POST['amortisseur_modele'] ?: null,
                    $_POST['amortisseur_precharge'] ?: null,
                    $_POST['amortisseur_compression_bv'] ?: null,
                    $_POST['amortisseur_compression_hv'] ?: null,
                    $_POST['amortisseur_detente'] ?: null,
                    $_POST['suspensions_notes'] ?: null
                ]);

                // 3. Insertion des freins
                $stmt = $this->db->prepare("
                    INSERT INTO freins (
                        moto_id, etrier_avant_marque, etrier_avant_modele,
                        etrier_arriere_marque, etrier_arriere_modele,
                        maitre_cylindre_avant_marque, maitre_cylindre_avant_modele,
                        maitre_cylindre_arriere_marque, maitre_cylindre_arriere_modele,
                        plaquettes_type, disques_type, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $motoId,
                    $_POST['etrier_avant_marque'],
                    $_POST['etrier_avant_modele'] ?: null,
                    $_POST['etrier_arriere_marque'] ?: null,
                    $_POST['etrier_arriere_modele'] ?: null,
                    $_POST['maitre_cylindre_avant_marque'] ?: null,
                    $_POST['maitre_cylindre_avant_modele'] ?: null,
                    $_POST['maitre_cylindre_arriere_marque'] ?: null,
                    $_POST['maitre_cylindre_arriere_modele'] ?: null,
                    $_POST['plaquettes_type'] ?: null,
                    $_POST['disques_type'] ?: null,
                    $_POST['freins_notes'] ?: null
                ]);

                // 4. Insertion de la transmission
                $stmt = $this->db->prepare("
                    INSERT INTO transmissions (
                        moto_id, chaine_marque, chaine_modele, chaine_type,
                        couronne_dents, pignon_dents, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $motoId,
                    $_POST['chaine_marque'] ?: null,
                    $_POST['chaine_modele'] ?: null,
                    $_POST['chaine_type'] ?: null,
                    $_POST['couronne_dents'],
                    $_POST['pignon_dents'],
                    $_POST['transmission_notes'] ?: null
                ]);

                // 5. Insertion de l'échappement
                $stmt = $this->db->prepare("
                    INSERT INTO echappements (
                        moto_id, marque, modele, type, notes
                    ) VALUES (?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $motoId,
                    $_POST['echappement_marque'],
                    $_POST['echappement_modele'] ?: null,
                    $_POST['echappement_type'] ?: null,
                    $_POST['echappement_notes'] ?: null
                ]);

                // 6. Insertion de l'électronique
                $stmt = $this->db->prepare("
                    INSERT INTO electroniques (
                        moto_id, ecu_marque, ecu_modele, capteur_vitesse,
                        capteur_regime, capteur_temperature_pneus, capteur_gps,
                        capteur_suspension, capteur_pression_pneus,
                        autres_capteurs, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $motoId,
                    $_POST['ecu_marque'],
                    $_POST['ecu_modele'] ?: null,
                    isset($_POST['capteur_vitesse']),
                    isset($_POST['capteur_regime']),
                    isset($_POST['capteur_temperature_pneus']),
                    isset($_POST['capteur_gps']),
                    isset($_POST['capteur_suspension']),
                    isset($_POST['capteur_pression_pneus']),
                    $_POST['autres_capteurs'] ?: null,
                    $_POST['electronique_notes'] ?: null
                ]);

                // 7. Insertion des pneumatiques
                $stmt = $this->db->prepare("
                    INSERT INTO pneumatiques (
                        moto_id, marque, modele, type_gomme,
                        pression_avant_froid, pression_arriere_froid, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $motoId,
                    $_POST['pneu_marque'],
                    $_POST['pneu_modele'] ?: null,
                    $_POST['type_gomme'] ?: null,
                    $_POST['pression_avant_froid'] ?: null,
                    $_POST['pression_arriere_froid'] ?: null,
                    $_POST['pneumatiques_notes'] ?: null
                ]);

                // 8. Insertion des accessoires
                $stmt = $this->db->prepare("
                    INSERT INTO accessoires (
                        moto_id, type_guidon, commandes_reculees_marque,
                        commandes_reculees_reglages, type_selle, type_carenage, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $motoId,
                    $_POST['type_guidon'] ?: null,
                    $_POST['commandes_reculees_marque'] ?: null,
                    $_POST['commandes_reculees_reglages'] ?: null,
                    $_POST['type_selle'] ?: null,
                    $_POST['type_carenage'] ?: null,
                    $_POST['accessoires_notes'] ?: null
                ]);

                $this->db->commit();

                $_SESSION['flash_message'] = "Moto ajoutée avec succès.";
                $_SESSION['flash_type'] = "success";
                header('Location: index.php?route=motos');
                exit;
            } catch (PDOException $e) {
                $this->db->rollBack();
                error_log("Erreur SQL dans MotoController::create(): " . $e->getMessage());
                $_SESSION['flash_message'] = "Erreur lors de l'ajout de la moto.";
                $_SESSION['flash_type'] = "danger";
            }
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
            $sql = "SELECT * FROM motos WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            $moto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$moto) {
                $_SESSION['flash_message'] = "Moto non trouvée";
                $_SESSION['flash_type'] = "danger";
                header('Location: index.php?route=motos');
                exit;
            }

            // Initialize all arrays with default values
            $data = [
                'moto' => $moto,
                'suspensions' => [],
                'freins' => [],
                'transmission' => [],
                'echappement' => [],
                'electronique' => [],
                'pneumatiques' => [],
                'capteurs' => [
                    'vitesse' => false,
                    'regime' => false,
                    'temperature_pneus' => false,
                    'gps' => false,
                    'suspension' => false,
                    'pression_pneus' => false
                ]
            ];

            // Récupération des données avec gestion des erreurs
            $tables = [
                'suspensions' => 'suspensions',
                'freins' => 'freins',
                'transmission' => 'transmissions',
                'echappement' => 'echappements',
                'electronique' => 'electroniques',
                'pneumatiques' => 'pneumatiques'
            ];

            foreach ($tables as $key => $table) {
                try {
                    $sql = "SELECT * FROM {$table} WHERE moto_id = :id";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute(['id' => $id]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($result) {
                        $data[$key] = $result;
                        
                        // Update capteurs if electronique data is found
                        if ($key === 'electronique' && $result) {
                            $data['capteurs'] = [
                                'vitesse' => $result['capteur_vitesse'] ?? false,
                                'regime' => $result['capteur_regime'] ?? false,
                                'temperature_pneus' => $result['capteur_temperature_pneus'] ?? false,
                                'gps' => $result['capteur_gps'] ?? false,
                                'suspension' => $result['capteur_suspension'] ?? false,
                                'pression_pneus' => $result['capteur_pression_pneus'] ?? false
                            ];
                        }
                    }
                } catch (PDOException $e) {
                    error_log("Erreur lors de la récupération des données de {$table}: " . $e->getMessage());
                    // Continue with next table if one fails
                    continue;
                }
            }

            // Extract all variables for the view
            extract($data);

            // Chargement de la vue avec toutes les données
            require_once APP_PATH . 'views/moto/detail.php';
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $_SESSION['flash_message'] = "Une erreur est survenue lors de la récupération des détails de la moto";
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