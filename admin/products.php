<?php
$title = 'Quản lý sách';
require_once __DIR__ . '/../includes/admin_header.php';
$pdo = db();
$cats = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($act === 'delete') {
        try {
            $st = $pdo->prepare('SELECT image FROM products WHERE id = ?');
            $st->execute([$id]);
            $img = $st->fetchColumn();
            $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
            if ($img && is_file(__DIR__ . '/../uploads/' . $img)) unlink(__DIR__ . '/../uploads/' . $img);
            flash('success', 'Đã xóa sách.');
        } catch (PDOException $ex) {
            flash('danger', 'Không thể xóa: sách đã có trong đơn hàng. Hãy đặt tồn kho về 0.');
        }
        redirect(url('admin/products.php'));
    }

    $data = [
        'category_id' => (int)($_POST['category_id'] ?? 0),
        'title' => trim($_POST['title'] ?? ''),
        'author' => trim($_POST['author'] ?? ''),
        'price' => $_POST['price'] ?? '',
        'stock' => $_POST['stock'] ?? '',
        'description' => trim($_POST['description'] ?? ''),
    ];
    if ($data['title'] === '') $errors['title'] = 'Nhập tên sách.';
    if ($data['author'] === '') $errors['author'] = 'Nhập tác giả.';
    if (!ctype_digit((string)$data['price']) || (int)$data['price'] <= 0) $errors['price'] = 'Giá phải là số nguyên dương.';
    if (!ctype_digit((string)$data['stock'])) $errors['stock'] = 'Tồn kho phải là số nguyên ≥ 0.';
    if (!in_array($data['category_id'], array_column($cats, 'id'))) $errors['category_id'] = 'Chọn danh mục.';

    $image = null;
    if (!$errors) {
        try {
            $image = save_upload('image');
        } catch (RuntimeException $ex) {
            $errors['image'] = $ex->getMessage();
        }
    }

    if (!$errors) {
        if ($act === 'create') {
            $pdo->prepare('INSERT INTO products (category_id,title,author,price,stock,description,image) VALUES (?,?,?,?,?,?,?)')
                ->execute([$data['category_id'], $data['title'], $data['author'], $data['price'], $data['stock'], $data['description'], $image]);
            flash('success', 'Đã thêm sách.');
        } else {
            if ($image) {
                $st = $pdo->prepare('SELECT image FROM products WHERE id = ?');
                $st->execute([$id]);
                $old = $st->fetchColumn();
                if ($old && is_file(__DIR__ . '/../uploads/' . $old)) unlink(__DIR__ . '/../uploads/' . $old);
            }
            $pdo->prepare('UPDATE products SET category_id=?, title=?, author=?, price=?, stock=?, description=?, image=COALESCE(?, image) WHERE id=?')
                ->execute([$data['category_id'], $data['title'], $data['author'], $data['price'], $data['stock'], $data['description'], $image, $id]);
            flash('success', 'Đã cập nhật sách.');
        }
        redirect(url('admin/products.php'));
    }
    $formData = $data + ['id' => $id, 'image' => null];
}

$edit = $formData ?? null;
$showForm = isset($_GET['new']) || isset($_GET['edit']) || $errors;
if (!$edit && isset($_GET['edit'])) {
    $st = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch() ?: null;
}

$q = trim($_GET['q'] ?? '');
$cat = (int)($_GET['cat'] ?? 0);
$per = 8;
$page = max(1, (int)($_GET['page'] ?? 1));

$where = '(p.title LIKE ? OR p.author LIKE ?)';
$args = ["%$q%", "%$q%"];
if ($cat > 0) {
    $where .= ' AND p.category_id = ?';
    $args[] = $cat;
}

$st = $pdo->prepare("SELECT COUNT(*) FROM products p WHERE $where");
$st->execute($args);
$total = (int)$st->fetchColumn();
$pages = max(1, (int)ceil($total / $per));
$page = min($page, $pages);

$st = $pdo->prepare("SELECT p.*, c.name AS cat_name FROM products p JOIN categories c ON c.id = p.category_id WHERE $where ORDER BY p.id DESC LIMIT $per OFFSET " . (($page - 1) * $per));
$st->execute($args);
$products = $st->fetchAll();

function page_url(int $i): string
{
    global $q, $cat;
    return '?' . http_build_query(array_filter(['q' => $q, 'cat' => $cat, 'page' => $i]));
}
function aerr(array $e, string $k): string
{
    return isset($e[$k]) ? '<div class="error">' . e($e[$k]) . '</div>' : '';
}
?>
<h1>Quản lý sách</h1>

<?php if ($showForm): ?>
<form class="card" method="post" enctype="multipart/form-data" style="margin-bottom:24px" novalidate>
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="<?= !empty($edit['id']) ? 'update' : 'create' ?>">
  <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
  <h2 style="margin-top:0"><?= !empty($edit['id']) ? 'Sửa sách' : 'Thêm sách mới' ?></h2>
  <div class="two-col" style="grid-template-columns:1fr 1fr">
    <div>
      <div class="form-group"><label for="title">Tên sách</label>
        <input id="title" type="text" name="title" value="<?= e($edit['title'] ?? '') ?>"><?= aerr($errors, 'title') ?></div>
      <div class="form-group"><label for="author">Tác giả</label>
        <input id="author" type="text" name="author" value="<?= e($edit['author'] ?? '') ?>"><?= aerr($errors, 'author') ?></div>
      <div class="form-group"><label for="category_id">Danh mục</label>
        <select id="category_id" name="category_id">
          <option value="">-- Chọn --</option>
          <?php foreach ($cats as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= ($edit['category_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select><?= aerr($errors, 'category_id') ?></div>
    </div>
    <div>
      <div class="form-group"><label for="price">Giá (₫)</label>
        <input id="price" type="number" name="price" min="0" value="<?= e($edit['price'] ?? '') ?>"><?= aerr($errors, 'price') ?></div>
      <div class="form-group"><label for="stock">Tồn kho</label>
        <input id="stock" type="number" name="stock" min="0" value="<?= e($edit['stock'] ?? '0') ?>"><?= aerr($errors, 'stock') ?></div>
      <div class="form-group"><label for="image">Ảnh bìa (JPG/PNG/WEBP, ≤ 2MB)</label>
        <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp"><?= aerr($errors, 'image') ?>
        <?php if (!empty($edit['image'])): ?><img class="thumb" style="margin-top:8px;width:60px" src="<?= e(cover_url($edit['image'])) ?>" alt=""><?php endif; ?></div>
    </div>
  </div>
  <div class="form-group"><label for="description">Mô tả</label>
    <textarea id="description" name="description"><?= e($edit['description'] ?? '') ?></textarea></div>
  <button class="btn">Lưu</button> <a class="btn btn-outline" href="products.php">Hủy</a>
</form>
<?php endif; ?>

<div class="toolbar">
    <form method="get" class="search" style="max-width:560px">
    <input type="search" name="q" placeholder="Tìm theo tên / tác giả" value="<?= e($q) ?>">
    <select name="cat" style="width:auto;border-radius:0">
      <option value="0">Tất cả danh mục</option>
      <?php foreach ($cats as $c): ?>
        <option value="<?= (int)$c['id'] ?>" <?= $cat === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <button>Tìm</button>
  </form>
  <a class="btn" href="?new=1">+ Thêm sách</a>
</div>
<div class="table-wrap"><table>
  <thead><tr><th></th><th>Tên sách</th><th>Danh mục</th><th>Giá</th><th>Tồn</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($products as $p): ?>
    <tr>
      <td><img class="thumb" src="<?= e(cover_url($p['image'])) ?>" alt=""></td>
      <td><b><?= e($p['title']) ?></b><br><span class="muted"><?= e($p['author']) ?></span></td>
      <td><?= e($p['cat_name']) ?></td><td><?= money($p['price']) ?></td><td><?= (int)$p['stock'] ?></td>
      <td style="white-space:nowrap">
        <a class="btn btn-sm btn-outline" href="?edit=<?= (int)$p['id'] ?>">Sửa</a>
        <form class="inline-form" method="post" onsubmit="return confirm('Xóa sách này?')">
          <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
          <button class="btn btn-sm btn-danger">Xóa</button>
        </form></td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$products): ?><tr><td colspan="6" class="muted">Không có sách nào.</td></tr><?php endif; ?>
  </tbody></table></div>
  <?php if ($pages > 1): ?>
<nav class="pager">
  <?php for ($i = 1; $i <= $pages; $i++): ?>
    <?php if ($i === $page): ?><span class="cur"><?= $i ?></span>
    <?php else: ?><a href="<?= page_url($i) ?>"><?= $i ?></a><?php endif; ?>
  <?php endfor; ?>
</nav>
<?php endif; ?>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
