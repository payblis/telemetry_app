<?php
/**
 * Classe Database
 * Gère la connexion à la base de données et les opérations de base
 */
class Database {
    private static $instance = null;
    private $connection;
    
    /**
     * Constructeur privé (pattern Singleton)
     */
    private function __construct() {
        require_once ROOT_PATH . '/config/database.php';
        
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, DB_OPTIONS);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données: " . $e->getMessage());
        }
    }
    
    /**
     * Obtenir l'instance unique de la base de données
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtenir la connexion PDO
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Exécuter une requête SQL
     * @param string $sql Requête SQL
     * @param array $params Paramètres pour la requête préparée
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            die("Erreur d'exécution de la requête: " . $e->getMessage());
        }
    }
    
    /**
     * Récupérer une seule ligne
     * @param string $sql Requête SQL
     * @param array $params Paramètres pour la requête préparée
     * @return array|false
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Récupérer toutes les lignes
     * @param string $sql Requête SQL
     * @param array $params Paramètres pour la requête préparée
     * @return array
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Insérer des données et retourner l'ID
     * @param string $table Nom de la table
     * @param array $data Données à insérer (clé => valeur)
     * @return int ID de la dernière insertion
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));
        
        return $this->connection->lastInsertId();
    }
    
    /**
     * Mettre à jour des données
     * @param string $table Nom de la table
     * @param array $data Données à mettre à jour (clé => valeur)
     * @param string $where Condition WHERE
     * @param array $params Paramètres pour la condition WHERE
     * @return int Nombre de lignes affectées
     */
    public function update($table, $data, $where, $params = []) {
        $set = [];
        foreach ($data as $column => $value) {
            $set[] = "{$column} = ?";
        }
        $set = implode(', ', $set);
        
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        $stmt = $this->query($sql, array_merge(array_values($data), $params));
        
        return $stmt->rowCount();
    }
    
    /**
     * Supprimer des données
     * @param string $table Nom de la table
     * @param string $where Condition WHERE
     * @param array $params Paramètres pour la condition WHERE
     * @return int Nombre de lignes affectées
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        
        return $stmt->rowCount();
    }
}
