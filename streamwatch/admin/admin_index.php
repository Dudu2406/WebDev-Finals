<?php
$in_subdir = true;
require_once __DIR__ . '/../includes/header.php';

require_admin();

$page_title = 'Admin — Manage creators — StreamWatch';

$stmt = $pdo->query(
    "SELECT c.*,
            ROUND(AVG(r.rating), 1) AS avg_rating,
            COUNT(r.id) AS review_count
     FROM creators c
     LEFT JOIN reviews r ON r.creator_id = c.id
     GROUP BY c.id
     ORDER BY c.name ASC"
);
$creators = $stmt->fetchAll();
?>

<div class="admin-toolbar">
    <h1>Manage creators</h1>
    <a href="creator_form.php" class="btn btn-primary">+ Add creator</a>
</div>

<?php if (empty($creators)): ?>
    <div class="empty-state">
        <p>No creators yet. Add the first one to get started.</p>
    </div>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Content type</th>
                <th>Platform</th>
                <th>Rating</th>
                <th>Reviews</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($creators as $creator): ?>
                <tr>
                    <td><?= h($creator['name']) ?></td>
                    <td><?= h($creator['content_type']) ?></td>
                    <td><?= h($creator['platform'] ?: '—') ?></td>
                    <td><?= $creator['avg_rating'] !== null ? h((string)$creator['avg_rating']) : '—' ?></td>
                    <td><?= (int)$creator['review_count'] ?></td>
                    <td class="actions">
                        <a href="../creator.php?id=<?= (int)$creator['id'] ?>" class="btn btn-small">View</a>
                        <a href="creator_form.php?id=<?= (int)$creator['id'] ?>" class="btn btn-small">Edit</a>
                        <form method="post" action="creator_delete.php" onsubmit="return confirm('Delete <?= h($creator['name']) ?>? This also removes its reviews and favorites.');" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="id" value="<?= (int)$creator['id'] ?>">
                            <button type="submit" class="btn btn-small btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
