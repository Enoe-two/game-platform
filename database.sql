-- Création de la base de données
CREATE DATABASE IF NOT EXISTS game_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE game_platform;

-- Table des joueurs en ligne
CREATE TABLE IF NOT EXISTS players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    last_activity DATETIME NOT NULL,
    INDEX idx_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des parties
CREATE TABLE IF NOT EXISTS game_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_type VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL,
    status ENUM('waiting', 'playing', 'finished') DEFAULT 'waiting',
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des joueurs dans les parties
CREATE TABLE IF NOT EXISTS game_players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    username VARCHAR(50) NOT NULL,
    score INT DEFAULT 0,
    game_data TEXT,
    FOREIGN KEY (session_id) REFERENCES game_sessions(id) ON DELETE CASCADE,
    INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des scores généraux
CREATE TABLE IF NOT EXISTS leaderboard (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    game_type VARCHAR(50) NOT NULL,
    score INT NOT NULL,
    played_at DATETIME NOT NULL,
    INDEX idx_game_score (game_type, score DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
