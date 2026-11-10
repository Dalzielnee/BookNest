<?php
require_once __DIR__ . '/includes/functions.php';
$cfg = require __DIR__ . '/config.php';
$title = 'Danh sách sách';

$q = trim($_GET['q'] ?? '');
$cat = (int)($_GET['cat'] ?? 0);
$sort = $_GET['sort'] ?? 'new';
$page = max(1, (int)($_GET['page'] ?? 1));
$per = $cfg['per_page'];

$where = [];
$args = [];
if ($q !== '') {
    $where[] = '(p.title LIKE ? OR p.author LIKE ?)';
    $args[] = "%$q%";
    $args[] = "%$q%";
}
if ($cat > 0) {
    $where[] = 'p.category_id = ?';
    $args[] = $cat;
}
$w = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$order = ['new' => 'p.id DESC', 'price_asc' => 'p.price ASC', 'price_desc' => 'p.price DESC', 'name' => 'p.title ASC'][$sort] ?? 'p.id DESC';

$st = db()->prepare("SELECT COUNT(*) FROM products p $w");
$st->execute($args);
$total = (int)$st->fetchColumn();
$pages = max(1, (int)ceil($total / $per));
$page = min($page, $pages);

$st = db()->prepare("SELECT p.* FROM products p $w ORDER BY $order LIMIT $per OFFSET " . (($page - 1) * $per));
$st->execute($args);
$products = $st->fetchAll();
$cats = db()->query('SELECT * FROM categories ORDER BY name')->fetchAll();

function qs(array $over): string
{
    return '?' . http_build_query(array_filter(array_merge($_GET, $over), fn($v) => $v !== '' && $v !== 0 && $v !== null));
}

include __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/product_card.php';
?>
<div class="shop">
  <aside class="sidebar">
    <div class="card">
      <b>Danh mục</b>
      <a href="<?= qs(['cat' => 0, 'page' => 1]) ?>" class="<?= !$cat ? 'active' : '' ?>">Tất cả</a>
      <?php foreach ($cats as $c): ?>
        <a href="<?= qs(['cat' => $c['id'], 'page' => 1]) ?>" class="<?= $cat == $c['id'] ? 'active' : '' ?>"><?= e($c['name']) ?></a>
      <?php endforeach; ?>
    </div>
  </aside>
  <section>
    <div class="toolbar">
      <h1 style="margin:0">
        <?= $q !== '' ? 'Kết quả cho "' . e($q) . '"' : 'Tất cả sách' ?>
        <small class="muted">(<?= $total ?>)</small>
      </h1>
      <form method="get">
        <?php foreach (['q' => $q, 'cat' => $cat] as $k => $v) if ($v) echo '<input type="hidden" name="' . $k . '" value="' . e($v) . '">'; ?>
        <select name="sort" onchange="this.form.submit()">
          <option value="new" <?= $sort === 'new' ? 'selected' : '' ?>>Mới nhất</option>
          <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Giá tăng dần</option>
          <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Giá giảm dần</option>
          <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Tên A-Z</option>
        </select>
      </form>
    </div>

    <?php if (!$products): ?>
      <div class="card">Không tìm thấy sách phù hợp.</div>
    <?php else: ?>
      <div class="grid"><?php foreach ($products as $p) product_card($p); ?></div>
      <?php if ($pages > 1): ?>
      <nav class="pager">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
          <?php if ($i === $page): ?><span class="cur"><?= $i ?></span>
          <?php else: ?><a href="<?= qs(['page' => $i]) ?>"><?= $i ?></a><?php endif; ?>
        <?php endfor; ?>
      </nav>
      <?php endif; ?>
    <?php endif; ?>
  </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
