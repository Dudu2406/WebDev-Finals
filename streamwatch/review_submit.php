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
$rating     = (int)($_POST['rating'] ?? 0);
$review_text = trim($_POST['review_text'] ?? '');

if ($rating < 1 || $rating > 5) {
    flash('Please choose a star rating between 1 and 5.', 'error');
    header('Location: creator.php?id=' . $creator_id);
    exit;
}

// Check if creator exists
$check = $pdo->prepare("SELECT id FROM creators WHERE id = :id");
$check->execute([':id' => $creator_id]);
if (!$check->fetch()) {
    http_response_code(404);
    die('Creator not found.');
}

// Insert new review or update existing review
$stmt = $pdo->prepare(
    "INSERT INTO reviews (user_id, creator_id, rating, review_text)
     VALUES (:user_id, :creator_id, :rating, :text)
     ON DUPLICATE KEY UPDATE rating = :rating2, review_text = :text2"
);
$stmt->execute([
    ':user_id'    => current_user_id(),
    ':creator_id' => $creator_id,
    ':rating'     => $rating,
    ':text'       => $review_text,
    ':rating2'    => $rating,
    ':text2'      => $review_text,
]);

flash('Your review has been saved.');
header('Location: creator.php?id=' . $creator_id);
exit;
