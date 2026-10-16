<?php
require_once __DIR__ . '/includes/functions.php';

if (current_user()) {
    redirect(url('index.php'));
}

$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $st = db()->prepare('SELECT * FROM users WHERE email = ?');
    $st->execute([$email]);
    $u = $st->fetch();
    if ($u && password_verify($pass, $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$u['id'];
        flash('success', 'Xin chào, ' . $u['full_name'] . '!');
        // chỉ cho phép chuyển hướng nội bộ
        $next = $_GET['next'] ?? '';
        if ($next !== '' && str_starts_with($next, '/') && !str_starts_with($next, '//')) {
            redirect($next);
        }
        redirect($u['role'] === 'admin' ? url('admin/index.php') : url('index.php'));
    }
    $error = 'Email hoặc mật khẩu không đúng.';
}

$title = 'Đăng nhập';
include __DIR__ . '/includes/header.php';
?>
<div class="card form-card">
  <h1>Đăng nhập</h1>
  <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
  <form method="post" novalidate>
    <?= csrf_field() ?>
    <div class="form-group"><label for="email">Email</label>
      <input id="email" type="email" name="email" value="<?= e($email) ?>" required autofocus></div>
    <div class="form-group"><label for="password">Mật khẩu</label>
      <input id="password" type="password" name="password" required></div>
    <button class="btn" style="width:100%">Đăng nhập</button>
  </form>
  <p class="muted" style="text-align:center">Chưa có tài khoản? <a href="<?= url('register.php') ?>">Đăng ký</a></p>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
