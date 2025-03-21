<?php

namespace App\Models;

use PDO;

abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct()
    {
        $config = require __DIR__ . '/../Config/config.php';
        
        try {
            $this->db = new PDO(
                "mysql:host={$config['database']['host']};dbname={$config['database']['database']}",
                $config['database']['username'],
                $config['database']['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
        } catch (\PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function all()
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    public function create(array $data)
    {
        $fields = array_keys($data);
        $values = array_values($data);
        $placeholders = str_repeat('?,', count($fields) - 1) . '?';
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $fields),
            $placeholders
        );
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        
        return $this->find($this->db->lastInsertId());
    }

    public function update($id, array $data)
    {
        $fields = array_keys($data);
        $set = implode('=?, ', $fields) . '=?';
        $values = array_values($data);
        $values[] = $id;
        
        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = ?",
            $this->table,
            $set,
            $this->primaryKey
        );
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }

    public function where($field, $value)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$field} = ?");
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }

    public function findBy(array $criteria)
    {
        $where = [];
        $values = [];
        
        foreach ($criteria as $field => $value) {
            $where[] = "{$field} = ?";
            $values[] = $value;
        }
        
        $sql = sprintf(
            "SELECT * FROM %s WHERE %s",
            $this->table,
            implode(' AND ', $where)
        );
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        return $stmt->fetch();
    }

    public function beginTransaction()
    {
        return $this->db->beginTransaction();
    }

    public function commit()
    {
        return $this->db->commit();
    }

    public function rollback()
    {
        return $this->db->rollBack();
    }
} 