<?php
/**
 * Session bootstrap + authentication/authorization helpers.
 * Included at the top of every page (via header.php) so session state
 * and CSRF protection are always available.
 */

// Buffer output so that pages which check permissions (require_login/require_admin)
// *after* including header.php can still redirect with header() — without this,
// the HTML already echoed by header.php would make any later header() call fail.
if (ob_get_level() === 0) {
    ob_start();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Is anyone logged in right now? */
function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

/** Is the logged-in user an admin? */
function is_admin(): bool
{
    return is_logged_in() && !empty($_SESSION['is_admin']);
}

/** Current user's id, or null if logged out. */
function current_user_id(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

/** Bounce non-logged-in visitors to the login page. */
function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

/** Bounce non-admins away from admin-only pages. */
function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        die('Access denied. Admins only.');
    }
}

/** Generate (or reuse) a CSRF token for the current session. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Validate a submitted CSRF token, ending the request if it's wrong. */
function verify_csrf(?string $token): void
{
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Invalid or expired form submission. Please go back and try again.');
    }
}

/** Small helper to escape output consistently. */
function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Set a one-time flash message shown on the next page load. */
function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

/** Pull (and clear) the current flash message, if any. */
function get_flash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}
