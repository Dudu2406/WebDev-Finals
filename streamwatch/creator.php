<?php
require_once __DIR__ . '/includes/header.php';

$creator_id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM creators WHERE id = :id");
$stmt->execute([':id' => $creator_id]);
$creator = $stmt->fetch();

if (!$creator) {
    http_response_code(404);
    echo '<div class="empty-state"><p>That creator doesn\'t exist. <a href="index.php" style="color:var(--accent)">Back to browse</a></p></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$page_title = $creator['name'] . ' — StreamWatch';

// Reviews + reviewer names
$reviewStmt = $pdo->prepare(
    "SELECT r.*, u.username FROM reviews r
     JOIN users u ON u.id = r.user_id
     WHERE r.creator_id = :id
     ORDER BY r.created_at DESC"
);
$reviewStmt->execute([':id' => $creator_id]);
$reviews = $reviewStmt->fetchAll();

$review_count = count($reviews);
$avg_rating = $review_count ? round(array_sum(array_column($reviews, 'rating')) / $review_count, 1) : 0;

// Does the current user already have a review here?
$my_review = null;
if (is_logged_in()) {
    foreach ($reviews as $r) {
        if ((int)$r['user_id'] === current_user_id()) { $my_review = $r; break; }
    }
}

// Is this creator already in the current user's favorites?
$is_favorited = false;
if (is_logged_in()) {
    $favStmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = :u AND creator_id = :c");
    $favStmt->execute([':u' => current_user_id(), ':c' => $creator_id]);
    $is_favorited = (bool)$favStmt->fetch();
}
?>

<div class="profile-head">
    <?php if (!empty($creator['image_url'])): ?>
        <img src="<?= h($creator['image_url']) ?>" alt="<?= h($creator['name']) ?>" class="profile-avatar-img">
    <?php else: ?>
        <div class="profile-avatar"><?= h(mb_substr($creator['name'], 0, 1)) ?></div>
    <?php endif; ?>
    <div class="profile-info">
        <span class="tag"><?= h($creator['content_type']) ?></span>
        <h1><?= h($creator['name']) ?></h1>
        <p><?= h($creator['description']) ?: 'No description yet.' ?></p>

        <div class="profile-stats">
            <div><strong><?= $avg_rating ?: '—' ?></strong>avg rating</div>
            <div><strong><?= $review_count ?></strong>review<?= $review_count == 1 ? '' : 's' ?></div>
            <?php if ($creator['platform']): ?>
                <div><strong style="font-size:1rem;"><?= h($creator['platform']) ?></strong>platform</div>
            <?php endif; ?>
        </div>

        <div class="profile-actions">
            <?php if (is_logged_in()): ?>
                <form method="post" action="<?= $is_favorited ? 'remove_favorite.php' : 'add_favorite.php' ?>" class="inline">
                    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="creator_id" value="<?= (int)$creator['id'] ?>">
                    <?php if ($is_favorited): ?>
                        <button type="submit" class="btn btn-danger">− Remove from favorites</button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-primary">+ Add to favorites</button>
                    <?php endif; ?>
                </form>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary">Log in to add to favorites</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<h2>Reviews</h2>

<?php if (is_logged_in()): ?>
    <div class="review-form">
        <h3 style="margin-bottom:4px;"><?= $my_review ? 'Update your review' : 'Leave a review' ?></h3>
        <p style="margin-bottom:16px;">Ratings and reviews help other viewers decide what to watch next.</p>
        <form method="post" action="review_submit.php">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="creator_id" value="<?= (int)$creator['id'] ?>">

            <label>Your rating</label>
            <div class="star-picker">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>"
                        <?= ($my_review && (int)$my_review['rating'] === $i) ? 'checked' : '' ?> required>
                    <label for="star<?= $i ?>">★</label>
                <?php endfor; ?>
            </div>

            <label for="review_text">Your review</label>
            <textarea id="review_text" name="review_text" placeholder="What do you like about their content?"><?= h($my_review['review_text'] ?? '') ?></textarea>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $my_review ? 'Update review' : 'Post review' ?></button>
            </div>
        </form>
    </div>
<?php else: ?>
    <p><a href="login.php" style="color:var(--accent)">Log in</a> to leave a review.</p>
<?php endif; ?>

<?php if (empty($reviews)): ?>
    <div class="empty-state"><p>No reviews yet — be the first to share your thoughts.</p></div>
<?php else: ?>
    <div class="review-list">
        <?php foreach ($reviews as $review): ?>
            <div class="review-card">
                <div class="review-card-head">
                    <div>
                        <span class="review-author"><?= h($review['username']) ?></span>
                        <span class="stars" style="margin-left:8px;">
                            <?php for ($i = 1; $i <= 5; $i++) echo $i <= $review['rating'] ? '★' : '<span class="dim">★</span>'; ?>
                        </span>
                    </div>
                    <span class="review-date"><?= date('M j, Y', strtotime($review['created_at'])) ?></span>
                </div>
                <?php if ($review['review_text']): ?>
                    <p class="review-text"><?= nl2br(h($review['review_text'])) ?></p>
                <?php endif; ?>

                <?php if (is_logged_in() && (int)$review['user_id'] === current_user_id()): ?>
                    <div class="review-actions">
                        <form method="post" action="review_delete.php" class="inline" onsubmit="return confirm('Delete your review?');">
                            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="review_id" value="<?= (int)$review['id'] ?>">
                            <input type="hidden" name="creator_id" value="<?= (int)$creator['id'] ?>">
                            <button type="submit" class="btn btn-small btn-danger">Delete my review</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
