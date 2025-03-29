-- Structure de la table telemetry_data
CREATE TABLE IF NOT EXISTS telemetry_data (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL,
    timestamp DATETIME NOT NULL,
    speed FLOAT NOT NULL,
    rpm INT NOT NULL,
    gear TINYINT NOT NULL,
    throttle FLOAT NOT NULL,
    brake FLOAT NOT NULL,
    lean_angle FLOAT NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    acceleration_x FLOAT,
    acceleration_y FLOAT,
    acceleration_z FLOAT,
    gyro_x FLOAT,
    gyro_y FLOAT,
    gyro_z FLOAT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index pour optimiser les requêtes
CREATE INDEX idx_telemetry_session_time ON telemetry_data(session_id, timestamp);

-- Structure de la table lap_times
CREATE TABLE IF NOT EXISTS lap_times (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL,
    lap_number INT UNSIGNED NOT NULL,
    lap_time INT UNSIGNED NOT NULL,
    sector1_time INT UNSIGNED,
    sector2_time INT UNSIGNED,
    sector3_time INT UNSIGNED,
    max_speed FLOAT,
    timestamp DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index pour optimiser les requêtes
CREATE INDEX idx_lap_session ON lap_times(session_id);
CREATE INDEX idx_lap_number ON lap_times(session_id, lap_number);

-- Structure de la table circuit_sectors
CREATE TABLE IF NOT EXISTS circuit_sectors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    circuit_id INT UNSIGNED NOT NULL,
    sector_number TINYINT UNSIGNED NOT NULL,
    start_latitude DECIMAL(10, 8) NOT NULL,
    start_longitude DECIMAL(11, 8) NOT NULL,
    end_latitude DECIMAL(10, 8) NOT NULL,
    end_longitude DECIMAL(11, 8) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (circuit_id) REFERENCES circuits(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index pour optimiser les requêtes
CREATE INDEX idx_circuit_sector ON circuit_sectors(circuit_id, sector_number);

-- Structure de la table circuit_corners
CREATE TABLE IF NOT EXISTS circuit_corners (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    circuit_id INT UNSIGNED NOT NULL,
    corner_number INT UNSIGNED NOT NULL,
    name VARCHAR(50),
    angle INT,
    direction ENUM('left', 'right'),
    entry_speed_estimate INT,
    exit_speed_estimate INT,
    gear_recommendation TINYINT,
    notes TEXT,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (circuit_id) REFERENCES circuits(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index pour optimiser les requêtes
CREATE INDEX idx_circuit_corner ON circuit_corners(circuit_id, corner_number);

-- Structure de la table telemetry_settings
CREATE TABLE IF NOT EXISTS telemetry_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL,
    setting_type ENUM('suspension', 'transmission', 'engine', 'electronics', 'tires') NOT NULL,
    setting_name VARCHAR(50) NOT NULL,
    setting_value VARCHAR(50) NOT NULL,
    timestamp DATETIME NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index pour optimiser les requêtes
CREATE INDEX idx_telemetry_settings ON telemetry_settings(session_id, setting_type);

-- Structure de la table telemetry_events
CREATE TABLE IF NOT EXISTS telemetry_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL,
    event_type ENUM('crash', 'highside', 'lowside', 'track_limit', 'pit_in', 'pit_out', 'flag') NOT NULL,
    timestamp DATETIME NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index pour optimiser les requêtes
CREATE INDEX idx_telemetry_events ON telemetry_events(session_id, event_type, timestamp);

-- Structure de la table weather_conditions
CREATE TABLE IF NOT EXISTS weather_conditions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL,
    timestamp DATETIME NOT NULL,
    air_temperature FLOAT NOT NULL,
    track_temperature FLOAT NOT NULL,
    humidity FLOAT,
    wind_speed FLOAT,
    wind_direction INT,
    pressure FLOAT,
    conditions ENUM('sunny', 'cloudy', 'partly_cloudy', 'rain', 'heavy_rain') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index pour optimiser les requêtes
CREATE INDEX idx_weather_conditions ON weather_conditions(session_id, timestamp); 