-- Crear base de datos
DROP DATABASE IF EXISTS moodplanned;
CREATE DATABASE moodplanned CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE moodplanned;

-- ======================
--        USERS
-- ======================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255),   -- Imagen de perfil del usuario
    banner VARCHAR(255),          -- Imagen de banner del usuario
    bio VARCHAR(255),          -- BiografÃ­a del usuario
    points INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ======================
--         PLANS
-- ======================
CREATE TABLE plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    category VARCHAR(50),
    lat DECIMAL(10,7),
    lng DECIMAL(10,7),
    direccion VARCHAR(255),       -- Nueva columna para la dirección
    image VARCHAR(255),           -- Imagen asociada al plan
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);


-- ======================
--       FAVORITES
-- ======================
CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    plan_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE CASCADE
);

-- ======================
--        REVIEWS
-- ======================
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_id INT NOT NULL,
    rating TINYINT NOT NULL,   -- 1-5
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (user_id, plan_id)
);

-- ======================
--         MOODS
-- ======================
CREATE TABLE moods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    mood VARCHAR(50),
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ======================
--      DAILY_MODS
-- ======================
CREATE TABLE user_mood_tracker ( 
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    mood VARCHAR(50) NOT NULL,
    last_check DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS saved_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE CASCADE,
    UNIQUE KEY unique_saved (user_id, plan_id)
);