<?php
require_once __DIR__ . '/includes/functions.php';
$user = require_login();

// Hủy đơn (chỉ khi đang chờ xác nhận) - hoàn lại tồn kho
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $oid = (int)($_POST['order_id'] ?? 0);
    $pdo = db();
    $pdo->beginTransaction();
    $st = $pdo->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ? AND status = 'pending' FOR UPDATE");
    $st->execute([$oid, $user['id']]);
    if ($st->fetch()) {
        $pdo->prepare("UPDATE products p JOIN order_items i ON i.product_id = p.id SET p.stock = p.stock + i.quantity WHERE i.order_id = ?")->execute([$oid]);
        $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?")->execute([$oid]);
        $pdo->commit();
        flash('success', 'Đã hủy đơn #' . $oid . '.');
    } else {
        $pdo->rollBack();
        flash('danger', 'Không thể hủy đơn hàng này.');
    }
    redirect(url('orders.php?id=' . $oid));
}

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $st = db()->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
    $st->execute([$id, $user['id']]);
    $order = $st->fetch();
    if (!$order) {
        http_response_code(404);
        flash('danger', 'Không tìm thấy đơn hàng.');
        redirect(url('orders.php'));
    }
    $st = db()->prepare('SELECT * FROM order_items WHERE order_id = ?');
    $st->execute([$id]);
    $lines = $st->fetchAll();
    $title = 'Đơn hàng #' . $id;
} else {
    $filter = $_GET['status'] ?? '';
    if (isset(ORDER_STATUS[$filter])) {
        $st = db()->prepare('SELECT * FROM orders WHERE user_id = ? AND status = ? ORDER BY id DESC');
        $st->execute([$user['id'], $filter]);
    } else {
        $filter = '';
        $st = db()->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC');
        $st->execute([$user['id']]);
    }
    $orders = $st->fetchAll();
    $title = 'Đơn hàng của tôi';
}
include __DIR__ . '/includes/header.php';
?>
<?php if ($id): ?>
  <p><a href="<?= url('orders.php') ?>">← Tất cả đơn hàng</a></p>
  <h1>Đơn hàng #<?= (int)$order['id'] ?> <span class="status status-<?= e($order['status']) ?>"><?= e(ORDER_STATUS[$order['status']]) ?></span></h1>
  <div class="two-col">
    <div class="table-wrap"><table>
      <thead><tr><th>Sách</th><th>Đơn giá</th><th>SL</th><th class="right">Thành tiền</th></tr></thead>
      <tbody>
      <?php foreach ($lines as $l): ?>
        <tr><td><a href="<?= url('product.php?id=' . $l['product_id']) ?>"><?= e($l['title']) ?></a></td>
          <td><?= money($l['price']) ?></td><td><?= (int)$l['quantity'] ?></td>
          <td class="right"><?= money($l['price'] * $l['quantity']) ?></td></tr>
      <?php endforeach; ?>
        <tr><td colspan="3" class="right"><b>Tổng cộng</b></td><td class="right"><b class="price"><?= money($order['total']) ?></b></td></tr>
      </tbody></table></div>
    <aside class="card">
      <h2 style="margin-top:0">Giao đến</h2>
      <p><b><?= e($order['receiver']) ?></b><br><?= e($order['phone']) ?><br><?= e($order['address']) ?></p>
      <?php if ($order['note']): ?><p class="muted">Ghi chú: <?= e($order['note']) ?></p><?php endif; ?>
      <p class="muted">Đặt lúc <?= e(date('d/m/Y H:i', strtotime($order['created_at']))) ?></p>
      <?php if ($order['status'] === 'pending'): ?>
      <form method="post" onsubmit="return confirm('Bạn chắc chắn muốn hủy đơn hàng này?')">
        <?= csrf_field() ?><input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
        <button class="btn btn-danger">Hủy đơn hàng</button>
      </form>
      <?php endif; ?>
    </aside>
  </div>
<?php else: ?>
  <div class="toolbar"><h1 style="margin:0">Đơn hàng của tôi</h1>
    <form method="get" style="min-width:200px"><select name="status" onchange="this.form.submit()">
      <option value="">Tất cả trạng thái</option>
      <?php foreach (ORDER_STATUS as $k => $label): ?>
        <option value="<?= e($k) ?>" <?= $filter === $k ? 'selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select></form></div>
  <?php if (!$orders && $filter): ?>
    <div class="card">Không có đơn hàng nào ở trạng thái "<?= e(ORDER_STATUS[$filter]) ?>". <a href="<?= url('orders.php') ?>">Xem tất cả</a></div>
  <?php elseif (!$orders): ?>
    <div class="card">Bạn chưa có đơn hàng nào. <a href="<?= url('products.php') ?>">Mua sắm ngay</a></div>
  <?php else: ?>
  <div class="table-wrap"><table>
    <thead><tr><th>Mã đơn</th><th>Ngày đặt</th><th>Tổng tiền</th><th>Trạng thái</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
      <tr><td>#<?= (int)$o['id'] ?></td>
        <td><?= e(date('d/m/Y H:i', strtotime($o['created_at']))) ?></td>
        <td><?= money($o['total']) ?></td>
        <td><span class="status status-<?= e($o['status']) ?>"><?= e(ORDER_STATUS[$o['status']]) ?></span></td>
        <td><a href="<?= url('orders.php?id=' . $o['id']) ?>">Chi tiết</a></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
  <?php endif; ?>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
