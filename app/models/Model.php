<?php
/**
 * Classe de base pour tous les modèles
 * 
 * Fournit les fonctionnalités CRUD de base et la connexion à la base de données
 */
namespace App\Models;

class Model {
    // Nom de la table associée au modèle
    protected $table;
    
    // Clé primaire de la table
    protected $primaryKey = 'id';
    
    // Colonnes autorisées pour l'assignement de masse
    protected $fillable = [];
    
    // Connexion à la base de données
    protected $db;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->db = $this->getConnection();
    }
    
    /**
     * Obtenir une connexion à la base de données
     * 
     * @return \PDO Instance de PDO
     */
    protected function getConnection() {
        static $db = null;
        
        if ($db === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            try {
                $db = new \PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (\PDOException $e) {
                throw new \Exception('Erreur de connexion à la base de données: ' . $e->getMessage());
            }
        }
        
        return $db;
    }
    
    /**
     * Trouver un enregistrement par ID
     * 
     * @param int $id ID de l'enregistrement
     * @return array|null Enregistrement trouvé ou null
     */
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Récupérer tous les enregistrements
     * 
     * @param string $orderBy Colonne pour le tri
     * @param string $order Direction du tri (ASC ou DESC)
     * @return array Tableau d'enregistrements
     */
    public function all($orderBy = null, $order = 'ASC') {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer des enregistrements avec pagination
     * 
     * @param int $page Numéro de page
     * @param int $perPage Nombre d'enregistrements par page
     * @param string $orderBy Colonne pour le tri
     * @param string $order Direction du tri (ASC ou DESC)
     * @return array Tableau d'enregistrements
     */
    public function paginate($page = 1, $perPage = 20, $orderBy = null, $order = 'ASC') {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }
        
        $sql .= " LIMIT :offset, :perPage";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->bindValue(':perPage', $perPage, \PDO::PARAM_INT);
        $stmt->execute();
        
        $items = $stmt->fetchAll();
        
        // Compter le nombre total d'enregistrements
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table}");
        $stmt->execute();
        $total = $stmt->fetchColumn();
        
        return [
            'items' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }
    
    /**
     * Trouver des enregistrements par condition
     * 
     * @param string $column Nom de la colonne
     * @param mixed $value Valeur à rechercher
     * @param string $operator Opérateur de comparaison
     * @return array Tableau d'enregistrements
     */
    public function where($column, $value, $operator = '=') {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} {$operator} :value";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['value' => $value]);
        return $stmt->fetchAll();
    }
    
    /**
     * Créer un nouvel enregistrement
     * 
     * @param array $data Données à insérer
     * @return int|bool ID de l'enregistrement créé ou false
     */
    public function create(array $data) {
        // Filtrer les données pour ne garder que les colonnes autorisées
        $data = $this->filterData($data);
        
        if (empty($data)) {
            return false;
        }
        
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute($data)) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Mettre à jour un enregistrement
     * 
     * @param int $id ID de l'enregistrement
     * @param array $data Données à mettre à jour
     * @return bool Succès de l'opération
     */
    public function update($id, array $data) {
        // Filtrer les données pour ne garder que les colonnes autorisées
        $data = $this->filterData($data);
        
        if (empty($data)) {
            return false;
        }
        
        $setClause = [];
        foreach (array_keys($data) as $column) {
            $setClause[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setClause);
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        
        $data['id'] = $id;
        return $stmt->execute($data);
    }
    
    /**
     * Supprimer un enregistrement
     * 
     * @param int $id ID de l'enregistrement
     * @return bool Succès de l'opération
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Filtrer les données pour ne garder que les colonnes autorisées
     * 
     * @param array $data Données à filtrer
     * @return array Données filtrées
     */
    protected function filterData(array $data) {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * Exécuter une requête SQL personnalisée
     * 
     * @param string $sql Requête SQL
     * @param array $params Paramètres de la requête
     * @param bool $fetchAll Récupérer tous les résultats ou un seul
     * @return mixed Résultat de la requête
     */
    public function query($sql, $params = [], $fetchAll = true) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        if ($fetchAll) {
            return $stmt->fetchAll();
        }
        
        return $stmt->fetch();
    }
    
    /**
     * Compter le nombre d'enregistrements
     * 
     * @param string $column Colonne pour la condition (optionnel)
     * @param mixed $value Valeur pour la condition (optionnel)
     * @param string $operator Opérateur pour la condition (optionnel)
     * @return int Nombre d'enregistrements
     */
    public function count($column = null, $value = null, $operator = '=') {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        
        $params = [];
        if ($column !== null && $value !== null) {
            $sql .= " WHERE {$column} {$operator} :value";
            $params['value'] = $value;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Vérifier si un enregistrement existe
     * 
     * @param string $column Colonne pour la condition
     * @param mixed $value Valeur pour la condition
     * @param string $operator Opérateur pour la condition
     * @return bool L'enregistrement existe ou non
     */
    public function exists($column, $value, $operator = '=') {
        return $this->count($column, $value, $operator) > 0;
    }
    
    /**
     * Commencer une transaction
     */
    public function beginTransaction() {
        $this->db->beginTransaction();
    }
    
    /**
     * Valider une transaction
     */
    public function commit() {
        $this->db->commit();
    }
    
    /**
     * Annuler une transaction
     */
    public function rollback() {
        $this->db->rollBack();
    }
}
