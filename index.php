  <?php
require_once __DIR__ . '/includes/functions.php';
$title = 'Trang chủ';

$new = db()->query('SELECT * FROM products ORDER BY created_at DESC, id DESC LIMIT 8')->fetchAll();
$cats = db()->query('SELECT c.*, COUNT(p.id) AS total FROM categories c LEFT JOIN products p ON p.category_id = c.id GROUP BY c.id ORDER BY c.name')->fetchAll();
$cheap = db()->query('SELECT * FROM products WHERE stock > 0 ORDER BY price ASC LIMIT 4')->fetchAll();

include __DIR__ . '/includes/header.php';

require_once __DIR__ . '/includes/product_card.php';
?>

<section class="hero">
  <h1>Khám phá thế giới qua từng trang sách</h1>
  <p>Hàng nghìn đầu sách từ văn học, kinh tế đến công nghệ với giá tốt mỗi ngày.</p>
  <a class="btn" href="<?= url('products.php') ?>">Xem tất cả sách</a>
</section>

<h2>Danh mục</h2>
<div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr))">
  <?php foreach ($cats as $c): ?>
    <a class="card" href="<?= url('products.php?cat=' . $c['id']) ?>">
      <b><?= e($c['name']) ?></b><br><span class="muted"><?= (int)$c['total'] ?> sách</span>
    </a>
  <?php endforeach; ?>
</div>

<h2>Sách mới</h2>
<div class="grid"><?php foreach ($new as $p) product_card($p); ?></div>

<h2>Giá mềm cho bạn</h2>
<div class="grid"><?php foreach ($cheap as $p) product_card($p); ?></div>

<?php include __DIR__ . '/includes/footer.php'; ?>
