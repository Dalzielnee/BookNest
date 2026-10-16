<?php
$title = 'Danh mục';
require_once __DIR__ . '/../includes/admin_header.php';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    try {
        if ($act === 'delete') {
            $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
            flash('success', 'Đã xóa danh mục.');
        } elseif ($name === '') {
            flash('danger', 'Tên danh mục không được để trống.');
        } elseif ($act === 'create') {
            $pdo->prepare('INSERT INTO categories (name, description) VALUES (?,?)')->execute([$name, $desc ?: null]);
            flash('success', 'Đã thêm danh mục.');
        } elseif ($act === 'update') {
            $pdo->prepare('UPDATE categories SET name = ?, description = ? WHERE id = ?')->execute([$name, $desc ?: null, $id]);
            flash('success', 'Đã cập nhật danh mục.');
        }
    } catch (PDOException $ex) {
        // 1062: trùng tên, 1451: còn sách thuộc danh mục
        $code = $ex->errorInfo[1] ?? 0;
        flash('danger', $code == 1451 ? 'Không thể xóa: danh mục còn chứa sách.' : ($code == 1062 ? 'Tên danh mục đã tồn tại.' : 'Có lỗi xảy ra.'));
    }
    redirect(url('admin/categories.php'));
}

$edit = null;
if (isset($_GET['edit'])) {
    $st = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch() ?: null;
}
$cats = $pdo->query('SELECT c.*, COUNT(p.id) AS total FROM categories c LEFT JOIN products p ON p.category_id = c.id GROUP BY c.id ORDER BY c.name')->fetchAll();
?>
<h1>Quản lý danh mục</h1>
<div class="two-col">
  <div class="table-wrap"><table>
    <thead><tr><th>Tên</th><th>Mô tả</th><th>Số sách</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($cats as $c): ?>
      <tr><td><b><?= e($c['name']) ?></b></td><td class="muted"><?= e($c['description']) ?></td><td><?= (int)$c['total'] ?></td>
        <td style="white-space:nowrap">
          <a class="btn btn-sm btn-outline" href="?edit=<?= (int)$c['id'] ?>">Sửa</a>
          <form class="inline-form" method="post" onsubmit="return confirm('Xóa danh mục này?')">
            <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
            <button class="btn btn-sm btn-danger">Xóa</button>
          </form></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>

  <form class="card" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <h2 style="margin-top:0"><?= $edit ? 'Sửa danh mục' : 'Thêm danh mục' ?></h2>
    <div class="form-group"><label for="name">Tên danh mục</label>
      <input id="name" type="text" name="name" value="<?= e($edit['name'] ?? '') ?>" required></div>
    <div class="form-group"><label for="description">Mô tả</label>
      <input id="description" type="text" name="description" value="<?= e($edit['description'] ?? '') ?>"></div>
    <button class="btn"><?= $edit ? 'Lưu' : 'Thêm' ?></button>
    <?php if ($edit): ?><a class="btn btn-outline" href="categories.php">Hủy</a><?php endif; ?>
  </form>
</div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
