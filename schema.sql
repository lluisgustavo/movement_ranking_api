-- Ranking de Movimentos - Schema do banco de dados
-- MySQL 8

CREATE DATABASE IF NOT EXISTS movement_ranking
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE movement_ranking;

CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE movements (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE personal_records (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    movement_id INT UNSIGNED NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    recorded_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_records_user
        FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_records_movement
        FOREIGN KEY (movement_id) REFERENCES movements(id),
    INDEX idx_records_movement_user (movement_id, user_id),
    INDEX idx_records_user (user_id),
    INDEX idx_records_movement (movement_id)
) ENGINE=InnoDB;

INSERT INTO users (id, name) VALUES
(1, 'Joao'),
(2, 'Jose'),
(3, 'Paulo');

INSERT INTO movements (id, name) VALUES
(1, 'Deadlift'),
(2, 'Back Squat'),
(3, 'Bench Press');

INSERT INTO personal_records (id, user_id, movement_id, value, recorded_at) VALUES
(1, 1, 1, 100.0, '2021-01-01 00:00:00'),
(2, 1, 1, 180.0, '2021-01-02 00:00:00'),
(3, 1, 1, 150.0, '2021-01-03 00:00:00'),
(4, 1, 1, 110.0, '2021-01-04 00:00:00'),
(5, 2, 1, 110.0, '2021-01-04 00:00:00'),
(6, 2, 1, 140.0, '2021-01-05 00:00:00'),
(7, 2, 1, 190.0, '2021-01-06 00:00:00'),
(8, 3, 1, 170.0, '2021-01-01 00:00:00'),
(9, 3, 1, 120.0, '2021-01-02 00:00:00'),
(10, 3, 1, 130.0, '2021-01-03 00:00:00'),
(11, 1, 2, 130.0, '2021-01-03 00:00:00'),
(12, 2, 2, 130.0, '2021-01-03 00:00:00'),
(13, 3, 2, 125.0, '2021-01-03 00:00:00'),
(14, 1, 2, 110.0, '2021-01-05 00:00:00'),
(15, 1, 2, 100.0, '2021-01-01 00:00:00'),
(16, 2, 2, 120.0, '2021-01-01 00:00:00'),
(17, 3, 2, 120.0, '2021-01-01 00:00:00');
