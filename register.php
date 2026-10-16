<?php
require_once __DIR__ . '/includes/functions.php';

if (current_user()) {
    redirect(url('index.php'));
}

$errors = [];
$old = ['full_name' => '', 'email' => '', 'phone' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $old = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
    ];
    $pass = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if (mb_strlen($old['full_name']) < 2) $errors['full_name'] = 'Họ tên tối thiểu 2 ký tự.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email không hợp lệ.';
    if ($old['phone'] !== '' && !preg_match('/^[0-9+\s.-]{8,15}$/', $old['phone'])) $errors['phone'] = 'Số điện thoại không hợp lệ.';
    if (strlen($pass) < 8) {
        $errors['password'] = 'Mật khẩu tối thiểu 8 ký tự.';
    } elseif (!preg_match('/[a-z]/', $pass) || !preg_match('/[A-Z]/', $pass) || !preg_match('/[0-9]/', $pass)) {
        $errors['password'] = 'Mật khẩu phải có chữ hoa, chữ thường và chữ số.';
    }
    if ($pass !== $pass2) $errors['password2'] = 'Mật khẩu nhập lại không khớp.';

    if (!$errors) {
        $st = db()->prepare('SELECT id FROM users WHERE email = ?');
        $st->execute([$old['email']]);
        if ($st->fetch()) {
            $errors['email'] = 'Email này đã được đăng ký.';
        }
    }
    if (!$errors) {
        $st = db()->prepare('INSERT INTO users (full_name, email, password, phone) VALUES (?,?,?,?)');
        $st->execute([$old['full_name'], $old['email'], password_hash($pass, PASSWORD_BCRYPT), $old['phone'] ?: null]);
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)db()->lastInsertId();
        flash('success', 'Đăng ký thành công! Chào mừng bạn đến với BookNest.');
        redirect(url('index.php'));
    }
}

$title = 'Đăng ký';
include __DIR__ . '/includes/header.php';
function field_err(array $errors, string $k): string
{
    return isset($errors[$k]) ? '<div class="error">' . e($errors[$k]) . '</div>' : '';
}
?>
<div class="card form-card">
  <h1>Tạo tài khoản</h1>
  <form method="post" novalidate>
    <?= csrf_field() ?>
    <div class="form-group"><label for="full_name">Họ và tên</label>
      <input id="full_name" type="text" name="full_name" value="<?= e($old['full_name']) ?>" required><?= field_err($errors, 'full_name') ?></div>
    <div class="form-group"><label for="email">Email</label>
      <input id="email" type="email" name="email" value="<?= e($old['email']) ?>" required><?= field_err($errors, 'email') ?></div>
    <div class="form-group"><label for="phone">Số điện thoại (không bắt buộc)</label>
      <input id="phone" type="tel" name="phone" value="<?= e($old['phone']) ?>"><?= field_err($errors, 'phone') ?></div>
    <div class="form-group"><label for="password">Mật khẩu</label>
      <input id="password" type="password" name="password" required>
      <small class="muted">Ít nhất 8 ký tự, gồm chữ hoa, chữ thường và chữ số.</small><?= field_err($errors, 'password') ?></div>    <div class="form-group"><label for="password2">Nhập lại mật khẩu</label>
      <input id="password2" type="password" name="password2" required><?= field_err($errors, 'password2') ?></div>
    <button class="btn" style="width:100%">Đăng ký</button>
  </form>
  <p class="muted" style="text-align:center">Đã có tài khoản? <a href="<?= url('login.php') ?>">Đăng nhập</a></p>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
