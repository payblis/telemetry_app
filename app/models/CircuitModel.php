<?php
/**
 * Modèle pour la gestion des circuits
 */
namespace App\Models;

class CircuitModel extends Model {
    protected $table = 'circuits';
    
    protected $fillable = [
        'nom', 'pays', 'ville', 'longueur', 'largeur', 'nombre_virages',
        'altitude', 'coordonnees_gps', 'description', 'image_path', 'created_by', 'source'
    ];
    
    /**
     * Récupérer tous les circuits
     * 
     * @param string $orderBy Colonne pour le tri
     * @param string $order Direction du tri (ASC ou DESC)
     * @return array Liste des circuits
     */
    public function getAll($orderBy = 'nom', $order = 'ASC') {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY {$orderBy} {$order}");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer les circuits créés par un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Liste des circuits
     */
    public function getByUser($userId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE created_by = :user_id ORDER BY nom");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer un circuit avec ses virages et secteurs
     * 
     * @param int $id ID du circuit
     * @return array|null Circuit avec virages et secteurs
     */
    public function getWithDetails($id) {
        // Récupérer le circuit
        $circuit = $this->find($id);
        
        if (!$circuit) {
            return null;
        }
        
        // Récupérer les virages
        $stmt = $this->db->prepare("
            SELECT * FROM virages_circuit 
            WHERE circuit_id = :circuit_id 
            ORDER BY numero_virage
        ");
        $stmt->execute(['circuit_id' => $id]);
        $virages = $stmt->fetchAll();
        
        // Récupérer les secteurs
        $stmt = $this->db->prepare("
            SELECT * FROM secteurs_circuit 
            WHERE circuit_id = :circuit_id 
            ORDER BY numero_debut
        ");
        $stmt->execute(['circuit_id' => $id]);
        $secteurs = $stmt->fetchAll();
        
        // Ajouter les virages et secteurs au circuit
        $circuit['virages'] = $virages;
        $circuit['secteurs'] = $secteurs;
        
        return $circuit;
    }
    
    /**
     * Récupérer les statistiques d'un circuit
     * 
     * @param int $circuitId ID du circuit
     * @return array Statistiques du circuit
     */
    public function getStats($circuitId) {
        $stats = [
            'total_sessions' => $this->countSessions($circuitId),
            'total_pilotes' => $this->countPilotes($circuitId),
            'total_motos' => $this->countMotos($circuitId),
            'meilleur_tour' => $this->getBestLap($circuitId),
            'derniere_session' => $this->getLastSession($circuitId)
        ];
        
        return $stats;
    }
    
    /**
     * Compter le nombre de sessions sur un circuit
     * 
     * @param int $circuitId ID du circuit
     * @return int Nombre de sessions
     */
    public function countSessions($circuitId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM sessions WHERE circuit_id = :circuit_id");
        $stmt->execute(['circuit_id' => $circuitId]);
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Compter le nombre de pilotes différents sur un circuit
     * 
     * @param int $circuitId ID du circuit
     * @return int Nombre de pilotes
     */
    public function countPilotes($circuitId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT pilote_id) 
            FROM sessions 
            WHERE circuit_id = :circuit_id
        ");
        $stmt->execute(['circuit_id' => $circuitId]);
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Compter le nombre de motos différentes sur un circuit
     * 
     * @param int $circuitId ID du circuit
     * @return int Nombre de motos
     */
    public function countMotos($circuitId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT moto_id) 
            FROM sessions 
            WHERE circuit_id = :circuit_id
        ");
        $stmt->execute(['circuit_id' => $circuitId]);
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Obtenir le meilleur tour sur un circuit
     * 
     * @param int $circuitId ID du circuit
     * @return array|null Meilleur tour
     */
    public function getBestLap($circuitId) {
        $stmt = $this->db->prepare("
            SELECT t.*, s.date_session, p.nom as pilote_nom, p.prenom as pilote_prenom, m.marque, m.modele
            FROM tours t
            JOIN sessions s ON t.session_id = s.id
            JOIN pilotes p ON s.pilote_id = p.id
            JOIN motos m ON s.moto_id = m.id
            WHERE s.circuit_id = :circuit_id AND t.valide = 1
            ORDER BY t.temps ASC
            LIMIT 1
        ");
        $stmt->execute(['circuit_id' => $circuitId]);
        return $stmt->fetch();
    }
    
    /**
     * Obtenir la dernière session sur un circuit
     * 
     * @param int $circuitId ID du circuit
     * @return array|null Dernière session
     */
    public function getLastSession($circuitId) {
        $stmt = $this->db->prepare("
            SELECT s.*, p.nom as pilote_nom, p.prenom as pilote_prenom, m.marque, m.modele
            FROM sessions s
            JOIN pilotes p ON s.pilote_id = p.id
            JOIN motos m ON s.moto_id = m.id
            WHERE s.circuit_id = :circuit_id
            ORDER BY s.date_session DESC, s.heure_debut DESC
            LIMIT 1
        ");
        $stmt->execute(['circuit_id' => $circuitId]);
        return $stmt->fetch();
    }
    
    /**
     * Obtenir les meilleurs temps par secteur
     * 
     * @param int $circuitId ID du circuit
     * @return array Meilleurs temps par secteur
     */
    public function getBestSectorTimes($circuitId) {
        $stmt = $this->db->prepare("
            SELECT sc.id as secteur_id, sc.nom as secteur_nom, sc.numero_debut, sc.numero_fin,
                   MIN(st.temps) as meilleur_temps, st.tour_id,
                   t.session_id, s.date_session, p.nom as pilote_nom, p.prenom as pilote_prenom, 
                   m.marque, m.modele
            FROM secteurs_circuit sc
            JOIN secteurs_temps st ON sc.id = st.secteur_id
            JOIN tours t ON st.tour_id = t.id
            JOIN sessions s ON t.session_id = s.id
            JOIN pilotes p ON s.pilote_id = p.id
            JOIN motos m ON s.moto_id = m.id
            WHERE sc.circuit_id = :circuit_id AND t.valide = 1
            GROUP BY sc.id
            ORDER BY sc.numero_debut
        ");
        $stmt->execute(['circuit_id' => $circuitId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtenir les records par pilote sur un circuit
     * 
     * @param int $circuitId ID du circuit
     * @param int $limit Nombre de records à récupérer
     * @return array Records par pilote
     */
    public function getRecordsByPilote($circuitId, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT p.id as pilote_id, p.nom as pilote_nom, p.prenom as pilote_prenom,
                   MIN(t.temps) as meilleur_temps, t.id as tour_id, t.session_id,
                   s.date_session, m.marque, m.modele
            FROM pilotes p
            JOIN sessions s ON p.id = s.pilote_id
            JOIN tours t ON s.id = t.session_id
            JOIN motos m ON s.moto_id = m.id
            WHERE s.circuit_id = :circuit_id AND t.valide = 1
            GROUP BY p.id
            ORDER BY meilleur_temps ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':circuit_id', $circuitId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Ajouter un virage à un circuit
     * 
     * @param int $circuitId ID du circuit
     * @param array $virageData Données du virage
     * @return int|bool ID du virage créé ou false
     */
    public function addVirage($circuitId, $virageData) {
        $virageData['circuit_id'] = $circuitId;
        
        $columns = implode(', ', array_keys($virageData));
        $placeholders = ':' . implode(', :', array_keys($virageData));
        
        $sql = "INSERT INTO virages_circuit ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute($virageData)) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Mettre à jour un virage
     * 
     * @param int $virageId ID du virage
     * @param array $virageData Données du virage
     * @return bool Succès de l'opération
     */
    public function updateVirage($virageId, $virageData) {
        $setClause = [];
        foreach (array_keys($virageData) as $column) {
            $setClause[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setClause);
        
        $sql = "UPDATE virages_circuit SET {$setClause} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        $virageData['id'] = $virageId;
        return $stmt->execute($virageData);
    }
    
    /**
     * Supprimer un virage
     * 
     * @param int $virageId ID du virage
     * @return bool Succès de l'opération
     */
    public function deleteVirage($virageId) {
        $sql = "DELETE FROM virages_circuit WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $virageId]);
    }
    
    /**
     * Ajouter un secteur à un circuit
     * 
     * @param int $circuitId ID du circuit
     * @param array $secteurData Données du secteur
     * @return int|bool ID du secteur créé ou false
     */
    public function addSecteur($circuitId, $secteurData) {
        $secteurData['circuit_id'] = $circuitId;
        
        $columns = implode(', ', array_keys($secteurData));
        $placeholders = ':' . implode(', :', array_keys($secteurData));
        
        $sql = "INSERT INTO secteurs_circuit ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute($secteurData)) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Mettre à jour un secteur
     * 
     * @param int $secteurId ID du secteur
     * @param array $secteurData Données du secteur
     * @return bool Succès de l'opération
     */
    public function updateSecteur($secteurId, $secteurData) {
        $setClause = [];
        foreach (array_keys($secteurData) as $column) {
            $setClause[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setClause);
        
        $sql = "UPDATE secteurs_circuit SET {$setClause} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        $secteurData['id'] = $secteurId;
        return $stmt->execute($secteurData);
    }
    
    /**
     * Supprimer un secteur
     * 
     * @param int $secteurId ID du secteur
     * @return bool Succès de l'opération
     */
    public function deleteSecteur($secteurId) {
        $sql = "DELETE FROM secteurs_circuit WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $secteurId]);
    }
    
    /**
     * Rechercher des circuits par nom ou pays
     * 
     * @param string $query Terme de recherche
     * @return array Résultats de la recherche
     */
    public function search($query) {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE nom LIKE :query OR pays LIKE :query OR ville LIKE :query
            ORDER BY nom
        ");
        $stmt->execute(['query' => "%{$query}%"]);
        return $stmt->fetchAll();
    }
    
    /**
     * Vérifier si un circuit appartient à un utilisateur
     * 
     * @param int $circuitId ID du circuit
     * @param int $userId ID de l'utilisateur
     * @return bool Le circuit appartient à l'utilisateur ou non
     */
    public function belongsToUser($circuitId, $userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE id = :id AND created_by = :user_id");
        $stmt->execute([
            'id' => $circuitId,
            'user_id' => $userId
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
