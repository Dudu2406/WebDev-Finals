<?php
$page_title = 'StreamWatch — Track your favorite creators';
require_once __DIR__ . '/includes/header.php';

// --- Search & filter ---
$search   = trim($_GET['q'] ?? '');
$type     = trim($_GET['type'] ?? '');
$platform = trim($_GET['platform'] ?? '');

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
if ($platform !== '') {
    $sql .= " AND c.platform = :platform";
    $params[':platform'] = $platform;
}
$sql .= " GROUP BY c.id ORDER BY c.name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$creators = $stmt->fetchAll();

// Distinct content types for the filter dropdown
$types = $pdo->query("SELECT DISTINCT content_type FROM creators ORDER BY content_type")->fetchAll(PDO::FETCH_COLUMN);

// Distinct platforms for the filter dropdown (e.g. YouTube, Twitch, Kick)
$platforms = $pdo->query("SELECT DISTINCT platform FROM creators WHERE platform IS NOT NULL AND platform <> '' ORDER BY platform")->fetchAll(PDO::FETCH_COLUMN);

// A small deterministic color per content type, purely visual
$tag_colors = ['#7c5cff', '#ffb454', '#22c98e', '#ff8a5c', '#5cc8ff', '#ff5c9e'];
function tag_color(string $type, array $palette, array $allTypes): string {
    $i = array_search($type, $allTypes);
    return $palette[$i % count($palette)] ?? $palette[0];
}

// Brand-ish colors for known platforms, with a neutral fallback for anything else
function platform_color(string $platform): string {
    $known = [
        'youtube' => '#ff4d4d',
        'twitch'  => '#9146ff',
        'kick'    => '#53fc18',
        'both'    => '#5cc8ff',
    ];
    return $known[strtolower($platform)] ?? '#8a8fa3';
}

// Small inline glyphs (generic, not brand logos) so each platform tag is scannable at a glance
function platform_icon(string $platform): string {
    $icons = [
        // Play-button glyph
        'youtube' => '<svg viewBox="0 0 16 16" width="12" height="12" fill="currentColor" aria-hidden="true"><rect x="1" y="3" width="14" height="10" rx="3"/><path d="M6.5 5.8 L11 8 L6.5 10.2 Z" fill="#fff"/></svg>',
        // Speech-bubble glyph
        'twitch'  => '<svg viewBox="0 0 16 16" width="12" height="12" fill="currentColor" aria-hidden="true"><path d="M2 1 L2 10 L5 10 L5 13 L8 10 L13 10 L13 1 Z"/><rect x="6" y="3.5" width="1.4" height="4" fill="#fff"/><rect x="9" y="3.5" width="1.4" height="4" fill="#fff"/></svg>',
        // Lightning-bolt glyph
        'kick'    => '<svg viewBox="0 0 16 16" width="12" height="12" fill="currentColor" aria-hidden="true"><path d="M6 1 L2 9 L6 9 L5 15 L14 6 L9 6 L11 1 Z"/></svg>',
        // Two-tone glyph for creators who stream to more than one place
        'both'    => '<svg viewBox="0 0 16 16" width="12" height="12" fill="currentColor" aria-hidden="true"><circle cx="5" cy="8" r="4.2"/><circle cx="10.5" cy="8" r="4.2" fill-opacity="0.65"/></svg>',
    ];
    return $icons[strtolower($platform)] ?? '';
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
    <select name="platform">
        <option value="">All platforms</option>
        <?php foreach ($platforms as $p): ?>
            <option value="<?= h($p) ?>" <?= $platform === $p ? 'selected' : '' ?>><?= h($p) ?></option>
        <?php endforeach; ?>
    </select>
    <div class="filter-actions">
        <button type="submit" class="btn btn-primary">Filter</button>
        <?php if ($search || $type || $platform): ?>
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
                    <div class="creator-card-tags">
                        <span class="tag" style="--tag-color: <?= tag_color($creator['content_type'], $tag_colors, $types) ?>"><?= h($creator['content_type']) ?></span>
                        <?php if (!empty($creator['platform'])): ?>
                            <span class="tag tag-platform" style="--tag-color: <?= platform_color($creator['platform']) ?>">
                                <?= platform_icon($creator['platform']) ?><?= h($creator['platform']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
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