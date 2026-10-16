<?php
require_once __DIR__ . '/includes/functions.php';
$user = require_login();

$errors = [];
$old = ['full_name' => $user['full_name'], 'phone' => $user['phone'] ?? '', 'address' => $user['address'] ?? ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['form'] ?? '') === 'info') {
        $old = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
        ];
        if (mb_strlen($old['full_name']) < 2) $errors['full_name'] = 'Họ tên tối thiểu 2 ký tự.';
        if ($old['phone'] !== '' && !preg_match('/^[0-9+\s.-]{8,15}$/', $old['phone'])) $errors['phone'] = 'Số điện thoại không hợp lệ.';
        if (!$errors) {
            db()->prepare('UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?')
                ->execute([$old['full_name'], $old['phone'] ?: null, $old['address'] ?: null, $user['id']]);
            flash('success', 'Đã cập nhật thông tin.');
            redirect(url('profile.php'));
        }
    } elseif (($_POST['form'] ?? '') === 'password') {
        $cur = $_POST['current'] ?? '';
        $new = $_POST['new'] ?? '';
        $new2 = $_POST['new2'] ?? '';
        $st = db()->prepare('SELECT password FROM users WHERE id = ?');
        $st->execute([$user['id']]);
        if (!password_verify($cur, $st->fetchColumn())) $errors['current'] = 'Mật khẩu hiện tại không đúng.';
        if (strlen($new) < 6) $errors['new'] = 'Mật khẩu mới tối thiểu 6 ký tự.';
        if ($new !== $new2) $errors['new2'] = 'Mật khẩu nhập lại không khớp.';
        if (!$errors) {
            db()->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_BCRYPT), $user['id']]);
            session_regenerate_id(true);
            flash('success', 'Đổi mật khẩu thành công.');
            redirect(url('profile.php'));
        }
    }
}

$title = 'Tài khoản';
include __DIR__ . '/includes/header.php';
function perr(array $e, string $k): string
{
    return isset($e[$k]) ? '<div class="error">' . e($e[$k]) . '</div>' : '';
}
?>
<h1>Tài khoản của tôi</h1>
<div class="two-col" style="grid-template-columns:1fr 1fr">
  <form class="card" method="post" novalidate>
    <?= csrf_field() ?><input type="hidden" name="form" value="info">
    <h2 style="margin-top:0">Thông tin cá nhân</h2>
    <div class="form-group"><label>Email</label><input type="email" value="<?= e($user['email']) ?>" disabled></div>
    <div class="form-group"><label for="full_name">Họ và tên</label>
      <input id="full_name" type="text" name="full_name" value="<?= e($old['full_name']) ?>"><?= perr($errors, 'full_name') ?></div>
    <div class="form-group"><label for="phone">Số điện thoại</label>
      <input id="phone" type="tel" name="phone" value="<?= e($old['phone']) ?>"><?= perr($errors, 'phone') ?></div>
    <div class="form-group"><label for="address">Địa chỉ</label>
      <input id="address" type="text" name="address" value="<?= e($old['address']) ?>"></div>
    <button class="btn">Lưu thay đổi</button>
  </form>
  <form class="card" method="post" novalidate>
    <?= csrf_field() ?><input type="hidden" name="form" value="password">
    <h2 style="margin-top:0">Đổi mật khẩu</h2>
    <div class="form-group"><label for="current">Mật khẩu hiện tại</label>
      <input id="current" type="password" name="current"><?= perr($errors, 'current') ?></div>
    <div class="form-group"><label for="new">Mật khẩu mới</label>
      <input id="new" type="password" name="new"><?= perr($errors, 'new') ?></div>
    <div class="form-group"><label for="new2">Nhập lại mật khẩu mới</label>
      <input id="new2" type="password" name="new2"><?= perr($errors, 'new2') ?></div>
    <button class="btn">Đổi mật khẩu</button>
  </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
