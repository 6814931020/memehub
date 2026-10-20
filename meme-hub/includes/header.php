<?php
$pageTitle = $pageTitle ?? 'Meme Hub';
$activePage = $activePage ?? '';
$notice = consumeFlash();
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> | Meme Hub</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="index.php" aria-label="Meme Hub หน้าแรก"><span class="brand-mark">M</span><span>Meme<span class="brand-accent">Hub</span><small>YOUR DAILY DOSE OF LOL</small></span></a>
  <nav class="main-nav" aria-label="เมนูหลัก">
    <a class="<?= $activePage === 'home' ? 'active' : '' ?>" href="index.php">สำรวจมีม</a>
    <a href="index.php?sort=popular">ยอดนิยม</a>
  </nav>
  <a class="button button-primary top-action" href="meme_form.php"><span class="plus-icon">+</span> เพิ่มมีมใหม่</a>
</header>
<main class="container">
<?php if ($notice): ?>
  <div class="alert alert-<?= e($notice['type']) ?>" role="status"><?= e($notice['message']) ?></div>
<?php endif; ?>
