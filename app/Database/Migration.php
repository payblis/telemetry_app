<?php

namespace App\Database;

abstract class Migration
{
    protected $db;

    public function __construct()
    {
        $config = require __DIR__ . '/../Config/config.php';
        $this->db = new \PDO(
            "mysql:host={$config['database']['host']};dbname={$config['database']['database']}",
            $config['database']['username'],
            $config['database']['password'],
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]
        );
    }

    abstract public function up();
    abstract public function down();
} 