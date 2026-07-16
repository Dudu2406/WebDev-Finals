<?php
$page_title = 'Log in — StreamWatch';
require_once __DIR__ . '/includes/header.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf_token'] ?? null);

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, username, password_hash, is_admin FROM users WHERE username = :u1 OR email = :u2");
    $stmt->execute([':u1' => $username, ':u2' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = (int)$user['is_admin'];

        flash('Welcome back, ' . $user['username'] . '!');
        header('Location: index.php');
        exit;
    }

    $errors[] = 'Incorrect username/email or password.';
}
?>

<div class="form-card">
    <h1>Log in</h1>
    <p>Pick up where you left off.</p>

    <?php foreach ($errors as $error): ?>
        <div class="form-error"><?= h($error) ?></div>
    <?php endforeach; ?>

    <form method="post" action="login.php">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

        <label for="username">Username or email</label>
        <input type="text" id="username" name="username" value="<?= h($username) ?>" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-block">Log in</button>
        </div>
    </form>

    <p style="margin-top:20px; font-size:0.9rem;">New here? <a href="register.php" style="color:var(--accent)">Create an account</a></p>
    <p class="field-hint">Demo admin login — username: <code>admin</code>, password: <code>admin123</code></p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
