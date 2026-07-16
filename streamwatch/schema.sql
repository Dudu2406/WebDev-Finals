-- StreamWatch Database Schema
-- Run this file once to create the database, tables, and some sample data.
-- Example: mysql -u root -p < schema.sql

CREATE DATABASE IF NOT EXISTS streamwatch CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE streamwatch;

-- ---------------------------------------------------------------
-- Users
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_admin      TINYINT(1)   NOT NULL DEFAULT 0,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Creators (streamers / YouTubers)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS creators (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL,
    content_type VARCHAR(50)  NOT NULL,   -- Gaming, Vlogs, Music, Education, etc.
    platform     VARCHAR(50)  DEFAULT NULL, -- YouTube, Twitch, Both...
    description  TEXT,
    image_url    VARCHAR(255) DEFAULT NULL,
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Reviews (star rating + text, one per user per creator)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reviews (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    creator_id  INT NOT NULL,
    rating      TINYINT NOT NULL,
    review_text TEXT,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5),
    UNIQUE KEY unique_user_creator_review (user_id, creator_id),
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (creator_id) REFERENCES creators(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Favorites (a user's followed-creator list)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS favorites (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    creator_id INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_favorite (user_id, creator_id),
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (creator_id) REFERENCES creators(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------
-- Seed data
-- ---------------------------------------------------------------

-- Admin account -> username: admin / password: admin123
-- (hash below corresponds to "admin123", generated with PHP password_hash)
INSERT INTO users (username, email, password_hash, is_admin) VALUES
('admin', 'admin@streamwatch.local', '$2y$10$l9YVdx6d4zyHvaHJZzgme.8dBXHD5XMNketRyfvkKiqA5HnV1bmzS', 1);

-- Sample creators
INSERT INTO creators (name, content_type, platform, description, image_url) VALUES
('PixelForge', 'Gaming', 'Twitch', 'Speedruns, co-op nights, and the occasional rage quit. Known for a laid-back chat and Friday marathon streams.', NULL),
('WanderNotes', 'Vlogs', 'YouTube', 'Slow-travel vlogs from small towns most tourists skip. Weekly uploads with a focus on food and local history.', NULL),
('LoFi Lantern', 'Music', 'YouTube', '24/7 lofi and ambient mixes for studying, plus monthly original beat tapes.', NULL),
('CodeCampfire', 'Education', 'Twitch', 'Live coding sessions building real projects from scratch, beginner-friendly explanations included.', NULL),
('Kitchen Static', 'Cooking', 'YouTube', 'Weeknight recipes tested for people who hate doing dishes. Fast, funny, and occasionally chaotic.', NULL);
