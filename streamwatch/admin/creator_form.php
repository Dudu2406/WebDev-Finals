<?php
$in_subdir = true;
require_once __DIR__ . '/../includes/header.php';

require_admin();

$editing = false;
$creator = ['name' => '', 'content_type' => '', 'platform' => '', 'description' => '', 'image_url' => ''];
$errors = [];

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM creators WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $found = $stmt->fetch();
    if ($found) {
        $creator = $found;
        $editing = true;
    }
}

$page_title = ($editing ? 'Edit' : 'Add') . ' creator — StreamWatch';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf_token'] ?? null);

    $creator['name']         = trim($_POST['name'] ?? '');
    $creator['content_type'] = trim($_POST['content_type'] ?? '');
    $creator['platform']     = trim($_POST['platform'] ?? '');
    $creator['description']  = trim($_POST['description'] ?? '');
    $creator['image_url']    = trim($_POST['image_url'] ?? '');

    if ($creator['name'] === '') {
        $errors[] = 'Name is required.';
    }
    if ($creator['content_type'] === '') {
        $errors[] = 'Content type is required.';
    }
    if ($creator['image_url'] !== '' && !filter_var($creator['image_url'], FILTER_VALIDATE_URL)) {
        $errors[] = 'Image URL must be a valid URL (or left blank).';
    }

    if (empty($errors)) {
        if ($editing) {
            $stmt = $pdo->prepare(
                "UPDATE creators SET name = :name, content_type = :type, platform = :platform,
                 description = :desc, image_url = :img WHERE id = :id"
            );
            $stmt->execute([
                ':name' => $creator['name'], ':type' => $creator['content_type'],
                ':platform' => $creator['platform'], ':desc' => $creator['description'],
                ':img' => $creator['image_url'] ?: null, ':id' => $id,
            ]);
            flash($creator['name'] . ' was updated.');
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO creators (name, content_type, platform, description, image_url)
                 VALUES (:name, :type, :platform, :desc, :img)"
            );
            $stmt->execute([
                ':name' => $creator['name'], ':type' => $creator['content_type'],
                ':platform' => $creator['platform'], ':desc' => $creator['description'],
                ':img' => $creator['image_url'] ?: null,
            ]);
            flash($creator['name'] . ' was added.');
        }
        header('Location: index.php');
        exit;
    }
}
?>

<div class="form-wide">
    <h1><?= $editing ? 'Edit creator' : 'Add a creator' ?></h1>

    <?php foreach ($errors as $error): ?>
        <div class="form-error"><?= h($error) ?></div>
    <?php endforeach; ?>

    <form method="post" action="creator_form.php">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int)$id ?>"><?php endif; ?>

        <label for="name">Channel name</label>
        <input type="text" id="name" name="name" value="<?= h($creator['name']) ?>" required>

        <label for="content_type">Content type</label>
        <input type="text" id="content_type" name="content_type" value="<?= h($creator['content_type']) ?>" placeholder="Gaming, Vlogs, Music, Education…" required>

        <label for="platform">Platform</label>
        <input type="text" id="platform" name="platform" value="<?= h($creator['platform']) ?>" placeholder="YouTube, Twitch, Both…">

        <label for="description">Description</label>
        <textarea id="description" name="description" placeholder="What kind of content do they make?"><?= h($creator['description']) ?></textarea>

        <label for="image_url">Image URL (optional)</label>
        <input type="url" id="image_url" name="image_url" value="<?= h($creator['image_url']) ?>" placeholder="https://…">
        <div class="field-hint">Leave blank to use the default avatar initial.</div>

        <div class="form-actions" style="display:flex; gap:10px;">
            <button type="submit" class="btn btn-primary"><?= $editing ? 'Save changes' : 'Add creator' ?></button>
            <a href="index.php" class="btn">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
