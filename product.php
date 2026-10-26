<?php
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare('SELECT p.*, c.name AS cat_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.id = ?');
$st->execute([$id]);
$p = $st->fetch();
if (!$p) {
    http_response_code(404);
    $title = 'Không tìm thấy';
    include __DIR__ . '/includes/header.php';
    echo '<div class="card">Không tìm thấy sách này. <a href="' . url('products.php') . '">Quay lại danh sách</a></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Ưu tiên sách cùng tác giả, bỏ sách hết hàng, còn lại lấy ngẫu nhiên cùng thể loại
$st = db()->prepare('SELECT * FROM products WHERE category_id = ? AND id <> ? AND stock > 0 ORDER BY (author = ?) DESC, RAND() LIMIT 4');
$st->execute([$p['category_id'], $p['id'], $p['author']]);
$related = $st->fetchAll();

$title = $p['title'];
include __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/product_card.php';
?>
<p class="muted"><a href="<?= url('products.php') ?>">Sách</a> / <a href="<?= url('products.php?cat=' . $p['category_id']) ?>"><?= e($p['cat_name']) ?></a></p>
<div class="detail">
  <img src="<?= e(cover_url($p['image'])) ?>" alt="<?= e($p['title']) ?>">
  <div>
    <h1><?= e($p['title']) ?></h1>
    <p class="muted">Tác giả: <b><?= e($p['author']) ?></b> · Thể loại: <?= e($p['cat_name']) ?></p>
    <p class="price"><?= money($p['price']) ?></p>
    <p><?= $p['stock'] > 0 ? 'Còn <b>' . (int)$p['stock'] . '</b> cuốn' : '<span class="error">Hết hàng</span>' ?></p>
    <?php if ($p['stock'] > 0): ?>
    <form action="<?= url('cart.php') ?>" method="post" style="display:flex;gap:10px;align-items:center">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add">
      <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
      <input class="qty" type="number" name="qty" value="1" min="1" max="<?= (int)$p['stock'] ?>">
      <button class="btn">Thêm vào giỏ hàng</button>
    </form>
    <?php endif; ?>
    <h2>Giới thiệu</h2>
    <p><?= nl2br(e($p['description'])) ?></p>
  </div>
</div>

<?php if ($related): ?>
<h2>Sách cùng thể loại</h2>
<div class="grid"><?php foreach ($related as $r) product_card($r); ?></div>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
