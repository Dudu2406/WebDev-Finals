<?php
$page_title = 'Sign up — StreamWatch';
require_once __DIR__ . '/includes/header.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf_token'] ?? null);

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($username === '' || strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM users WHERE username = :u OR email = :e");
        $check->execute([':u' => $username, ':e' => $email]);
        if ($check->fetch()) {
            $errors[] = 'That username or email is already taken.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (:u, :e, :p)");
        $insert->execute([':u' => $username, ':e' => $email, ':p' => $hash]);

        $_SESSION['user_id']  = $pdo->lastInsertId();
        $_SESSION['username'] = $username;
        $_SESSION['is_admin'] = 0;

        flash('Welcome to StreamWatch, ' . $username . '!');
        header('Location: index.php');
        exit;
    }
}
?>

<div class="form-card">
    <h1>Create your account</h1>
    <p>Build a favorites list and start reviewing creators.</p>

    <?php foreach ($errors as $error): ?>
        <div class="form-error"><?= h($error) ?></div>
    <?php endforeach; ?>

    <form method="post" action="register.php">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= h($username) ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= h($email) ?>" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <div class="field-hint">At least 6 characters.</div>

        <label for="confirm_password">Confirm password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-block">Sign up</button>
        </div>
    </form>

    <p style="margin-top:20px; font-size:0.9rem;">Already have an account? <a href="login.php" style="color:var(--accent)">Log in</a></p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
