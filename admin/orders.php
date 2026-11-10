<?php
$title = 'Đơn hàng';
require_once __DIR__ . '/../includes/admin_header.php';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $new = $_POST['status'] ?? '';
    if (isset(ORDER_STATUS[$new])) {
        $pdo->beginTransaction();
        $st = $pdo->prepare('SELECT status FROM orders WHERE id = ? FOR UPDATE');
        $st->execute([$id]);
        $old = $st->fetchColumn();
        if ($old === false) {
            $pdo->rollBack();
        } elseif ($old === 'cancelled' && $new !== 'cancelled') {
            $pdo->rollBack();
            flash('danger', 'Đơn đã hủy không thể mở lại.');
        } else {
            // hủy đơn: hoàn lại tồn kho
            if ($new === 'cancelled' && $old !== 'cancelled') {
                $pdo->prepare('UPDATE products p JOIN order_items i ON i.product_id = p.id SET p.stock = p.stock + i.quantity WHERE i.order_id = ?')->execute([$id]);
            }
            $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$new, $id]);
            $pdo->commit();
            flash('success', 'Đã cập nhật trạng thái đơn #' . $id . '.');
        }
    }
    redirect(url('admin/orders.php?id=' . $id));
}

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $st = $pdo->prepare('SELECT o.*, u.email FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = ?');
    $st->execute([$id]);
    $order = $st->fetch();
    if (!$order) {
        flash('danger', 'Không tìm thấy đơn hàng.');
        redirect(url('admin/orders.php'));
    }
    $st = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
    $st->execute([$id]);
    $lines = $st->fetchAll();
} else {
    $filter = $_GET['status'] ?? '';
    if (isset(ORDER_STATUS[$filter])) {
        $st = $pdo->prepare('SELECT o.*, u.email FROM orders o JOIN users u ON u.id = o.user_id WHERE o.status = ? ORDER BY o.id DESC');
        $st->execute([$filter]);
    } else {
        $st = $pdo->query('SELECT o.*, u.email FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.id DESC');
    }
    $orders = $st->fetchAll();
}
?>
<?php if ($id): ?>
  <p><a href="orders.php">← Danh sách đơn hàng</a></p>
  <h1>Đơn #<?= (int)$order['id'] ?></h1>
  <div class="two-col">
    <div class="table-wrap"><table>
      <thead><tr><th>Sách</th><th>Đơn giá</th><th>SL</th><th class="right">Thành tiền</th></tr></thead>
      <tbody>
      <?php foreach ($lines as $l): ?>
        <tr><td><?= e($l['title']) ?></td><td><?= money($l['price']) ?></td><td><?= (int)$l['quantity'] ?></td><td class="right"><?= money($l['price'] * $l['quantity']) ?></td></tr>
      <?php endforeach; ?>
        <tr><td colspan="3" class="right"><b>Tổng</b></td><td class="right"><b><?= money($order['total']) ?></b></td></tr>
      </tbody></table></div>
    <div class="card">
      <p><b><?= e($order['receiver']) ?></b> (<?= e($order['email']) ?>)<br><?= e($order['phone']) ?><br><?= e($order['address']) ?></p>
      <?php if ($order['note']): ?><p class="muted">Ghi chú: <?= e($order['note']) ?></p><?php endif; ?>
      <form method="post">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$order['id'] ?>">
        <div class="form-group"><label for="status">Trạng thái</label>
          <select id="status" name="status">
            <?php foreach (ORDER_STATUS as $k => $label): ?>
              <option value="<?= e($k) ?>" <?= $order['status'] === $k ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select></div>
        <button class="btn">Cập nhật</button>
      </form>
    </div>
  </div>
<?php else: ?>
  <div class="toolbar"><h1 style="margin:0">Đơn hàng</h1>
    <form method="get"><select name="status" onchange="this.form.submit()">
      <option value="">Tất cả trạng thái</option>
      <?php foreach (ORDER_STATUS as $k => $label): ?>
        <option value="<?= e($k) ?>" <?= $filter === $k ? 'selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select></form></div>
  <div class="table-wrap"><table>
    <thead><tr><th>Mã</th><th>Khách hàng</th><th>Ngày đặt</th><th>Tổng</th><th>Trạng thái</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
      <tr><td>#<?= (int)$o['id'] ?></td><td><?= e($o['receiver']) ?><br><span class="muted"><?= e($o['email']) ?></span></td>
        <td><?= e(date('d/m/Y H:i', strtotime($o['created_at']))) ?></td><td><?= money($o['total']) ?></td>
        <td><span class="status status-<?= e($o['status']) ?>"><?= e(ORDER_STATUS[$o['status']]) ?></span></td>
        <td><a class="btn btn-sm btn-outline" href="?id=<?= (int)$o['id'] ?>">Xem</a></td></tr>
    <?php endforeach; ?>
    <?php if (!$orders): ?><tr><td colspan="6" class="muted">Chưa có đơn hàng.</td></tr><?php endif; ?>
    </tbody></table></div>
<?php endif; ?>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
