<?php

class TelemetrieController {
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
            // Récupération des dernières sessions avec données télémétriques
            $stmt = $this->db->query("
                SELECT s.*, p.nom as pilote_nom, p.prenom as pilote_prenom,
                       m.marque as moto_marque, m.modele as moto_modele,
                       c.nom as circuit_nom,
                       COUNT(d.id) as total_donnees
                FROM sessions s
                JOIN pilotes p ON s.pilote_id = p.id
                JOIN motos m ON s.moto_id = m.id
                JOIN circuits c ON s.circuit_id = c.id
                LEFT JOIN donnees_telemetrie d ON s.id = d.session_id
                GROUP BY s.id
                HAVING total_donnees > 0
                ORDER BY s.date_session DESC
                LIMIT 10
            ");
            $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $pageTitle = "Télémétrie";
            require_once 'app/views/telemetrie/index.php';
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = "Erreur lors de la récupération des données télémétriques.";
            $_SESSION['flash_type'] = "danger";
            header('Location: index.php?route=dashboard');
            exit;
        }
    }

    public function import() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->db->beginTransaction();

                // Vérification du fichier
                if (!isset($_FILES['telemetrie_file']) || $_FILES['telemetrie_file']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception("Erreur lors de l'upload du fichier.");
                }

                // Création de la session
                $stmt = $this->db->prepare("
                    INSERT INTO sessions (pilote_id, moto_id, circuit_id, date_session, conditions, temperature, humidite)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $_POST['pilote_id'],
                    $_POST['moto_id'],
                    $_POST['circuit_id'],
                    $_POST['date_session'],
                    $_POST['conditions'],
                    $_POST['temperature'],
                    $_POST['humidite']
                ]);

                $sessionId = $this->db->lastInsertId();

                // Traitement du fichier CSV
                $handle = fopen($_FILES['telemetrie_file']['tmp_name'], "r");
                if ($handle !== FALSE) {
                    // Préparation de la requête d'insertion des données
                    $stmt = $this->db->prepare("
                        INSERT INTO donnees_telemetrie (
                            session_id, timestamp, vitesse, regime_moteur, acceleration,
                            angle_inclinaison, temperature_pneu_avant, temperature_pneu_arriere,
                            pression_pneu_avant, pression_pneu_arriere, force_freinage,
                            position_gps_lat, position_gps_long
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");

                    // Ignorer la première ligne (en-têtes)
                    fgetcsv($handle);

                    // Lecture et insertion des données
                    while (($data = fgetcsv($handle)) !== FALSE) {
                        $stmt->execute([
                            $sessionId,
                            $data[0],  // timestamp
                            $data[1],  // vitesse
                            $data[2],  // regime_moteur
                            $data[3],  // acceleration
                            $data[4],  // angle_inclinaison
                            $data[5],  // temperature_pneu_avant
                            $data[6],  // temperature_pneu_arriere
                            $data[7],  // pression_pneu_avant
                            $data[8],  // pression_pneu_arriere
                            $data[9],  // force_freinage
                            $data[10], // position_gps_lat
                            $data[11]  // position_gps_long
                        ]);
                    }
                    fclose($handle);
                }

                $this->db->commit();

                $_SESSION['flash_message'] = "Données télémétriques importées avec succès.";
                $_SESSION['flash_type'] = "success";
                header('Location: index.php?route=telemetrie/analyse&session_id=' . $sessionId);
                exit;
            } catch (Exception $e) {
                $this->db->rollBack();
                $_SESSION['flash_message'] = "Erreur lors de l'import des données : " . $e->getMessage();
                $_SESSION['flash_type'] = "danger";
            }
        }

        // Récupération des listes pour le formulaire
        try {
            $stmt = $this->db->query("SELECT id, nom, prenom FROM pilotes ORDER BY nom, prenom");
            $pilotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->db->query("SELECT id, marque, modele FROM motos ORDER BY marque, modele");
            $motos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->db->query("SELECT id, nom FROM circuits ORDER BY nom");
            $circuits = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $pilotes = [];
            $motos = [];
            $circuits = [];
        }

        $pageTitle = "Import des données télémétriques";
        require_once 'app/views/telemetrie/import.php';
    }

    public function analyse($sessionId = null) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        try {
            if ($sessionId === null && isset($_GET['session_id'])) {
                $sessionId = $_GET['session_id'];
            }

            if ($sessionId === null) {
                throw new Exception("Session non spécifiée");
            }

            // Récupération des informations de la session
            $stmt = $this->db->prepare("
                SELECT s.*, 
                       p.nom as pilote_nom, p.prenom as pilote_prenom,
                       m.marque as moto_marque, m.modele as moto_modele,
                       c.nom as circuit_nom
                FROM sessions s
                JOIN pilotes p ON s.pilote_id = p.id
                JOIN motos m ON s.moto_id = m.id
                JOIN circuits c ON s.circuit_id = c.id
                WHERE s.id = ?
            ");
            $stmt->execute([$sessionId]);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$session) {
                throw new Exception("Session non trouvée");
            }

            // Récupération des données télémétriques
            $stmt = $this->db->prepare("
                SELECT *
                FROM donnees_telemetrie
                WHERE session_id = ?
                ORDER BY timestamp
            ");
            $stmt->execute([$sessionId]);
            $donnees = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Préparation des données pour les graphiques
            $data = [
                'timestamps' => [],
                'vitesse' => [],
                'regime_moteur' => [],
                'acceleration' => [],
                'angle_inclinaison' => [],
                'temperatures_pneus' => [
                    'avant' => [],
                    'arriere' => []
                ],
                'pressions_pneus' => [
                    'avant' => [],
                    'arriere' => []
                ],
                'force_freinage' => [],
                'positions_gps' => [
                    'lat' => [],
                    'long' => []
                ]
            ];

            foreach ($donnees as $donnee) {
                $data['timestamps'][] = $donnee['timestamp'];
                $data['vitesse'][] = $donnee['vitesse'];
                $data['regime_moteur'][] = $donnee['regime_moteur'];
                $data['acceleration'][] = $donnee['acceleration'];
                $data['angle_inclinaison'][] = $donnee['angle_inclinaison'];
                $data['temperatures_pneus']['avant'][] = $donnee['temperature_pneu_avant'];
                $data['temperatures_pneus']['arriere'][] = $donnee['temperature_pneu_arriere'];
                $data['pressions_pneus']['avant'][] = $donnee['pression_pneu_avant'];
                $data['pressions_pneus']['arriere'][] = $donnee['pression_pneu_arriere'];
                $data['force_freinage'][] = $donnee['force_freinage'];
                $data['positions_gps']['lat'][] = $donnee['position_gps_lat'];
                $data['positions_gps']['long'][] = $donnee['position_gps_long'];
            }

            $pageTitle = "Analyse télémétrique";
            require_once 'app/views/telemetrie/analyse.php';
        } catch (Exception $e) {
            $_SESSION['flash_message'] = "Erreur lors de l'analyse des données : " . $e->getMessage();
            $_SESSION['flash_type'] = "danger";
            header('Location: index.php?route=telemetrie');
            exit;
        }
    }

    public function historique() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        try {
            // Filtres
            $where = [];
            $params = [];

            if (!empty($_GET['pilote_id'])) {
                $where[] = "s.pilote_id = ?";
                $params[] = $_GET['pilote_id'];
            }

            if (!empty($_GET['moto_id'])) {
                $where[] = "s.moto_id = ?";
                $params[] = $_GET['moto_id'];
            }

            if (!empty($_GET['circuit_id'])) {
                $where[] = "s.circuit_id = ?";
                $params[] = $_GET['circuit_id'];
            }

            if (!empty($_GET['date_debut'])) {
                $where[] = "s.date_session >= ?";
                $params[] = $_GET['date_debut'];
            }

            if (!empty($_GET['date_fin'])) {
                $where[] = "s.date_session <= ?";
                $params[] = $_GET['date_fin'];
            }

            $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

            // Récupération des sessions
            $query = "
                SELECT s.*, 
                       p.nom as pilote_nom, p.prenom as pilote_prenom,
                       m.marque as moto_marque, m.modele as moto_modele,
                       c.nom as circuit_nom,
                       COUNT(d.id) as total_donnees
                FROM sessions s
                JOIN pilotes p ON s.pilote_id = p.id
                JOIN motos m ON s.moto_id = m.id
                JOIN circuits c ON s.circuit_id = c.id
                LEFT JOIN donnees_telemetrie d ON s.id = d.session_id
                $whereClause
                GROUP BY s.id
                HAVING total_donnees > 0
                ORDER BY s.date_session DESC
            ";

            $stmt = !empty($params) ? $this->db->prepare($query) : $this->db->query($query);
            
            if (!empty($params)) {
                $stmt->execute($params);
            }
            
            $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Récupération des listes pour les filtres
            $stmt = $this->db->query("SELECT id, nom, prenom FROM pilotes ORDER BY nom, prenom");
            $pilotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->db->query("SELECT id, marque, modele FROM motos ORDER BY marque, modele");
            $motos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->db->query("SELECT id, nom FROM circuits ORDER BY nom");
            $circuits = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $pageTitle = "Historique des sessions";
            require_once 'app/views/telemetrie/historique.php';
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = "Erreur lors de la récupération de l'historique.";
            $_SESSION['flash_type'] = "danger";
            header('Location: index.php?route=dashboard');
            exit;
        }
    }
} 