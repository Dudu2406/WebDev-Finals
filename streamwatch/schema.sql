CREATE DATABASE IF NOT EXISTS streamwatch CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE streamwatch;

-- Users
CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_admin      TINYINT(1)   NOT NULL DEFAULT 0,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Creators (Twitch Streamers / YouTubers)
CREATE TABLE IF NOT EXISTS creators (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL,
    content_type VARCHAR(50)  NOT NULL,   -- Gaming, Vlogs, Music, Education, etc.
    platform     VARCHAR(50)  DEFAULT NULL, -- YouTube, Twitch, Kick, Both...
    description  TEXT,
    image_url    VARCHAR(255) DEFAULT NULL,
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Reviews (star rating + text, one per user per creator)
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

-- Favorites (user's followed creator list)
CREATE TABLE IF NOT EXISTS favorites (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    creator_id INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_favorite (user_id, creator_id),
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (creator_id) REFERENCES creators(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Admin account > username: admin / password: admin123
INSERT INTO users (username, email, password_hash, is_admin) VALUES
('admin', 'admin@streamwatch.local', '$2y$10$l9YVdx6d4zyHvaHJZzgme.8dBXHD5XMNketRyfvkKiqA5HnV1bmzS', 1);

-- Creators
INSERT INTO creators (name, content_type, platform, description, image_url) VALUES
('PixelForge', 'Gaming', 'Twitch', 'Speedruns, co-op nights, and the occasional rage quit. Known for a laid-back chat and Friday marathon streams.', NULL),
('WanderNotes', 'Vlogs', 'YouTube', 'Slow-travel vlogs from small towns most tourists skip. Weekly uploads with a focus on food and local history.', NULL),
('LoFi Lantern', 'Music', 'YouTube', '24/7 lofi and ambient mixes for studying, plus monthly original beat tapes.', NULL),
('CodeCampfire', 'Education', 'Twitch', 'Live coding sessions building real projects from scratch, beginner-friendly explanations included.', NULL),
('Kitchen Static', 'Cooking', 'YouTube', 'Weeknight recipes tested for people who hate doing dishes. Fast, funny, and occasionally chaotic.', NULL),
('Nightslate', 'Gaming', 'Kick', 'Late-night just-chatting and gaming streams with a fast-growing, tight-knit community.', NULL),
('MrBeast', 'Entertainment', 'YouTube', 'Content creator known for his large-scale challenges and philanthropy.', NULL),
('PewDiePie', 'Gaming', 'YouTube', 'One of the most popular YouTubers, known for gaming content and commentary.', NULL),
('Ninja', 'Gaming', 'Twitch', 'Professional gamer and streamer, famous for Fortnite gameplay.', NULL),
('Marques Brownlee', 'Technology', 'YouTube', 'Tech reviewer and content creator, known for in-depth reviews and tech insights.', NULL),
('CoComelon', 'Entertainment', 'YouTube', 'Popular children''s channel featuring nursery rhymes and educational content.', NULL),
('Dude Perfect', 'Entertainment', 'YouTube', 'Comedy and entertainment channel known for humorous sketches and challenges.', NULL),
('Markiplier', 'Gaming', 'YouTube', 'Popular gaming YouTuber known for his Let''s Play videos and charity work.', NULL),
('Jacksepticeye', 'Gaming', 'YouTube', 'Irish YouTuber known for his energetic gaming content and vlogs.', NULL),
('Dream', 'Gaming', 'YouTube', 'Minecraft content creator known for speedruns and collaborative projects.', NULL),
('Pokimane', 'Gaming', 'Twitch', 'Popular Twitch streamer known for gaming content and community engagement.', NULL),
('Shroud', 'Gaming', 'Twitch', 'Professional gamer and streamer, known for his exceptional aim and gameplay skills.', NULL),
('Kai Cenat', 'Entertainment', 'Twitch', 'Content creator known for comedy clips and social media presence.', NULL),
('xQc', 'Gaming', 'Twitch', 'Popular Twitch streamer known for his variety of content and energetic personality.', NULL),
('Valkyrae', 'Gaming', 'YouTube', 'Content creator known for gaming streams and collaborations with other creators.', NULL),
('Jynxzi', 'Gaming', 'Twitch', 'Streamer known for engaging gameplay and community interaction.', NULL),
('CaseOh', 'Gaming', 'Twitch', 'Content creator known for his energetic personality and variety of content. Also known for his growing variety and horror gaming content.', NULL),
('Adin Ross', 'Vlogs', 'Twitch', 'Known for his high-profile collaboration and reaction videos.', NULL),
('iShowSpeed', 'Entertainment', 'YouTube', 'An energetic creator known for lively gaming streams, viral reactions and sports commentary.', NULL),
('Ludwig', 'Entertainment', 'YouTube', 'A popular streamer and host famous for high-stakes gaming marathons, creative events, and comedy commentary.', NULL),
('DanTDM', 'Gaming', 'YouTube', 'A British creator known for family-friendly Minecraft gameplay, mod showcases, and story-driven gaming videos.', NULL),
('PopularMMOs', 'Gaming', 'YouTube', 'A long-time creator recognized for playful Minecraft mod battles, custom map playthroughs, and collaborative content.', NULL),
('SSundee', 'Gaming', 'YouTube', 'A well-known creator known for funny modded gameplay, Minecraft challenges, and diverse game experiments.', NULL),
('SSSniperWolf', 'Gaming', 'YouTube', 'A creator famous for reacting to viral videos, internet stories, and sharing interesting online content.', NULL),
('Kwebbelkop', 'Gaming', 'YouTube', 'A creator known for gaming challenges, funny experiments, and creative scripted content.', NULL),
('Jelly', 'Gaming', 'YouTube', 'A creator best known for high-energy Minecraft gameplays, funny challenges, and collaborative videos with friends.', NULL),
('JasonTheWeen', 'Gaming', 'YouTube', 'A creator known for chaotic Roblox gameplay, funny skits, and relatable internet humor.', NULL),
('Marlon', 'Entertainment', 'YouTube', 'A creator known for his casual vlogs centered around online trends and variety content.', NULL),
('Ryan''s World', 'Entertainment', 'YouTube', 'Family-friendly channel with toy reviews, pretend play, and fun learning adventures.', NULL),
('Ryan Trahan', 'Vlogs', 'YouTube', 'A creator known for creative cross-country challenges, fundraising missions, and storytelling travel content.', NULL),
('Guava Juice', 'Entertainment', 'YouTube', 'Creates quirky DIY experiments, large-scale challenges, and playful unboxing videos.', NULL),
('Smosh', 'Entertainment', 'YouTube', 'Iconic comedy duo/brand famous for sketch parodies, skits, and hilarious pop culture commentary.', NULL),
('Alex Wassabi', 'Entertainment', 'YouTube', 'Makes comedy skits, challenge videos, travel vlogs, and lighthearted reaction content.', NULL),
('Marshmello', 'Music', 'YouTube', 'Electronic music producer and DJ known for his hit songs, live performances, and collaborations.', NULL),
('Alan Walker', 'Music', 'YouTube', 'Electronic music producer and DJ known for his signature sound and visually striking music videos.', NULL),
('Zach King', 'Entertainment', 'YouTube', 'Famous for his creative and mind-bending magic videos, visual effects, and storytelling content.', NULL),
('KSI', 'Entertainment', 'YouTube', 'Content creator, musician, and boxer known for his gaming videos, music releases, and boxing matches.', NULL),
('Logan Paul', 'Entertainment', 'YouTube', 'Content creator, podcaster, and boxer known for his vlogs, comedy sketches, and boxing events.', NULL),
('Jake Paul', 'Entertainment', 'YouTube', 'Content creator, entrepreneur, and boxer known for his vlogs, pranks, and boxing career.', NULL),
('Denji', 'Entertainment', 'YouTube', 'Shares comedy commentary, gaming clips, reaction videos and personal lifestyle updates.', NULL);