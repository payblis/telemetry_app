<?php
/**
 * Classe User
 * Gère les utilisateurs de l'application
 */
class User {
    private $db;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Authentifier un utilisateur
     * @param string $email Email de l'utilisateur
     * @param string $password Mot de passe
     * @return array|false Données de l'utilisateur ou false
     */
    public function authenticate($email, $password) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $user = $this->db->fetchOne($sql, [$email]);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Créer un nouvel utilisateur
     * @param string $username Nom d'utilisateur
     * @param string $email Email
     * @param string $password Mot de passe
     * @param string $role Rôle (ADMIN, USER, EXPERT)
     * @param string $telemetricianName Nom du télémétriste
     * @return int ID de l'utilisateur créé
     */
    public function create($username, $email, $password, $role = 'USER', $telemetricianName = 'Télémétriste') {
        // Vérifier si l'email existe déjà
        $sql = "SELECT id FROM users WHERE email = ?";
        $existingUser = $this->db->fetchOne($sql, [$email]);
        
        if ($existingUser) {
            throw new Exception("Un utilisateur avec cet email existe déjà.");
        }
        
        // Vérifier si le nom d'utilisateur existe déjà
        $sql = "SELECT id FROM users WHERE username = ?";
        $existingUser = $this->db->fetchOne($sql, [$username]);
        
        if ($existingUser) {
            throw new Exception("Ce nom d'utilisateur est déjà pris.");
        }
        
        // Hacher le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insérer l'utilisateur
        $data = [
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
            'role' => $role,
            'telemetrician_name' => $telemetricianName
        ];
        
        return $this->db->insert('users', $data);
    }
    
    /**
     * Obtenir un utilisateur par son ID
     * @param int $id ID de l'utilisateur
     * @return array|false Données de l'utilisateur ou false
     */
    public function getById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Mettre à jour un utilisateur
     * @param int $id ID de l'utilisateur
     * @param array $data Données à mettre à jour
     * @return int Nombre de lignes affectées
     */
    public function update($id, $data) {
        // Ne pas permettre la mise à jour du mot de passe via cette méthode
        if (isset($data['password'])) {
            unset($data['password']);
        }
        
        return $this->db->update('users', $data, 'id = ?', [$id]);
    }
    
    /**
     * Changer le mot de passe d'un utilisateur
     * @param int $id ID de l'utilisateur
     * @param string $newPassword Nouveau mot de passe
     * @return int Nombre de lignes affectées
     */
    public function changePassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $data = [
            'password' => $hashedPassword
        ];
        
        return $this->db->update('users', $data, 'id = ?', [$id]);
    }
    
    /**
     * Supprimer un utilisateur
     * @param int $id ID de l'utilisateur
     * @return int Nombre de lignes affectées
     */
    public function delete($id) {
        return $this->db->delete('users', 'id = ?', [$id]);
    }
    
    /**
     * Obtenir tous les utilisateurs
     * @param string $role Filtrer par rôle (optionnel)
     * @return array Liste des utilisateurs
     */
    public function getAll($role = null) {
        if ($role) {
            $sql = "SELECT * FROM users WHERE role = ? ORDER BY username";
            return $this->db->fetchAll($sql, [$role]);
        } else {
            $sql = "SELECT * FROM users ORDER BY username";
            return $this->db->fetchAll($sql);
        }
    }
    
    /**
     * Vérifier si un utilisateur est administrateur
     * @param int $id ID de l'utilisateur
     * @return bool Résultat de la vérification
     */
    public function isAdmin($id) {
        $sql = "SELECT role FROM users WHERE id = ?";
        $user = $this->db->fetchOne($sql, [$id]);
        
        return $user && $user['role'] === 'ADMIN';
    }
    
    /**
     * Vérifier si un utilisateur est expert
     * @param int $id ID de l'utilisateur
     * @return bool Résultat de la vérification
     */
    public function isExpert($id) {
        $sql = "SELECT role FROM users WHERE id = ?";
        $user = $this->db->fetchOne($sql, [$id]);
        
        return $user && ($user['role'] === 'EXPERT' || $user['role'] === 'ADMIN');
    }
}
