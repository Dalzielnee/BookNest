<?php
require_once __DIR__ . '/includes/functions.php';
$user = require_login();

$items = cart_items();
if (!$items) {
    flash('warning', 'Giỏ hàng trống.');
    redirect(url('cart.php'));
}

$errors = [];
$old = [
    'receiver' => $user['full_name'],
    'phone' => $user['phone'] ?? '',
    'address' => $user['address'] ?? '',
    'note' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($old as $k => $_) {
        $old[$k] = trim($_POST[$k] ?? '');
    }
    if (mb_strlen($old['receiver']) < 2) $errors['receiver'] = 'Vui lòng nhập tên người nhận.';
    if (!preg_match('/^[0-9+\s.-]{8,15}$/', $old['phone'])) $errors['phone'] = 'Số điện thoại không hợp lệ.';
    if (mb_strlen($old['address']) < 8) $errors['address'] = 'Vui lòng nhập địa chỉ chi tiết.';

    if (!$errors) {
        $pdo = db();
        try {
            $pdo->beginTransaction();
            // khóa các dòng sản phẩm để kiểm tra tồn kho chính xác
            $total = 0;
            $lines = [];
            foreach ($_SESSION['cart'] as $pid => $qty) {
                $st = $pdo->prepare('SELECT id, title, price, stock FROM products WHERE id = ? FOR UPDATE');
                $st->execute([$pid]);
                $p = $st->fetch();
                if (!$p) continue;
                if ($p['stock'] < $qty) {
                    throw new RuntimeException('Sách "' . $p['title'] . '" chỉ còn ' . $p['stock'] . ' cuốn.');
                }
                $lines[] = [$p, (int)$qty];
                $total += $p['price'] * $qty;
            }
            if (!$lines) throw new RuntimeException('Giỏ hàng không hợp lệ.');

            $pdo->prepare('INSERT INTO orders (user_id, receiver, phone, address, note, total) VALUES (?,?,?,?,?,?)')
                ->execute([$user['id'], $old['receiver'], $old['phone'], $old['address'], $old['note'] ?: null, $total]);
            $orderId = (int)$pdo->lastInsertId();

            $ins = $pdo->prepare('INSERT INTO order_items (order_id, product_id, title, price, quantity) VALUES (?,?,?,?,?)');
            $upd = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
            foreach ($lines as [$p, $qty]) {
                $ins->execute([$orderId, $p['id'], $p['title'], $p['price'], $qty]);
                $upd->execute([$qty, $p['id']]);
            }
            $pdo->commit();
            unset($_SESSION['cart']);
            flash('success', 'Đặt hàng thành công! Mã đơn #' . $orderId);
            redirect(url('orders.php?id=' . $orderId));
        } catch (RuntimeException $ex) {
            $pdo->rollBack();
            flash('danger', $ex->getMessage());
            redirect(url('cart.php'));
        }
    }
}

$title = 'Đặt hàng';
include __DIR__ . '/includes/header.php';
function ferr(array $e, string $k): string
{
    return isset($e[$k]) ? '<div class="error">' . e($e[$k]) . '</div>' : '';
}
?>
<h1>Thanh toán</h1>
<div class="two-col">
  <form class="card" method="post" novalidate>
    <?= csrf_field() ?>
    <h2 style="margin-top:0">Thông tin giao hàng</h2>
    <div class="form-group"><label for="receiver">Người nhận</label>
      <input id="receiver" type="text" name="receiver" value="<?= e($old['receiver']) ?>"><?= ferr($errors, 'receiver') ?></div>
    <div class="form-group"><label for="phone">Số điện thoại</label>
      <input id="phone" type="tel" name="phone" value="<?= e($old['phone']) ?>"><?= ferr($errors, 'phone') ?></div>
    <div class="form-group"><label for="address">Địa chỉ</label>
      <input id="address" type="text" name="address" value="<?= e($old['address']) ?>"><?= ferr($errors, 'address') ?></div>
    <div class="form-group"><label for="note">Ghi chú</label>
      <textarea id="note" name="note"><?= e($old['note']) ?></textarea></div>
    <p class="muted">Thanh toán khi nhận hàng (COD).</p>
    <button class="btn">Xác nhận đặt hàng</button>
  </form>
  <aside class="card">
    <h2 style="margin-top:0">Đơn hàng của bạn</h2>
    <?php foreach ($items as $it): ?>
      <p style="display:flex;justify-content:space-between;gap:10px"><span><?= e($it['title']) ?> × <?= (int)$it['qty'] ?></span><span><?= money($it['subtotal']) ?></span></p>
    <?php endforeach; ?>
    <hr>
    <p style="display:flex;justify-content:space-between"><b>Tổng cộng</b><b class="price"><?= money(cart_total($items)) ?></b></p>
  </aside>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
