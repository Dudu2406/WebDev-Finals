<?php
if (ob_get_level() === 0) {
    ob_start();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

// Check if the user is an admin
function is_admin(): bool
{
    return is_logged_in() && !empty($_SESSION['is_admin']);
}

// Get the current user id
function current_user_id(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

// Send non logged in users to login page
function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

// Stop non admin users from admin pages 
function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        die('Access denied. Admins only.');
    }
}

// Create or get CSRF token
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Check if CSRF token is valid
function verify_csrf(?string $token): void
{
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Invalid or expired form submission. Please go back and try again.');
    }
}

// Escape text for safe display
function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Save a message to show once
function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

// Get and remove flash message
function get_flash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}
