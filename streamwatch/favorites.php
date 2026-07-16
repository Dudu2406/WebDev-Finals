<?php
$page_title = 'My Favorites — StreamWatch';
require_once __DIR__ . '/includes/header.php';

require_login();

$stmt = $pdo->prepare(
    "SELECT c.*,
            ROUND(AVG(r.rating), 1) AS avg_rating,
            COUNT(DISTINCT r.id) AS review_count
     FROM favorites f
     JOIN creators c ON c.id = f.creator_id
     LEFT JOIN reviews r ON r.creator_id = c.id
     WHERE f.user_id = :user_id
     GROUP BY c.id
     ORDER BY f.created_at DESC"
);
$stmt->execute([':user_id' => current_user_id()]);
$favorites = $stmt->fetchAll();
?>

<section class="page-hero">
    <div class="eyebrow"><span class="live-dot"></span> Your list</div>
    <h1>My Favorites</h1>
    <p class="lede">Creators you're following. Check back for new reviews from the community.</p>
</section>

<?php if (empty($favorites)): ?>
    <div class="empty-state">
        <p>You haven't added any creators yet.</p>
        <p><a href="index.php" class="btn btn-primary" style="margin-top:10px;">Browse creators</a></p>
    </div>
<?php else: ?>
    <div class="fav-list">
        <?php foreach ($favorites as $creator): ?>
            <div class="fav-row">
                <div class="creator-avatar"><?= h(mb_substr($creator['name'], 0, 1)) ?></div>
                <div class="fav-row-info">
                    <h3><a href="creator.php?id=<?= (int)$creator['id'] ?>" style="color:var(--text);"><?= h($creator['name']) ?></a></h3>
                    <span class="tag"><?= h($creator['content_type']) ?></span>
                    <span style="font-family:var(--font-mono); font-size:0.8rem; color:var(--text-muted); margin-left:8px;">
                        <?= $creator['avg_rating'] ? '★ ' . $creator['avg_rating'] : 'No ratings yet' ?>
                        · <?= (int)$creator['review_count'] ?> review<?= $creator['review_count'] == 1 ? '' : 's' ?>
                    </span>
                </div>
                <form method="post" action="remove_favorite.php" class="inline">
                    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="creator_id" value="<?= (int)$creator['id'] ?>">
                    <input type="hidden" name="redirect" value="favorites">
                    <button type="submit" class="btn btn-small btn-danger">Remove</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
