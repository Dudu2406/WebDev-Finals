<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

verify_csrf($_POST['csrf_token'] ?? null);

$creator_id = (int)($_POST['creator_id'] ?? 0);

$stmt = $pdo->prepare(
    "INSERT IGNORE INTO favorites (user_id, creator_id) VALUES (:user_id, :creator_id)"
);
$stmt->execute([':user_id' => current_user_id(), ':creator_id' => $creator_id]);

flash('Added to your favorites.');
header('Location: creator.php?id=' . $creator_id);
exit;
