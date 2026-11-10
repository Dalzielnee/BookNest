<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Có thể lọc theo trạng thái giống trang danh sách: export_orders.php?status=pending
$filter = $_GET['status'] ?? '';
if (isset(ORDER_STATUS[$filter])) {
    $st = db()->prepare('SELECT o.*, u.email FROM orders o JOIN users u ON u.id = o.user_id WHERE o.status = ? ORDER BY o.id DESC');
    $st->execute([$filter]);
} else {
    $st = db()->query('SELECT o.*, u.email FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.id DESC');
}

// Chống công thức độc hại khi mở bằng Excel (ô bắt đầu bằng = + - @)
function csv_safe($v): string
{
    $v = (string)$v;
    return ($v !== '' && strpos('=+-@', $v[0]) !== false) ? "'" . $v : $v;
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="orders-' . date('Ymd-His') . '.csv"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // BOM để Excel đọc đúng tiếng Việt
fputcsv($out, ['Mã đơn', 'Người nhận', 'Email', 'Điện thoại', 'Địa chỉ', 'Tổng tiền', 'Trạng thái', 'Ngày đặt'], ',', '"', '');
foreach ($st->fetchAll() as $o) {
    fputcsv($out, [
        $o['id'],
        csv_safe($o['receiver']),
        csv_safe($o['email']),
        csv_safe($o['phone']),
        csv_safe($o['address']),
        (int)$o['total'],
        ORDER_STATUS[$o['status']],
        date('d/m/Y H:i', strtotime($o['created_at'])),
    ], ',', '"', '');
}
fclose($out);
exit;
