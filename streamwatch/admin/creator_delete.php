<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

verify_csrf($_POST['csrf_token'] ?? null);

$id = (int)($_POST['id'] ?? 0);

// Related reviews and favorites are deleted as well!!!
$stmt = $pdo->prepare("DELETE FROM creators WHERE id = :id");
$stmt->execute([':id' => $id]);

flash('Creator deleted.');
header('Location: index.php');
exit;
