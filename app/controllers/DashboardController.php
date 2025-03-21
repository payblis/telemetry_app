<?php
class DashboardController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function index() {
        // Vérification de l'authentification
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        // Récupération des statistiques
        try {
            // Nombre total de sessions
            $stmt = $this->db->query("SELECT COUNT(*) FROM sessions");
            $totalSessions = $stmt->fetchColumn();

            // Nombre total de pilotes
            $stmt = $this->db->query("SELECT COUNT(*) FROM pilotes");
            $totalPilots = $stmt->fetchColumn();

            // Dernières sessions
            $stmt = $this->db->query("
                SELECT s.*, p.nom as pilot_name, c.nom as circuit_name 
                FROM sessions s 
                JOIN pilotes p ON s.pilote_id = p.id 
                JOIN circuits c ON s.circuit_id = c.id 
                ORDER BY s.date_session DESC 
                LIMIT 5
            ");
            $lastSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            // En cas d'erreur, initialiser des valeurs par défaut
            $totalSessions = 0;
            $totalPilots = 0;
            $lastSessions = [];
        }

        // Affichage de la vue
        $pageTitle = "Tableau de bord - Télémétrie IA";
        require_once APP_PATH . 'views/templates/header.php';
        require_once APP_PATH . 'views/dashboard/index.php';
        require_once APP_PATH . 'views/templates/footer.php';
    }
} 