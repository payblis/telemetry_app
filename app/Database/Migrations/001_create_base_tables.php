<?php

use App\Database\Migration;

class CreateBaseTables extends Migration
{
    public function up()
    {
        // Users table
        $this->db->query("
            CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(255) NOT NULL UNIQUE,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin', 'user', 'telemetrist') NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Virtual Telemetrists table
        $this->db->query("
            CREATE TABLE telemetriste_virtuel (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                settings JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Pilots table
        $this->db->query("
            CREATE TABLE pilotes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                age INT,
                weight DECIMAL(5,2),
                height DECIMAL(5,2),
                experience TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Motorcycles table
        $this->db->query("
            CREATE TABLE motos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                marque VARCHAR(255) NOT NULL,
                modele VARCHAR(255) NOT NULL,
                annee INT NOT NULL,
                cylindree INT,
                puissance INT,
                poids DECIMAL(6,2),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Equipment table
        $this->db->query("
            CREATE TABLE equipements_moto (
                id INT AUTO_INCREMENT PRIMARY KEY,
                moto_id INT NOT NULL,
                type ENUM('suspension', 'frein', 'pneu', 'ecu', 'capteur') NOT NULL,
                marque VARCHAR(255),
                modele VARCHAR(255),
                specifications JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (moto_id) REFERENCES motos(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Circuits table
        $this->db->query("
            CREATE TABLE circuits (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nom VARCHAR(255) NOT NULL,
                longueur DECIMAL(6,2),
                type_virages TEXT,
                meteo_habituelle TEXT,
                adherence TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Sessions table
        $this->db->query("
            CREATE TABLE sessions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                circuit_id INT NOT NULL,
                pilote_id INT NOT NULL,
                moto_id INT NOT NULL,
                date_session DATETIME NOT NULL,
                conditions_meteo TEXT,
                reglages_initiaux JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (circuit_id) REFERENCES circuits(id),
                FOREIGN KEY (pilote_id) REFERENCES pilotes(id),
                FOREIGN KEY (moto_id) REFERENCES motos(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Laps table
        $this->db->query("
            CREATE TABLE tours (
                id INT AUTO_INCREMENT PRIMARY KEY,
                session_id INT NOT NULL,
                numero_tour INT NOT NULL,
                temps_tour DECIMAL(8,3),
                donnees_telemetrie JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Telemetry data table
        $this->db->query("
            CREATE TABLE telemetry_data (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tour_id INT NOT NULL,
                timestamp DATETIME(3) NOT NULL,
                type_donnee VARCHAR(50) NOT NULL,
                valeur DECIMAL(10,3),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Internal AI Knowledge table
        $this->db->query("
            CREATE TABLE ia_internal_knowledge (
                id INT AUTO_INCREMENT PRIMARY KEY,
                categorie VARCHAR(255) NOT NULL,
                question TEXT NOT NULL,
                reponse TEXT NOT NULL,
                confiance DECIMAL(4,3),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Expert responses table
        $this->db->query("
            CREATE TABLE expert_responses (
                id INT AUTO_INCREMENT PRIMARY KEY,
                expert_id INT NOT NULL,
                question_id INT NOT NULL,
                reponse TEXT NOT NULL,
                validee BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (expert_id) REFERENCES users(id),
                FOREIGN KEY (question_id) REFERENCES ia_internal_knowledge(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // API configuration table
        $this->db->query("
            CREATE TABLE api_config (
                id INT AUTO_INCREMENT PRIMARY KEY,
                api_key VARCHAR(255) NOT NULL,
                service VARCHAR(50) NOT NULL,
                configuration JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS api_config");
        $this->db->query("DROP TABLE IF EXISTS expert_responses");
        $this->db->query("DROP TABLE IF EXISTS ia_internal_knowledge");
        $this->db->query("DROP TABLE IF EXISTS telemetry_data");
        $this->db->query("DROP TABLE IF EXISTS tours");
        $this->db->query("DROP TABLE IF EXISTS sessions");
        $this->db->query("DROP TABLE IF EXISTS circuits");
        $this->db->query("DROP TABLE IF EXISTS equipements_moto");
        $this->db->query("DROP TABLE IF EXISTS motos");
        $this->db->query("DROP TABLE IF EXISTS pilotes");
        $this->db->query("DROP TABLE IF EXISTS telemetriste_virtuel");
        $this->db->query("DROP TABLE IF EXISTS users");
    }
} 