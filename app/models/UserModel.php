<?php
/**
 * Modèle pour la gestion des utilisateurs
 */
namespace App\Models;

class UserModel extends Model {
    protected $table = 'users';
    
    protected $fillable = [
        'username', 'email', 'password', 'role', 'active', 
        'consent_community', 'consent_data_collection', 'api_usage_limit'
    ];
    
    /**
     * Créer un nouvel utilisateur
     * 
     * @param array $data Données de l'utilisateur
     * @return int|bool ID de l'utilisateur créé ou false
     */
    public function create(array $data) {
        // Hasher le mot de passe
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        return parent::create($data);
    }
    
    /**
     * Mettre à jour un utilisateur
     * 
     * @param int $id ID de l'utilisateur
     * @param array $data Données à mettre à jour
     * @return bool Succès de l'opération
     */
    public function update($id, array $data) {
        // Hasher le mot de passe si présent
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            // Ne pas mettre à jour le mot de passe s'il est vide
            unset($data['password']);
        }
        
        return parent::update($id, $data);
    }
    
    /**
     * Trouver un utilisateur par son nom d'utilisateur
     * 
     * @param string $username Nom d'utilisateur
     * @return array|null Utilisateur trouvé ou null
     */
    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE username = :username");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch();
    }
    
    /**
     * Trouver un utilisateur par son email
     * 
     * @param string $email Email
     * @return array|null Utilisateur trouvé ou null
     */
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }
    
    /**
     * Vérifier les identifiants d'un utilisateur
     * 
     * @param string $username Nom d'utilisateur ou email
     * @param string $password Mot de passe
     * @return array|bool Utilisateur si authentifié, false sinon
     */
    public function authenticate($username, $password) {
        // Vérifier si c'est un email ou un nom d'utilisateur
        $field = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        // Trouver l'utilisateur
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$field} = :username AND active = 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        
        // Vérifier le mot de passe
        if ($user && password_verify($password, $user['password'])) {
            // Mettre à jour la dernière connexion
            $this->updateLastLogin($user['id']);
            return $user;
        }
        
        return false;
    }
    
    /**
     * Mettre à jour la dernière connexion d'un utilisateur
     * 
     * @param int $id ID de l'utilisateur
     * @return bool Succès de l'opération
     */
    public function updateLastLogin($id) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET last_login = NOW() WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Vérifier si un nom d'utilisateur existe déjà
     * 
     * @param string $username Nom d'utilisateur
     * @param int $excludeId ID à exclure (pour les mises à jour)
     * @return bool Le nom d'utilisateur existe ou non
     */
    public function usernameExists($username, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE username = :username";
        $params = ['username' => $username];
        
        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }
    
    /**
     * Vérifier si un email existe déjà
     * 
     * @param string $email Email
     * @param int $excludeId ID à exclure (pour les mises à jour)
     * @return bool L'email existe ou non
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE email = :email";
        $params = ['email' => $email];
        
        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }
    
    /**
     * Créer un jeton de réinitialisation de mot de passe
     * 
     * @param string $email Email de l'utilisateur
     * @return string|bool Jeton créé ou false
     */
    public function createPasswordResetToken($email) {
        // Vérifier si l'email existe
        if (!$this->emailExists($email)) {
            return false;
        }
        
        // Générer un jeton
        $token = bin2hex(random_bytes(32));
        
        // Date d'expiration (24 heures)
        $expires = date('Y-m-d H:i:s', time() + 86400);
        
        // Supprimer les jetons existants pour cet email
        $stmt = $this->db->prepare("DELETE FROM password_resets WHERE email = :email");
        $stmt->execute(['email' => $email]);
        
        // Insérer le nouveau jeton
        $stmt = $this->db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires)");
        $result = $stmt->execute([
            'email' => $email,
            'token' => $token,
            'expires' => $expires
        ]);
        
        return $result ? $token : false;
    }
    
    /**
     * Vérifier un jeton de réinitialisation de mot de passe
     * 
     * @param string $token Jeton à vérifier
     * @return string|bool Email associé au jeton ou false
     */
    public function verifyPasswordResetToken($token) {
        $stmt = $this->db->prepare("SELECT email FROM password_resets WHERE token = :token AND expires_at > NOW() AND used = 0");
        $stmt->execute(['token' => $token]);
        $result = $stmt->fetch();
        
        return $result ? $result['email'] : false;
    }
    
    /**
     * Réinitialiser le mot de passe d'un utilisateur
     * 
     * @param string $token Jeton de réinitialisation
     * @param string $password Nouveau mot de passe
     * @return bool Succès de l'opération
     */
    public function resetPassword($token, $password) {
        // Vérifier le jeton
        $email = $this->verifyPasswordResetToken($token);
        
        if (!$email) {
            return false;
        }
        
        // Trouver l'utilisateur
        $user = $this->findByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        // Mettre à jour le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE {$this->table} SET password = :password WHERE id = :id");
        $result = $stmt->execute([
            'password' => $hashedPassword,
            'id' => $user['id']
        ]);
        
        if ($result) {
            // Marquer le jeton comme utilisé
            $stmt = $this->db->prepare("UPDATE password_resets SET used = 1 WHERE token = :token");
            $stmt->execute(['token' => $token]);
        }
        
        return $result;
    }
    
    /**
     * Obtenir les préférences d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Préférences de l'utilisateur
     */
    public function getPreferences($userId) {
        $stmt = $this->db->prepare("SELECT * FROM user_preferences WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch();
    }
    
    /**
     * Mettre à jour les préférences d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param array $preferences Préférences à mettre à jour
     * @return bool Succès de l'opération
     */
    public function updatePreferences($userId, $preferences) {
        // Vérifier si les préférences existent déjà
        $stmt = $this->db->prepare("SELECT id FROM user_preferences WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch();
        
        if ($result) {
            // Mettre à jour les préférences existantes
            $setClause = [];
            foreach (array_keys($preferences) as $key) {
                $setClause[] = "{$key} = :{$key}";
            }
            $setClause = implode(', ', $setClause);
            
            $sql = "UPDATE user_preferences SET {$setClause} WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            
            $preferences['user_id'] = $userId;
            return $stmt->execute($preferences);
        } else {
            // Créer de nouvelles préférences
            $preferences['user_id'] = $userId;
            
            $columns = implode(', ', array_keys($preferences));
            $placeholders = ':' . implode(', :', array_keys($preferences));
            
            $sql = "INSERT INTO user_preferences ({$columns}) VALUES ({$placeholders})";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute($preferences);
        }
    }
    
    /**
     * Obtenir les appareils d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Appareils de l'utilisateur
     */
    public function getDevices($userId) {
        $stmt = $this->db->prepare("SELECT * FROM user_devices WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Ajouter un appareil pour un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param array $deviceData Données de l'appareil
     * @return int|bool ID de l'appareil créé ou false
     */
    public function addDevice($userId, $deviceData) {
        $deviceData['user_id'] = $userId;
        
        $columns = implode(', ', array_keys($deviceData));
        $placeholders = ':' . implode(', :', array_keys($deviceData));
        
        $sql = "INSERT INTO user_devices ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute($deviceData)) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Supprimer un appareil
     * 
     * @param int $deviceId ID de l'appareil
     * @param int $userId ID de l'utilisateur (pour vérification)
     * @return bool Succès de l'opération
     */
    public function deleteDevice($deviceId, $userId) {
        $stmt = $this->db->prepare("DELETE FROM user_devices WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([
            'id' => $deviceId,
            'user_id' => $userId
        ]);
    }
    
    /**
     * Mettre à jour l'utilisation de l'API pour un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return bool Succès de l'opération
     */
    public function incrementApiUsage($userId) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET api_usage_count = api_usage_count + 1 WHERE id = :id");
        return $stmt->execute(['id' => $userId]);
    }
    
    /**
     * Vérifier si un utilisateur a atteint sa limite d'utilisation de l'API
     * 
     * @param int $userId ID de l'utilisateur
     * @return bool L'utilisateur a atteint sa limite ou non
     */
    public function hasReachedApiLimit($userId) {
        $stmt = $this->db->prepare("SELECT api_usage_count, api_usage_limit FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $result = $stmt->fetch();
        
        return $result && $result['api_usage_count'] >= $result['api_usage_limit'];
    }
}
