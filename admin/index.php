<?php
$title = 'Tổng quan';
require_once __DIR__ . '/../includes/admin_header.php';

$pdo = db();
$stats = [
    'Đầu sách' => $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'Đơn hàng' => $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'Đơn chờ xác nhận' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn(),
    'Khách hàng' => $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn(),
];
$revenue = $pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status <> 'cancelled'")->fetchColumn();
$recent = $pdo->query('SELECT o.*, u.email FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.id DESC LIMIT 5')->fetchAll();
// Doanh thu 7 ngày gần nhất (không tính đơn hủy)
$from = date('Y-m-d', strtotime('-6 day'));
$st = $pdo->prepare("SELECT DATE(created_at) AS d, COUNT(*) AS n, SUM(total) AS rev FROM orders WHERE status <> 'cancelled' AND created_at >= ? GROUP BY DATE(created_at)");
$st->execute([$from . ' 00:00:00']);
$byDay = [];
foreach ($st->fetchAll() as $r) {
    $byDay[$r['d']] = $r;
}
$daily = [];
for ($i = 0; $i < 7; $i++) {
    $d = date('Y-m-d', strtotime("-$i day"));
    $daily[] = ['d' => $d, 'n' => (int)($byDay[$d]['n'] ?? 0), 'rev' => (float)($byDay[$d]['rev'] ?? 0)];
}
$low = $pdo->query('SELECT id, title, stock FROM products WHERE stock <= 10 ORDER BY stock ASC LIMIT 5')->fetchAll();
?>
<h1>Tổng quan</h1>
<div class="stats">
  <?php foreach ($stats as $label => $v): ?>
    <div class="stat"><span class="muted"><?= e($label) ?></span><b><?= (int)$v ?></b></div>
  <?php endforeach; ?>
  <div class="stat"><span class="muted">Doanh thu (không tính đơn hủy)</span><b><?= money($revenue) ?></b></div>
</div>

<h2 style="margin-top:0">Doanh thu 7 ngày gần nhất</h2>
<div class="table-wrap" style="margin-bottom:24px"><table>
  <thead><tr><th>Ngày</th><th>Số đơn</th><th class="right">Doanh thu</th></tr></thead>
  <tbody>
  <?php foreach ($daily as $r): ?>
    <tr><td><?= e(date('d/m/Y', strtotime($r['d']))) ?></td><td><?= (int)$r['n'] ?></td><td class="right"><?= money($r['rev']) ?></td></tr>
  <?php endforeach; ?>
  </tbody></table></div>

<div class="two-col" style="grid-template-columns:1.4fr 1fr">
  <div>
    <h2 style="margin-top:0">Đơn hàng gần đây</h2>
    <div class="table-wrap"><table>
      <thead><tr><th>Mã</th><th>Khách</th><th>Tổng</th><th>Trạng thái</th></tr></thead>
      <tbody>
      <?php foreach ($recent as $o): ?>
        <tr><td><a href="<?= url('admin/orders.php?id=' . $o['id']) ?>">#<?= (int)$o['id'] ?></a></td>
          <td><?= e($o['email']) ?></td><td><?= money($o['total']) ?></td>
          <td><span class="status status-<?= e($o['status']) ?>"><?= e(ORDER_STATUS[$o['status']]) ?></span></td></tr>
      <?php endforeach; ?>
      <?php if (!$recent): ?><tr><td colspan="4" class="muted">Chưa có đơn hàng.</td></tr><?php endif; ?>
      </tbody></table></div>
  </div>
  <div>
    <h2 style="margin-top:0">Sắp hết hàng</h2>
    <div class="table-wrap"><table>
      <thead><tr><th>Sách</th><th>Tồn</th></tr></thead>
      <tbody>
      <?php foreach ($low as $p): ?>
        <tr><td><a href="<?= url('admin/products.php?edit=' . $p['id']) ?>"><?= e($p['title']) ?></a></td><td><?= (int)$p['stock'] ?></td></tr>
      <?php endforeach; ?>
      <?php if (!$low): ?><tr><td colspan="2" class="muted">Tồn kho ổn định.</td></tr><?php endif; ?>
      </tbody></table></div>
  </div>
</div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
