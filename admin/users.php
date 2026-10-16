<?php
$title = 'Người dùng';
require_once __DIR__ . '/../includes/admin_header.php';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $role = $_POST['role'] ?? '';
    if ($id === (int)$admin['id']) {
        flash('danger', 'Bạn không thể tự đổi quyền của chính mình.');
    } elseif (in_array($role, ['user', 'admin'], true)) {
        $pdo->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$role, $id]);
        flash('success', 'Đã cập nhật quyền người dùng.');
    }
    redirect(url('admin/users.php'));
}

$users = $pdo->query('SELECT u.*, (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS orders FROM users u ORDER BY u.id')->fetchAll();
?>
<h1>Người dùng</h1>
<div class="table-wrap"><table>
  <thead><tr><th>Họ tên</th><th>Email</th><th>SĐT</th><th>Đơn hàng</th><th>Ngày tạo</th><th>Quyền</th></tr></thead>
  <tbody>
  <?php foreach ($users as $u): ?>
    <tr><td><?= e($u['full_name']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['phone']) ?></td>
      <td><?= (int)$u['orders'] ?></td><td><?= e(date('d/m/Y', strtotime($u['created_at']))) ?></td>
      <td>
        <?php if ((int)$u['id'] === (int)$admin['id']): ?>
          <span class="status">admin (bạn)</span>
        <?php else: ?>
        <form method="post" style="display:flex;gap:6px">
          <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
          <select name="role" onchange="this.form.submit()" style="width:auto">
            <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>user</option>
            <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
          </select>
        </form>
        <?php endif; ?>
      </td></tr>
  <?php endforeach; ?>
  </tbody></table></div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
