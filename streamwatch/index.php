<?php
$page_title = 'StreamWatch — Track your favorite creators';
require_once __DIR__ . '/includes/header.php';

// --- Search & filter ---
$search  = trim($_GET['q'] ?? '');
$type    = trim($_GET['type'] ?? '');

$sql = "SELECT c.*,
               ROUND(AVG(r.rating), 1) AS avg_rating,
               COUNT(r.id) AS review_count
        FROM creators c
        LEFT JOIN reviews r ON r.creator_id = c.id
        WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND c.name LIKE :search";
    $params[':search'] = '%' . $search . '%';
}
if ($type !== '') {
    $sql .= " AND c.content_type = :type";
    $params[':type'] = $type;
}
$sql .= " GROUP BY c.id ORDER BY c.name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$creators = $stmt->fetchAll();

// Distinct content types for the filter dropdown
$types = $pdo->query("SELECT DISTINCT content_type FROM creators ORDER BY content_type")->fetchAll(PDO::FETCH_COLUMN);

// A small deterministic color per content type, purely visual
$tag_colors = ['#7c5cff', '#ffb454', '#22c98e', '#ff8a5c', '#5cc8ff', '#ff5c9e'];
function tag_color(string $type, array $palette, array $allTypes): string {
    $i = array_search($type, $allTypes);
    return $palette[$i % count($palette)] ?? $palette[0];
}
?>

<section class="page-hero">
    <div class="eyebrow"><span class="live-dot"></span> Creator directory</div>
    <h1>Never miss when they go live.</h1>
    <p class="lede">Keep a running list of the streamers and YouTubers you follow, see what other viewers think, and leave your own rating.</p>
</section>

<form class="filter-bar" method="get" action="index.php">
    <input type="text" name="q" placeholder="Search by creator name…" value="<?= h($search) ?>">
    <select name="type">
        <option value="">All content types</option>
        <?php foreach ($types as $t): ?>
            <option value="<?= h($t) ?>" <?= $type === $t ? 'selected' : '' ?>><?= h($t) ?></option>
        <?php endforeach; ?>
    </select>
    <div class="filter-actions">
        <button type="submit" class="btn btn-primary">Filter</button>
        <?php if ($search || $type): ?>
            <a href="index.php" class="btn btn-small">Clear</a>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($creators)): ?>
    <div class="empty-state">
        <p>No creators match that search.</p>
    </div>
<?php else: ?>
    <div class="creator-grid">
        <?php foreach ($creators as $creator): ?>
            <a class="creator-card" style="--tag-color: <?= tag_color($creator['content_type'], $tag_colors, $types) ?>"
               href="creator.php?id=<?= (int)$creator['id'] ?>">
                <div class="creator-card-top">
                    <div class="creator-avatar"><?= h(mb_substr($creator['name'], 0, 1)) ?></div>
                    <span class="tag" style="--tag-color: <?= tag_color($creator['content_type'], $tag_colors, $types) ?>"><?= h($creator['content_type']) ?></span>
                </div>
                <h3><?= h($creator['name']) ?></h3>
                <p class="creator-desc"><?= h($creator['description']) ?></p>
                <div class="creator-meta">
                    <span class="stars">
                        <?php
                        $avg = round($creator['avg_rating'] ?? 0);
                        for ($i = 1; $i <= 5; $i++) echo $i <= $avg ? '★' : '<span class="dim">★</span>';
                        ?>
                    </span>
                    <span><?= (int)$creator['review_count'] ?> review<?= $creator['review_count'] == 1 ? '' : 's' ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
