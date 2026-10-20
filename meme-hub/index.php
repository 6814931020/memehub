<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'คลังมีมทั้งหมด';
$activePage = 'home';
$errorMessage = null;
try {
    $pdo = getPDO();
    $categories = $pdo->query('SELECT id, name, slug, color_hex FROM categories ORDER BY name')->fetchAll();

    $search = trim((string)($_GET['q'] ?? ''));
    $categoryId = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT) ?: 0;
    $sort = (string)($_GET['sort'] ?? 'newest');
    $orderBy = $sort === 'popular' ? 'm.like_count DESC, m.created_at DESC' : 'm.created_at DESC, m.id DESC';

    $where = ["m.status = 'published'"];
    $params = [];
    if ($search !== '') {
        $where[] = '(m.title LIKE :search_title OR m.caption LIKE :search_caption OR c.name LIKE :search_category)';
        $params['search_title'] = '%' . $search . '%';
        $params['search_caption'] = '%' . $search . '%';
        $params['search_category'] = '%' . $search . '%';
    }
    if ($categoryId > 0) {
        $where[] = 'm.category_id = :category_id';
        $params['category_id'] = $categoryId;
    }
    $whereSql = implode(' AND ', $where);

    $stmt = $pdo->prepare("SELECT m.*, c.name AS category_name, c.color_hex, u.display_name AS creator_name
        FROM memes m
        JOIN categories c ON c.id = m.category_id
        JOIN users u ON u.id = m.created_by
        WHERE {$whereSql} ORDER BY {$orderBy}");
    $stmt->execute($params);
    $memes = $stmt->fetchAll();

    $totalMemes = (int)$pdo->query("SELECT COUNT(*) FROM memes WHERE status = 'published'")->fetchColumn();
    $totalLikes = (int)$pdo->query("SELECT COALESCE(SUM(like_count), 0) FROM memes WHERE status = 'published'")->fetchColumn();
    $totalCategories = count($categories);
} catch (Throwable $exception) {
    http_response_code(503);
    $errorMessage = 'ยังเชื่อมต่อฐานข้อมูลไม่ได้ กรุณาสร้างฐานข้อมูลและนำเข้าไฟล์ database/schema.sql จากนั้นตรวจสอบค่า DB_HOST, DB_NAME, DB_USER และ DB_PASS ใน config/database.php';
    $categories = $memes = [];
    $search = trim((string)($_GET['q'] ?? ''));
    $categoryId = 0;
    $sort = 'newest';
    $totalMemes = $totalLikes = $totalCategories = 0;
}
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div class="hero-copy">
    <div class="eyebrow"><span class="eyebrow-dot"></span> THE INTERNET'S HAPPY PLACE</div>
    <h1>โลกนี้มีเรื่องเครียดเยอะแล้ว<br><span>มาแชร์มีมกันดีกว่า</span> ✨</h1>
    <p>คลังรวมมีมฮา ๆ จากทุกโมเมนต์ในชีวิต เติมเสียงหัวเราะให้วันธรรมดาของคุณ</p>
    <a class="button button-light" href="meme_form.php">แชร์มีมของคุณ <span>↗</span></a>
  </div>
  <div class="hero-art" aria-hidden="true"><div class="art-orbit orbit-one"></div><div class="art-orbit orbit-two"></div><div class="hero-emoji emoji-one">😂</div><div class="hero-emoji emoji-two">🤡</div><div class="hero-emoji emoji-three">💀</div><div class="hero-sticker">IT'S<br>MEME<br>TIME!</div><div class="sparkle sparkle-one">✦</div><div class="sparkle sparkle-two">✧</div></div>
</section>
<?php if ($errorMessage): ?>
  <div class="db-warning"><strong>ยังไม่พร้อมใช้งาน</strong><p><?= e($errorMessage) ?></p><code>mysql -u root -p &lt; database/schema.sql</code></div>
<?php else: ?>
<section class="stats-row" aria-label="สถิติคลังมีม">
  <div class="stat-item"><span class="stat-icon stat-purple">▧</span><div><strong><?= number_format($totalMemes) ?></strong><span>มีมในคลัง</span></div></div>
  <div class="stat-item"><span class="stat-icon stat-pink">♥</span><div><strong><?= number_format($totalLikes) ?></strong><span>ยอดฮารวม (likes)</span></div></div>
  <div class="stat-item"><span class="stat-icon stat-yellow">✦</span><div><strong><?= number_format($totalCategories) ?></strong><span>หมวดหมู่</span></div></div>
</section>
<section class="content-section">
  <div class="section-heading"><div><div class="eyebrow muted-eyebrow">MEME COLLECTION</div><h2>มีมที่กำลังฮิต <span class="heading-emoji">🔥</span></h2><p>เลือกมีมที่ตรงกับอารมณ์ของคุณวันนี้</p></div><span class="result-count"><?= count($memes) ?> รายการ</span></div>
  <form class="filter-bar" method="get" action="index.php">
    <label class="search-box"><span>⌕</span><input type="search" name="q" value="<?= e($search) ?>" placeholder="ค้นหามีมที่ใช่..."><button type="submit">ค้นหา</button></label>
    <label class="category-select"><span>หมวดหมู่</span><select name="category" onchange="this.form.submit()"><option value="0">ทั้งหมด</option><?php foreach ($categories as $category): ?><option value="<?= (int)$category['id'] ?>" <?= $categoryId === (int)$category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select></label>
    <input type="hidden" name="sort" value="<?= e($sort) ?>">
  </form>
  <?php if (!$memes): ?><div class="empty-state"><span>🫠</span><h3>ยังไม่เจอมีมที่ตามหา</h3><p>ลองเปลี่ยนคำค้นหา หรือเพิ่มมีมใหม่เข้าคลังได้เลย</p><a class="button button-primary" href="meme_form.php">+ เพิ่มมีมแรก</a></div><?php else: ?>
  <div class="meme-grid">
    <?php foreach ($memes as $meme): ?>
      <article class="meme-card">
        <div class="meme-image-wrap"><img class="meme-image" src="<?= e(safeImageUrl((string)$meme['image_url'])) ?>" alt="<?= e($meme['alt_text'] ?: $meme['title']) ?>" loading="lazy"><span class="category-chip" style="--chip-color: <?= e($meme['color_hex']) ?>"><?= e($meme['category_name']) ?></span><span class="image-menu">MEME #<?= (int)$meme['id'] ?></span></div>
        <div class="meme-card-body"><h3><?= e($meme['title']) ?></h3><p class="meme-caption"><?= e($meme['caption']) ?></p><div class="card-meta"><span class="creator-avatar"><?= e(mb_substr_safe((string)$meme['creator_name'])) ?></span><span class="creator-name"><?= e($meme['creator_name']) ?></span><span class="meta-spacer"></span><span class="likes">♥ <?= number_format((int)$meme['like_count']) ?></span></div><div class="card-actions"><a class="text-link" href="meme_form.php?id=<?= (int)$meme['id'] ?>">แก้ไข <span>↗</span></a><form action="delete_meme.php" method="post" onsubmit="return confirm('ต้องการลบมีมนี้ใช่หรือไม่?');"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="id" value="<?= (int)$meme['id'] ?>"><button class="delete-link" type="submit">ลบ</button></form></div></div>
      </article>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
<?php endif; ?>
<?php
// Multibyte-safe initial without requiring ext-mbstring.
function mb_substr_safe(string $text): string { return function_exists('mb_substr') ? mb_substr($text, 0, 1, 'UTF-8') : strtoupper(substr($text, 0, 1)); }
require __DIR__ . '/includes/footer.php';
