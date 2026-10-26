<?php
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $ajax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'; // request từ cart.js
    $ok = true;
    $msg = '';
    $level = 'success'; // success | warning | danger

    if ($action === 'add' || $action === 'update') {
        $st = db()->prepare('SELECT id, stock, title FROM products WHERE id = ?');
        $st->execute([$id]);
        $p = $st->fetch();
        if (!$p) {
            $ok = false;
            $level = 'danger';
            $msg = 'Sách không tồn tại.';
            if (!$ajax) flash('danger', $msg);
        } else {
            $qty = max(1, (int)($_POST['qty'] ?? 1));
            if ($action === 'add') {
                $qty += (int)($_SESSION['cart'][$id] ?? 0);
            }
            if ($qty > $p['stock']) {
                $qty = (int)$p['stock'];
                $level = 'warning';
                $msg = 'Chỉ còn ' . $p['stock'] . ' cuốn "' . $p['title'] . '".';
                if (!$ajax) flash('warning', $msg);
            }
            if ($qty > 0) {
                $_SESSION['cart'][$id] = $qty;
                if ($action === 'add') {
                    if ($ajax) {
                        if ($msg === '') $msg = 'Đã thêm "' . $p['title'] . '" vào giỏ hàng.';
                    } else {
                        flash('success', 'Đã thêm "' . $p['title'] . '" vào giỏ hàng.');
                    }
                }
            }
        }
        // Cập nhật số lượng hoặc thêm vào giỏ bằng AJAX: trả JSON, không tải lại trang
        if ($ajax) {
            $items = cart_items();
            $line = 0;
            foreach ($items as $it) {
                if ((int)$it['id'] === $id) $line = $it['subtotal'];
            }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok' => $ok,
                'qty' => (int)($_SESSION['cart'][$id] ?? 0),
                'subtotal' => money($line),
                'total' => money(cart_total($items)),
                'count' => cart_count(),
                'message' => $msg,
                'level' => $level,
            ]);
            exit;
        }
        if ($action === 'add') {
            redirect($_SERVER['HTTP_REFERER'] ?? url('cart.php'));
        }
    } elseif ($action === 'remove') {
        unset($_SESSION['cart'][$id]);
        flash('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    } elseif ($action === 'clear') {
        unset($_SESSION['cart']);
    }
    redirect(url('cart.php'));
}

$title = 'Giỏ hàng';
$items = cart_items();
include __DIR__ . '/includes/header.php';
?>
<h1>Giỏ hàng</h1>
<div id="cart-msg" class="alert alert-warning" style="display:none"></div>
<?php if (!$items): ?>
  <div class="card">Giỏ hàng của bạn đang trống. <a href="<?= url('products.php') ?>">Tiếp tục mua sắm</a></div>
<?php else: ?>
<div class="two-col">
  <div class="table-wrap">
    <table>
      <thead><tr><th></th><th>Sách</th><th>Đơn giá</th><th>Số lượng</th><th class="right">Thành tiền</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td><img class="thumb" src="<?= e(cover_url($it['image'])) ?>" alt=""></td>
          <td><a href="<?= url('product.php?id=' . $it['id']) ?>"><?= e($it['title']) ?></a></td>
          <td><?= money($it['price']) ?></td>
          <td>
            <form method="post" class="js-cart-update" style="display:flex;gap:6px">
              <?= csrf_field() ?><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
              <input class="qty" type="number" name="qty" value="<?= (int)$it['qty'] ?>" min="1" max="<?= (int)$it['stock'] ?>">
              <button class="btn btn-sm btn-outline">Cập nhật</button>
            </form>
          </td>
          <td class="right"><b class="js-subtotal"><?= money($it['subtotal']) ?></b></td>
          <td>
            <form method="post">
              <?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
              <button class="btn btn-sm btn-danger" aria-label="Xóa">✕</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <aside class="card">
    <h2 style="margin-top:0">Tổng đơn hàng</h2>
    <p style="display:flex;justify-content:space-between"><span>Tạm tính</span><b class="price js-total"><?= money(cart_total($items)) ?></b></p>
    <a class="btn" style="width:100%;text-align:center" href="<?= url('checkout.php') ?>">Tiến hành đặt hàng</a>
    <form method="post" style="margin-top:10px">
      <?= csrf_field() ?><input type="hidden" name="action" value="clear">
      <button class="btn btn-outline" style="width:100%">Xóa giỏ hàng</button>
    </form>
  </aside>
</div>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
