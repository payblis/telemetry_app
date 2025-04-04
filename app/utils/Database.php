<?php
/**
 * Classe utilitaire pour la gestion des bases de données
 * 
 * Fournit une couche d'abstraction pour les opérations de base de données
 */
namespace App\Utils;

class Database {
    /**
     * Instance PDO
     */
    protected static $instance = null;
    
    /**
     * Options de configuration PDO
     */
    protected static $options = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    /**
     * Obtenir une instance de connexion à la base de données
     * 
     * @return \PDO Instance de PDO
     */
    public static function getInstance() {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            
            try {
                self::$instance = new \PDO($dsn, DB_USER, DB_PASS, self::$options);
            } catch (\PDOException $e) {
                throw new \Exception('Erreur de connexion à la base de données: ' . $e->getMessage());
            }
        }
        
        return self::$instance;
    }
    
    /**
     * Exécuter une requête SQL
     * 
     * @param string $sql Requête SQL
     * @param array $params Paramètres de la requête
     * @return \PDOStatement Résultat de la requête
     */
    public static function query($sql, $params = []) {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Récupérer tous les résultats d'une requête
     * 
     * @param string $sql Requête SQL
     * @param array $params Paramètres de la requête
     * @return array Résultats de la requête
     */
    public static function fetchAll($sql, $params = []) {
        return self::query($sql, $params)->fetchAll();
    }
    
    /**
     * Récupérer un seul résultat d'une requête
     * 
     * @param string $sql Requête SQL
     * @param array $params Paramètres de la requête
     * @return array|false Résultat de la requête ou false si aucun résultat
     */
    public static function fetch($sql, $params = []) {
        return self::query($sql, $params)->fetch();
    }
    
    /**
     * Récupérer une seule valeur d'une requête
     * 
     * @param string $sql Requête SQL
     * @param array $params Paramètres de la requête
     * @return mixed Valeur récupérée ou false si aucun résultat
     */
    public static function fetchColumn($sql, $params = []) {
        return self::query($sql, $params)->fetchColumn();
    }
    
    /**
     * Insérer des données dans une table
     * 
     * @param string $table Nom de la table
     * @param array $data Données à insérer (tableau associatif colonne => valeur)
     * @return int|false ID de la dernière insertion ou false en cas d'échec
     */
    public static function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = self::getInstance()->prepare($sql);
        
        if ($stmt->execute($data)) {
            return self::getInstance()->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Mettre à jour des données dans une table
     * 
     * @param string $table Nom de la table
     * @param array $data Données à mettre à jour (tableau associatif colonne => valeur)
     * @param string $where Condition WHERE
     * @param array $params Paramètres pour la condition WHERE
     * @return int Nombre de lignes affectées
     */
    public static function update($table, $data, $where, $params = []) {
        $setClause = [];
        foreach (array_keys($data) as $column) {
            $setClause[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setClause);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $stmt = self::getInstance()->prepare($sql);
        
        // Fusionner les données et les paramètres
        $executeParams = array_merge($data, $params);
        
        $stmt->execute($executeParams);
        return $stmt->rowCount();
    }
    
    /**
     * Supprimer des données d'une table
     * 
     * @param string $table Nom de la table
     * @param string $where Condition WHERE
     * @param array $params Paramètres pour la condition WHERE
     * @return int Nombre de lignes affectées
     */
    public static function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = self::getInstance()->prepare($sql);
        
        $stmt->execute($params);
        return $stmt->rowCount();
    }
    
    /**
     * Compter le nombre de lignes dans une table
     * 
     * @param string $table Nom de la table
     * @param string $where Condition WHERE (optionnel)
     * @param array $params Paramètres pour la condition WHERE (optionnel)
     * @return int Nombre de lignes
     */
    public static function count($table, $where = '', $params = []) {
        $sql = "SELECT COUNT(*) FROM {$table}";
        
        if (!empty($where)) {
            $sql .= " WHERE {$where}";
        }
        
        return (int) self::fetchColumn($sql, $params);
    }
    
    /**
     * Vérifier si une valeur existe dans une table
     * 
     * @param string $table Nom de la table
     * @param string $column Nom de la colonne
     * @param mixed $value Valeur à rechercher
     * @return bool La valeur existe ou non
     */
    public static function exists($table, $column, $value) {
        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = :value";
        return (int) self::fetchColumn($sql, ['value' => $value]) > 0;
    }
    
    /**
     * Commencer une transaction
     */
    public static function beginTransaction() {
        self::getInstance()->beginTransaction();
    }
    
    /**
     * Valider une transaction
     */
    public static function commit() {
        self::getInstance()->commit();
    }
    
    /**
     * Annuler une transaction
     */
    public static function rollback() {
        self::getInstance()->rollBack();
    }
    
    /**
     * Obtenir le dernier ID inséré
     * 
     * @return string Dernier ID inséré
     */
    public static function lastInsertId() {
        return self::getInstance()->lastInsertId();
    }
    
    /**
     * Échapper un identifiant (nom de table ou de colonne)
     * 
     * @param string $identifier Identifiant à échapper
     * @return string Identifiant échappé
     */
    public static function escapeIdentifier($identifier) {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
