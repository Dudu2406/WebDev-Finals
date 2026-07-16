<?php
$page_title = 'Admin — StreamWatch';
$in_subdir = true;
require_once __DIR__ . '/../includes/header.php';

require_admin();

$creators = $pdo->query(
    "SELECT c.*, COUNT(r.id) AS review_count
     FROM creators c
     LEFT JOIN reviews r ON r.creator_id = c.id
     GROUP BY c.id
     ORDER BY c.created_at DESC"
)->fetchAll();
?>

<section class="page-hero">
    <div class="eyebrow"><span class="live-dot"></span> Admin</div>
    <h1>Manage creators</h1>
    <p class="lede">Add, edit, or remove creator profiles. This is the only place creator info is entered — StreamWatch doesn't pull it from YouTube or Twitch automatically.</p>
</section>

<div class="admin-toolbar">
    <span style="color:var(--text-muted); font-family:var(--font-mono); font-size:0.85rem;"><?= count($creators) ?> creator<?= count($creators) == 1 ? '' : 's' ?> total</span>
    <a href="creator_form.php" class="btn btn-primary">+ Add creator</a>
</div>

<?php if (empty($creators)): ?>
    <div class="empty-state"><p>No creators yet. Add the first one.</p></div>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Content type</th>
                <th>Platform</th>
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
                    <td><?= (int)$creator['review_count'] ?></td>
                    <td class="actions">
                        <a href="creator_form.php?id=<?= (int)$creator['id'] ?>" class="btn btn-small">Edit</a>
                        <form method="post" action="creator_delete.php" class="inline" onsubmit="return confirm('Delete this creator? This also deletes their reviews and removes them from everyone\'s favorites.');">
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
