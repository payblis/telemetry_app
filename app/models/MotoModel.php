<?php
/**
 * Modèle pour la gestion des motos
 */
namespace App\Models;

class MotoModel extends Model {
    protected $table = 'motos';
    
    protected $fillable = [
        'user_id', 'marque', 'modele', 'annee', 'cylindree', 
        'poids_sec', 'type_moteur', 'type_cadre', 'image_path', 'notes'
    ];
    
    /**
     * Récupérer toutes les motos d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Liste des motos
     */
    public function getAllByUser($userId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY marque, modele");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer une moto avec ses réglages
     * 
     * @param int $id ID de la moto
     * @return array|null Moto avec réglages
     */
    public function getWithSettings($id) {
        // Récupérer la moto
        $moto = $this->find($id);
        
        if (!$moto) {
            return null;
        }
        
        // Récupérer les réglages
        $stmt = $this->db->prepare("
            SELECT * FROM reglages_moto 
            WHERE moto_id = :moto_id 
            ORDER BY type_reglage, nom_reglage
        ");
        $stmt->execute(['moto_id' => $id]);
        $reglages = $stmt->fetchAll();
        
        // Récupérer les configurations
        $stmt = $this->db->prepare("
            SELECT * FROM configurations_moto 
            WHERE moto_id = :moto_id 
            ORDER BY est_configuration_defaut DESC, nom
        ");
        $stmt->execute(['moto_id' => $id]);
        $configurations = $stmt->fetchAll();
        
        // Pour chaque configuration, récupérer ses réglages
        foreach ($configurations as &$config) {
            $stmt = $this->db->prepare("
                SELECT cr.*, rm.nom_reglage, rm.type_reglage, rm.unite
                FROM configuration_reglages cr
                JOIN reglages_moto rm ON cr.reglage_id = rm.id
                WHERE cr.configuration_id = :config_id
                ORDER BY rm.type_reglage, rm.nom_reglage
            ");
            $stmt->execute(['config_id' => $config['id']]);
            $config['reglages'] = $stmt->fetchAll();
        }
        
        // Ajouter les réglages et configurations à la moto
        $moto['reglages'] = $reglages;
        $moto['configurations'] = $configurations;
        
        return $moto;
    }
    
    /**
     * Récupérer les statistiques d'utilisation d'une moto
     * 
     * @param int $motoId ID de la moto
     * @return array Statistiques d'utilisation
     */
    public function getUsageStats($motoId) {
        $stats = [
            'total_sessions' => $this->countSessions($motoId),
            'total_tours' => $this->countTours($motoId),
            'distance_totale' => $this->getTotalDistance($motoId),
            'circuits_visites' => $this->getVisitedCircuits($motoId),
            'pilotes_utilisateurs' => $this->getUsers($motoId),
            'derniere_utilisation' => $this->getLastUsage($motoId)
        ];
        
        return $stats;
    }
    
    /**
     * Compter le nombre de sessions pour une moto
     * 
     * @param int $motoId ID de la moto
     * @return int Nombre de sessions
     */
    public function countSessions($motoId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM sessions WHERE moto_id = :moto_id");
        $stmt->execute(['moto_id' => $motoId]);
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Compter le nombre de tours pour une moto
     * 
     * @param int $motoId ID de la moto
     * @return int Nombre de tours
     */
    public function countTours($motoId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM tours t
            JOIN sessions s ON t.session_id = s.id
            WHERE s.moto_id = :moto_id
        ");
        $stmt->execute(['moto_id' => $motoId]);
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Obtenir la distance totale parcourue par une moto
     * 
     * @param int $motoId ID de la moto
     * @return float Distance totale en km
     */
    public function getTotalDistance($motoId) {
        $stmt = $this->db->prepare("
            SELECT SUM(ta.distance_parcourue) 
            FROM telemetrie_agregee ta
            JOIN tours t ON ta.tour_id = t.id
            JOIN sessions s ON t.session_id = s.id
            WHERE s.moto_id = :moto_id
        ");
        $stmt->execute(['moto_id' => $motoId]);
        $distance = $stmt->fetchColumn();
        
        return $distance ? round($distance / 1000, 2) : 0; // Convertir en km
    }
    
    /**
     * Obtenir les circuits visités avec une moto
     * 
     * @param int $motoId ID de la moto
     * @return array Circuits visités
     */
    public function getVisitedCircuits($motoId) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT c.id, c.nom, c.pays, COUNT(s.id) as sessions_count
            FROM circuits c
            JOIN sessions s ON c.id = s.circuit_id
            WHERE s.moto_id = :moto_id
            GROUP BY c.id
            ORDER BY sessions_count DESC
        ");
        $stmt->execute(['moto_id' => $motoId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtenir les pilotes qui ont utilisé une moto
     * 
     * @param int $motoId ID de la moto
     * @return array Pilotes utilisateurs
     */
    public function getUsers($motoId) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT p.id, p.nom, p.prenom, COUNT(s.id) as sessions_count
            FROM pilotes p
            JOIN sessions s ON p.id = s.pilote_id
            WHERE s.moto_id = :moto_id
            GROUP BY p.id
            ORDER BY sessions_count DESC
        ");
        $stmt->execute(['moto_id' => $motoId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtenir la dernière utilisation d'une moto
     * 
     * @param int $motoId ID de la moto
     * @return array|null Dernière session
     */
    public function getLastUsage($motoId) {
        $stmt = $this->db->prepare("
            SELECT s.*, c.nom as circuit_nom, p.nom as pilote_nom, p.prenom as pilote_prenom
            FROM sessions s
            JOIN circuits c ON s.circuit_id = c.id
            JOIN pilotes p ON s.pilote_id = p.id
            WHERE s.moto_id = :moto_id
            ORDER BY s.date_session DESC, s.heure_debut DESC
            LIMIT 1
        ");
        $stmt->execute(['moto_id' => $motoId]);
        return $stmt->fetch();
    }
    
    /**
     * Ajouter un réglage à une moto
     * 
     * @param int $motoId ID de la moto
     * @param array $reglageData Données du réglage
     * @return int|bool ID du réglage créé ou false
     */
    public function addReglage($motoId, $reglageData) {
        $reglageData['moto_id'] = $motoId;
        
        $columns = implode(', ', array_keys($reglageData));
        $placeholders = ':' . implode(', :', array_keys($reglageData));
        
        $sql = "INSERT INTO reglages_moto ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute($reglageData)) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Mettre à jour un réglage
     * 
     * @param int $reglageId ID du réglage
     * @param array $reglageData Données du réglage
     * @return bool Succès de l'opération
     */
    public function updateReglage($reglageId, $reglageData) {
        $setClause = [];
        foreach (array_keys($reglageData) as $column) {
            $setClause[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setClause);
        
        $sql = "UPDATE reglages_moto SET {$setClause} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        $reglageData['id'] = $reglageId;
        return $stmt->execute($reglageData);
    }
    
    /**
     * Supprimer un réglage
     * 
     * @param int $reglageId ID du réglage
     * @return bool Succès de l'opération
     */
    public function deleteReglage($reglageId) {
        $sql = "DELETE FROM reglages_moto WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $reglageId]);
    }
    
    /**
     * Créer une configuration pour une moto
     * 
     * @param int $motoId ID de la moto
     * @param array $configData Données de la configuration
     * @param array $reglages Réglages de la configuration
     * @return int|bool ID de la configuration créée ou false
     */
    public function createConfiguration($motoId, $configData, $reglages = []) {
        try {
            // Démarrer une transaction
            $this->db->beginTransaction();
            
            // Créer la configuration
            $configData['moto_id'] = $motoId;
            
            $columns = implode(', ', array_keys($configData));
            $placeholders = ':' . implode(', :', array_keys($configData));
            
            $sql = "INSERT INTO configurations_moto ({$columns}) VALUES ({$placeholders})";
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt->execute($configData)) {
                throw new \Exception("Erreur lors de la création de la configuration");
            }
            
            $configId = $this->db->lastInsertId();
            
            // Ajouter les réglages à la configuration
            if (!empty($reglages)) {
                foreach ($reglages as $reglageId => $valeur) {
                    $sql = "INSERT INTO configuration_reglages (configuration_id, reglage_id, valeur) VALUES (:config_id, :reglage_id, :valeur)";
                    $stmt = $this->db->prepare($sql);
                    
                    if (!$stmt->execute([
                        'config_id' => $configId,
                        'reglage_id' => $reglageId,
                        'valeur' => $valeur
                    ])) {
                        throw new \Exception("Erreur lors de l'ajout des réglages à la configuration");
                    }
                }
            }
            
            // Valider la transaction
            $this->db->commit();
            
            return $configId;
        } catch (\Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->rollback();
            return false;
        }
    }
    
    /**
     * Mettre à jour une configuration
     * 
     * @param int $configId ID de la configuration
     * @param array $configData Données de la configuration
     * @param array $reglages Réglages de la configuration
     * @return bool Succès de l'opération
     */
    public function updateConfiguration($configId, $configData, $reglages = []) {
        try {
            // Démarrer une transaction
            $this->db->beginTransaction();
            
            // Mettre à jour la configuration
            $setClause = [];
            foreach (array_keys($configData) as $column) {
                $setClause[] = "{$column} = :{$column}";
            }
            $setClause = implode(', ', $setClause);
            
            $sql = "UPDATE configurations_moto SET {$setClause} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            $configData['id'] = $configId;
            
            if (!$stmt->execute($configData)) {
                throw new \Exception("Erreur lors de la mise à jour de la configuration");
            }
            
            // Supprimer les réglages existants
            $sql = "DELETE FROM configuration_reglages WHERE configuration_id = :config_id";
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt->execute(['config_id' => $configId])) {
                throw new \Exception("Erreur lors de la suppression des réglages existants");
            }
            
            // Ajouter les nouveaux réglages
            if (!empty($reglages)) {
                foreach ($reglages as $reglageId => $valeur) {
                    $sql = "INSERT INTO configuration_reglages (configuration_id, reglage_id, valeur) VALUES (:config_id, :reglage_id, :valeur)";
                    $stmt = $this->db->prepare($sql);
                    
                    if (!$stmt->execute([
                        'config_id' => $configId,
                        'reglage_id' => $reglageId,
                        'valeur' => $valeur
                    ])) {
                        throw new \Exception("Erreur lors de l'ajout des réglages à la configuration");
                    }
                }
            }
            
            // Valider la transaction
            $this->db->commit();
            
            return true;
        } catch (\Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->rollback();
            return false;
        }
    }
    
    /**
     * Supprimer une configuration
     * 
     * @param int $configId ID de la configuration
     * @return bool Succès de l'opération
     */
    public function deleteConfiguration($configId) {
        try {
            // Démarrer une transaction
            $this->db->beginTransaction();
            
            // Supprimer les réglages de la configuration
            $sql = "DELETE FROM configuration_reglages WHERE configuration_id = :config_id";
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt->execute(['config_id' => $configId])) {
                throw new \Exception("Erreur lors de la suppression des réglages de la configuration");
            }
            
            // Supprimer la configuration
            $sql = "DELETE FROM configurations_moto WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt->execute(['id' => $configId])) {
                throw new \Exception("Erreur lors de la suppression de la configuration");
            }
            
            // Valider la transaction
            $this->db->commit();
            
            return true;
        } catch (\Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->rollback();
            return false;
        }
    }
    
    /**
     * Vérifier si une moto appartient à un utilisateur
     * 
     * @param int $motoId ID de la moto
     * @param int $userId ID de l'utilisateur
     * @return bool La moto appartient à l'utilisateur ou non
     */
    public function belongsToUser($motoId, $userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE id = :id AND user_id = :user_id");
        $stmt->execute([
            'id' => $motoId,
            'user_id' => $userId
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
