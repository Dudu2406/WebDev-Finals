<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

verify_csrf($_POST['csrf_token'] ?? null);

$review_id  = (int)($_POST['review_id'] ?? 0);
$creator_id = (int)($_POST['creator_id'] ?? 0);

$stmt = $pdo->prepare("DELETE FROM reviews WHERE id = :id AND user_id = :user_id");
$stmt->execute([':id' => $review_id, ':user_id' => current_user_id()]);

if ($stmt->rowCount() > 0) {
    flash('Your review was deleted.');
} else {
    flash('That review could not be deleted.', 'error');
}

header('Location: creator.php?id=' . $creator_id);
exit;
