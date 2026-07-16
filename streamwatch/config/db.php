<?php
/**
 * Database connection.
 * Update the four constants below to match your local MySQL setup.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'streamwatch');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // In production you would log this instead of showing it to the user.
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}
