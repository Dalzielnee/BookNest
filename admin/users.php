<?php
    $title = 'Người dùng';
    require_once __DIR__ . '/../includes/admin_header.php';
    $pdo = db();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id   = (int) ($_POST['id'] ?? 0);
    $role = $_POST['role'] ?? '';
    if ($id === (int) $admin['id']) {
        flash('danger', 'Bạn không thể tự đổi quyền của chính mình.');
    } elseif (in_array($role, ['user', 'admin'], true)) {
        $pdo->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$role, $id]);
        flash('success', 'Đã cập nhật quyền người dùng.');
    }
    redirect(url('admin/users.php'));
    }
    $q      = trim($_GET['q'] ?? '');
    $where  = '';
    $params = [];
    if ($q !== '') {
    $where  = ' WHERE u.full_name LIKE ? OR u.email LIKE ?';
    $params = ['%' . $q . '%', '%' . $q . '%'];
    }
    $perPage = 10;
    $cnt     = $pdo->prepare('SELECT COUNT(*) FROM users u' . $where);
    $cnt->execute($params);
    $total  = (int) $cnt->fetchColumn();
    $pages  = max(1, (int) ceil($total / $perPage));
    $page   = min(max(1, (int) ($_GET['page'] ?? 1)), $pages);
    $offset = ($page - 1) * $perPage;
    $st     = $pdo->prepare('SELECT u.*, (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS orders FROM users u' . $where . ' ORDER BY u.id LIMIT ' . $perPage . ' OFFSET ' . $offset);
    $st->execute($params);
$users = $st->fetchAll(); ?>
<div class="toolbar"><h1 style="margin:0">Người dùng</h1>
  <form method="get" style="display:flex;gap:6px">
    <input type="search" name="q" value="<?php echo e($q) ?>" placeholder="Tìm theo tên hoặc email" style="width:auto">
    <button class="btn btn-sm">Tìm</button>
    <?php if ($q !== ''): ?><a class="btn btn-sm btn-outline" href="users.php">Xóa lọc</a><?php endif; ?>
  </form></div><div class="table-wrap"><table>
  <thead><tr><th>Họ tên</th><th>Email</th><th>SĐT</th><th>Đơn hàng</th><th>Ngày tạo</th><th>Quyền</th></tr></thead>
  <tbody>
  <?php foreach ($users as $u): ?>
    <tr><td><?php echo e($u['full_name']) ?></td><td><?php echo e($u['email']) ?></td><td><?php echo e($u['phone']) ?></td>
      <td><?php echo (int)$u['orders'] ?></td><td><?php echo e(date('d/m/Y', strtotime($u['created_at']))) ?></td>
      <td>
        <?php if ((int) $u['id'] === (int) $admin['id']): ?>
          <span class="status">admin (bạn)</span>
        <?php else: ?>
        <form method="post" style="display:flex;gap:6px">
          <?php echo csrf_field() ?><input type="hidden" name="id" value="<?php echo (int)$u['id'] ?>">
          <select name="role" onchange="this.form.submit()" style="width:auto">
            <option value="user" <?php echo $u['role'] === 'user' ? 'selected' : '' ?>>user</option>
            <option value="admin" <?php echo $u['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
          </select>
        </form>
        <?php endif; ?>
      </td></tr>
  <?php endforeach; ?>
    <?php if (! $users): ?><tr><td colspan="6" class="muted">Không tìm thấy người dùng.</td></tr><?php endif; ?>
  </tbody></table></div>
  <?php if ($pages > 1): ?>
<div class="toolbar">
  <?php for ($i = 1; $i <= $pages; $i++): ?>
    <a class="btn btn-sm <?php echo $i === $page ? '' : 'btn-outline' ?>" href="?<?php echo e(http_build_query(['q' => $q, 'page' => $i])) ?>"><?php echo $i ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
