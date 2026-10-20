<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getPDO();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$isEdit = $id > 0;
$meme = ['title' => '', 'caption' => '', 'image_url' => 'assets/memes/meme-default.svg', 'alt_text' => '', 'category_id' => '', 'created_by' => 1, 'status' => 'published'];
$errors = [];
if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM memes WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $existing = $stmt->fetch();
    if (!$existing) {
        flash('error', 'ไม่พบมีมที่ต้องการแก้ไข');
        redirect('index.php');
    }
    $meme = $existing;
}
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$users = $pdo->query('SELECT id, display_name FROM users ORDER BY display_name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $meme['title'] = trim((string)($_POST['title'] ?? ''));
    $meme['caption'] = trim((string)($_POST['caption'] ?? ''));
    $meme['image_url'] = trim((string)($_POST['image_url'] ?? ''));
    $meme['alt_text'] = trim((string)($_POST['alt_text'] ?? ''));
    $meme['category_id'] = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT) ?: 0;
    $meme['created_by'] = filter_input(INPUT_POST, 'created_by', FILTER_VALIDATE_INT) ?: 0;
    $meme['status'] = (string)($_POST['status'] ?? 'published');

    if ($meme['title'] === '' || mb_strlen_safe($meme['title']) > 150) $errors[] = 'กรุณากรอกชื่อมีม (ไม่เกิน 150 ตัวอักษร)';
    if ($meme['caption'] === '' || mb_strlen_safe($meme['caption']) > 1000) $errors[] = 'กรุณากรอกคำบรรยาย (ไม่เกิน 1,000 ตัวอักษร)';
    if ($meme['image_url'] === '') $errors[] = 'กรุณาระบุที่อยู่รูปภาพ';
    $scheme = parse_url((string)$meme['image_url'], PHP_URL_SCHEME);
    if (!str_starts_with((string)$meme['image_url'], 'assets/') && !in_array(strtolower((string)$scheme), ['http', 'https'], true)) $errors[] = 'รูปภาพต้องเป็น URL ที่ขึ้นต้นด้วย http://, https:// หรือ assets/';
    if (!in_array((int)$meme['category_id'], array_map(static fn($v) => (int)$v['id'], $categories), true)) $errors[] = 'กรุณาเลือกหมวดหมู่';
    if (!in_array((int)$meme['created_by'], array_map(static fn($v) => (int)$v['id'], $users), true)) $errors[] = 'กรุณาเลือกผู้สร้าง';
    if (!in_array($meme['status'], ['published', 'draft'], true)) $errors[] = 'สถานะไม่ถูกต้อง';

    if (!$errors) {
        $values = [
            'title' => $meme['title'], 'caption' => $meme['caption'], 'image_url' => $meme['image_url'],
            'alt_text' => $meme['alt_text'], 'category_id' => (int)$meme['category_id'],
            'created_by' => (int)$meme['created_by'], 'status' => $meme['status'],
        ];
        if ($isEdit) {
            $values['id'] = $id;
            $stmt = $pdo->prepare('UPDATE memes SET title=:title, caption=:caption, image_url=:image_url, alt_text=:alt_text, category_id=:category_id, created_by=:created_by, status=:status, updated_at=CURRENT_TIMESTAMP WHERE id=:id');
            $stmt->execute($values);
            flash('success', 'บันทึกการแก้ไขมีมเรียบร้อยแล้ว');
        } else {
            $stmt = $pdo->prepare('INSERT INTO memes (title, caption, image_url, alt_text, category_id, created_by, status, view_count, like_count) VALUES (:title, :caption, :image_url, :alt_text, :category_id, :created_by, :status, 0, 0)');
            $stmt->execute($values);
            flash('success', 'เพิ่มมีมใหม่เรียบร้อยแล้ว');
        }
        redirect('index.php');
    }
}
$pageTitle = $isEdit ? 'แก้ไขมีม' : 'เพิ่มมีมใหม่';
require __DIR__ . '/includes/header.php';
function mb_strlen_safe(string $text): int { return function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text); }
?>
<section class="form-page">
  <a class="back-link" href="index.php">← กลับไปยังคลังมีม</a>
  <div class="form-intro"><div class="eyebrow muted-eyebrow">YOUR MEME, YOUR MOMENT</div><h1><?= $isEdit ? 'ปรับแต่งมีมของคุณ' : 'มาเพิ่มมีมกันเถอะ' ?> <span>✨</span></h1><p><?= $isEdit ? 'แก้ไขรายละเอียดให้ตรงใจ แล้วบันทึกการเปลี่ยนแปลง' : 'มีมดี ๆ ต้องแบ่งปัน กรอกรายละเอียดด้านล่างแล้วปล่อยความฮาได้เลย' ?></p></div>
  <?php if ($errors): ?><div class="alert alert-error"><strong>ตรวจสอบข้อมูลอีกครั้ง</strong><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <form class="meme-form" method="post" action="meme_form.php<?= $isEdit ? '?id=' . $id : '' ?>">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <div class="form-main"><div class="form-card"><div class="form-card-title"><span class="step-number">01</span><div><h2>ข้อมูลมีม</h2><p>ชื่อและคำบรรยายที่คนเห็นแล้วต้องยิ้ม</p></div></div>
      <label class="field-label" for="title">ชื่อมีม <span class="required">*</span></label><input class="field-input" id="title" name="title" maxlength="150" required value="<?= e((string)$meme['title']) ?>" placeholder="เช่น เมื่อวันจันทร์มาถึงอีกครั้ง">
      <label class="field-label" for="caption">คำบรรยาย <span class="required">*</span></label><textarea class="field-input field-textarea" id="caption" name="caption" maxlength="1000" required rows="4" placeholder="ใส่แคปชันโดน ๆ ของมีมนี้..."> <?= e((string)$meme['caption']) ?></textarea>
      <label class="field-label" for="image_url">URL รูปภาพ <span class="required">*</span></label><input class="field-input" id="image_url" name="image_url" required value="<?= e((string)$meme['image_url']) ?>" placeholder="assets/memes/meme-default.svg หรือ https://...">
      <label class="field-label" for="alt_text">ข้อความอธิบายรูปภาพ</label><input class="field-input" id="alt_text" name="alt_text" maxlength="255" value="<?= e((string)$meme['alt_text']) ?>" placeholder="อธิบายรูปภาพสั้น ๆ เพื่อการเข้าถึง">
    </div><div class="form-card"><div class="form-card-title"><span class="step-number">02</span><div><h2>จัดหมวดหมู่</h2><p>ช่วยให้ค้นหามีมได้ง่ายขึ้น</p></div></div>
      <label class="field-label" for="category_id">หมวดหมู่ <span class="required">*</span></label><select class="field-input" id="category_id" name="category_id" required><option value="">เลือกหมวดหมู่</option><?php foreach ($categories as $category): ?><option value="<?= (int)$category['id'] ?>" <?= (int)$meme['category_id'] === (int)$category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select>
      <label class="field-label" for="created_by">ผู้สร้าง</label><select class="field-input" id="created_by" name="created_by" required><?php foreach ($users as $user): ?><option value="<?= (int)$user['id'] ?>" <?= (int)$meme['created_by'] === (int)$user['id'] ? 'selected' : '' ?>><?= e($user['display_name']) ?></option><?php endforeach; ?></select>
      <label class="field-label" for="status">สถานะเผยแพร่</label><select class="field-input" id="status" name="status"><option value="published" <?= $meme['status'] === 'published' ? 'selected' : '' ?>>เผยแพร่</option><option value="draft" <?= $meme['status'] === 'draft' ? 'selected' : '' ?>>ฉบับร่าง</option></select>
    </div><div class="form-actions"><a class="button button-outline" href="index.php">ยกเลิก</a><button class="button button-primary" type="submit"><?= $isEdit ? 'บันทึกการแก้ไข' : 'บันทึกมีม' ?> <span>↗</span></button></div></div>
    <aside class="form-side"><div class="preview-card"><div class="preview-label"><span class="live-dot"></span> LIVE PREVIEW</div><div class="preview-art"><img id="image-preview" src="<?= e(safeImageUrl((string)$meme['image_url'])) ?>" alt="ตัวอย่างมีม"><div class="preview-gradient"><span id="preview-category">MEME HUB</span><strong id="preview-title"><?= e((string)($meme['title'] ?: 'ตัวอย่างชื่อมีม')) ?></strong></div></div><div class="preview-caption" id="preview-caption"><?= e((string)($meme['caption'] ?: 'คำบรรยายมีมของคุณจะแสดงที่นี่')) ?></div><div class="preview-like">♥ <span>พร้อมแชร์ความฮา</span></div></div><div class="tip-card"><span>💡</span><div><strong>เคล็ดลับเพิ่มความปัง</strong><p>ตั้งชื่อให้สั้น กระชับ และเลือกภาพที่สื่ออารมณ์ได้ชัดเจน</p></div></div></aside>
  </form>
</section>
<script>
const titleField=document.getElementById('title'), captionField=document.getElementById('caption'), imageField=document.getElementById('image_url');
titleField.addEventListener('input',()=>document.getElementById('preview-title').textContent=titleField.value||'ตัวอย่างชื่อมีม');
captionField.addEventListener('input',()=>document.getElementById('preview-caption').textContent=captionField.value||'คำบรรยายมีมของคุณจะแสดงที่นี่');
imageField.addEventListener('input',()=>{const value=imageField.value.trim();if(value.startsWith('assets/')||/^https?:\/\//i.test(value))document.getElementById('image-preview').src=value;});
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
