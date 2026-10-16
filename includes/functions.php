<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';

/** Escape HTML */
function e($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function money($n): string
{
    return number_format((float)$n, 0, ',', '.') . ' ₫';
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/** Đường dẫn gốc của site (hỗ trợ cả khi đặt trong thư mục con) */
function base_url(): string
{
    static $base = null;
    if ($base === null) {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');
        if (str_ends_with($dir, '/admin')) {
            $dir = substr($dir, 0, -6);
        }
        $base = $dir;
    }
    return $base;
}

function url(string $path = ''): string
{
    return base_url() . '/' . ltrim($path, '/');
}

/* ---------- Flash message ---------- */
function flash(string $type, string $msg): void
{
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}

function flash_render(): string
{
    $out = '';
    foreach ($_SESSION['flash'] ?? [] as $f) {
        $out .= '<div class="alert alert-' . e($f['type']) . '">' . e($f['msg']) . '</div>';
    }
    unset($_SESSION['flash']);
    return $out;
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $t = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $t)) {
        http_response_code(419);
        exit('Phiên làm việc không hợp lệ. Vui lòng tải lại trang.');
    }
}

/* ---------- Auth ---------- */
function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    static $user = false;
    if ($user === false) {
        $st = db()->prepare('SELECT id, full_name, email, phone, address, role FROM users WHERE id = ?');
        $st->execute([$_SESSION['user_id']]);
        $user = $st->fetch() ?: null;
        if (!$user) {
            unset($_SESSION['user_id']);
        }
    }
    return $user;
}

function require_login(): array
{
    $u = current_user();
    if (!$u) {
        flash('warning', 'Vui lòng đăng nhập để tiếp tục.');
        redirect(url('login.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? '')));
    }
    return $u;
}

function require_admin(): array
{
    $u = current_user();
    if (!$u || $u['role'] !== 'admin') {
        flash('danger', 'Bạn không có quyền truy cập khu vực quản trị.');
        redirect(url('login.php'));
    }
    return $u;
}

/* ---------- Giỏ hàng: lưu trong session [product_id => qty] ---------- */
function cart_items(): array
{
    $cart = $_SESSION['cart'] ?? [];
    if (!$cart) {
        return [];
    }
    $ids = array_keys($cart);
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $st = db()->prepare("SELECT * FROM products WHERE id IN ($ph)");
    $st->execute($ids);
    $items = [];
    foreach ($st->fetchAll() as $p) {
        $qty = min((int)$cart[$p['id']], (int)$p['stock']);
        if ($qty < 1) {
            continue;
        }
        $p['qty'] = $qty;
        $p['subtotal'] = $qty * $p['price'];
        $items[] = $p;
    }
    return $items;
}

function cart_count(): int
{
    return array_sum($_SESSION['cart'] ?? []);
}

function cart_total(array $items): float
{
    return array_sum(array_column($items, 'subtotal'));
}

/* ---------- Ảnh bìa ---------- */
function cover_url(?string $image): string
{
    if ($image && is_file(__DIR__ . '/../uploads/' . $image)) {
        return url('uploads/' . $image);
    }
    return url('assets/img/default-cover.svg');
}

const ORDER_STATUS = [
    'pending'   => 'Chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'shipping'  => 'Đang giao',
    'done'      => 'Hoàn thành',
    'cancelled' => 'Đã hủy',
];

/** Lưu ảnh upload, trả về tên file hoặc null. Ném Exception nếu file không hợp lệ. */
function save_upload(string $field): ?string
{
    if (empty($_FILES[$field]['name'])) {
        return null;
    }
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Tải ảnh thất bại.');
    }
    if ($f['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException('Ảnh tối đa 2MB.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if (!$ext) {
        throw new RuntimeException('Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.');
    }
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    $dir = __DIR__ . '/../uploads';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    move_uploaded_file($f['tmp_name'], $dir . '/' . $name);
    return $name;
}
