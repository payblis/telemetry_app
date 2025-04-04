<?php
/**
 * Modèle pour la gestion des pilotes
 */
namespace App\Models;

class PiloteModel extends Model {
    protected $table = 'pilotes';
    
    protected $fillable = [
        'user_id', 'nom', 'prenom', 'taille', 'poids', 'championnat',
        'niveau_experience', 'style_pilotage', 'notes'
    ];
    
    /**
     * Récupérer tous les pilotes d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Liste des pilotes
     */
    public function getAllByUser($userId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY nom, prenom");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer un pilote avec ses statistiques
     * 
     * @param int $id ID du pilote
     * @return array|null Pilote avec statistiques
     */
    public function getWithStats($id) {
        // Récupérer le pilote
        $pilote = $this->find($id);
        
        if (!$pilote) {
            return null;
        }
        
        // Récupérer les statistiques
        $stats = [
            'total_sessions' => $this->countSessions($id),
            'total_tours' => $this->countTours($id),
            'meilleur_tour' => $this->getBestLap($id),
            'circuits_visites' => $this->getVisitedCircuits($id),
            'motos_utilisees' => $this->getUsedMotos($id)
        ];
        
        return array_merge($pilote, ['stats' => $stats]);
    }
    
    /**
     * Compter le nombre de sessions pour un pilote
     * 
     * @param int $piloteId ID du pilote
     * @return int Nombre de sessions
     */
    public function countSessions($piloteId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM sessions WHERE pilote_id = :pilote_id");
        $stmt->execute(['pilote_id' => $piloteId]);
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Compter le nombre de tours pour un pilote
     * 
     * @param int $piloteId ID du pilote
     * @return int Nombre de tours
     */
    public function countTours($piloteId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM tours t
            JOIN sessions s ON t.session_id = s.id
            WHERE s.pilote_id = :pilote_id
        ");
        $stmt->execute(['pilote_id' => $piloteId]);
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Obtenir le meilleur tour d'un pilote
     * 
     * @param int $piloteId ID du pilote
     * @return array|null Meilleur tour
     */
    public function getBestLap($piloteId) {
        $stmt = $this->db->prepare("
            SELECT t.*, s.circuit_id, c.nom as circuit_nom
            FROM tours t
            JOIN sessions s ON t.session_id = s.id
            JOIN circuits c ON s.circuit_id = c.id
            WHERE s.pilote_id = :pilote_id AND t.valide = 1
            ORDER BY t.temps ASC
            LIMIT 1
        ");
        $stmt->execute(['pilote_id' => $piloteId]);
        return $stmt->fetch();
    }
    
    /**
     * Obtenir les circuits visités par un pilote
     * 
     * @param int $piloteId ID du pilote
     * @return array Circuits visités
     */
    public function getVisitedCircuits($piloteId) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT c.id, c.nom, c.pays, COUNT(s.id) as sessions_count
            FROM circuits c
            JOIN sessions s ON c.id = s.circuit_id
            WHERE s.pilote_id = :pilote_id
            GROUP BY c.id
            ORDER BY sessions_count DESC
        ");
        $stmt->execute(['pilote_id' => $piloteId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtenir les motos utilisées par un pilote
     * 
     * @param int $piloteId ID du pilote
     * @return array Motos utilisées
     */
    public function getUsedMotos($piloteId) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT m.id, m.marque, m.modele, COUNT(s.id) as sessions_count
            FROM motos m
            JOIN sessions s ON m.id = s.moto_id
            WHERE s.pilote_id = :pilote_id
            GROUP BY m.id
            ORDER BY sessions_count DESC
        ");
        $stmt->execute(['pilote_id' => $piloteId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtenir les sessions récentes d'un pilote
     * 
     * @param int $piloteId ID du pilote
     * @param int $limit Nombre de sessions à récupérer
     * @return array Sessions récentes
     */
    public function getRecentSessions($piloteId, $limit = 5) {
        $stmt = $this->db->prepare("
            SELECT s.*, c.nom as circuit_nom, m.marque, m.modele
            FROM sessions s
            JOIN circuits c ON s.circuit_id = c.id
            JOIN motos m ON s.moto_id = m.id
            WHERE s.pilote_id = :pilote_id
            ORDER BY s.date_session DESC, s.heure_debut DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':pilote_id', $piloteId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtenir les performances d'un pilote par circuit
     * 
     * @param int $piloteId ID du pilote
     * @return array Performances par circuit
     */
    public function getPerformancesByCircuit($piloteId) {
        $stmt = $this->db->prepare("
            SELECT 
                c.id, c.nom as circuit_nom, c.pays,
                MIN(t.temps) as meilleur_temps,
                COUNT(DISTINCT s.id) as sessions_count,
                COUNT(t.id) as tours_count,
                MAX(s.date_session) as derniere_visite
            FROM circuits c
            JOIN sessions s ON c.id = s.circuit_id
            JOIN tours t ON s.id = t.session_id
            WHERE s.pilote_id = :pilote_id AND t.valide = 1
            GROUP BY c.id
            ORDER BY meilleur_temps ASC
        ");
        $stmt->execute(['pilote_id' => $piloteId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Vérifier si un pilote appartient à un utilisateur
     * 
     * @param int $piloteId ID du pilote
     * @param int $userId ID de l'utilisateur
     * @return bool Le pilote appartient à l'utilisateur ou non
     */
    public function belongsToUser($piloteId, $userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE id = :id AND user_id = :user_id");
        $stmt->execute([
            'id' => $piloteId,
            'user_id' => $userId
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
