<?php
require_once(__DIR__ . '/../../config/db.php');
require_once __DIR__ . '/auth.php';

$flash = get_flash();
$page_title = $page_title ?? 'StreamWatch';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($page_title) ?></title>
<link rel="stylesheet" href="<?= isset($in_subdir) ? '../' : '' ?>assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container header-inner">
        <a class="logo" href="<?= isset($in_subdir) ? '../' : '' ?>index.php">📺 StreamWatch</a>

        <nav class="main-nav">
            <a href="<?= isset($in_subdir) ? '../' : '' ?>index.php">Browse</a>
            <?php if (is_logged_in()): ?>
                <a href="<?= isset($in_subdir) ? '../' : '' ?>favorites.php">My Favorites</a>
                <?php if (is_admin()): ?>
                    <a href="<?= isset($in_subdir) ? '' : 'admin/' ?>index.php">Admin</a>
                <?php endif; ?>
                <span class="nav-user">Logged in as: <?= h($_SESSION['username']) ?></span>
                <a href="<?= isset($in_subdir) ? '../' : '' ?>logout.php">Log out</a>
            <?php else: ?>
                <a href="<?= isset($in_subdir) ? '../' : '' ?>login.php">Log in</a>
                <a href="<?= isset($in_subdir) ? '../' : '' ?>register.php" class="btn-nav">Sign up</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container">
    <?php if ($flash): ?>
        <div class="flash flash-<?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
    <?php endif; ?>
