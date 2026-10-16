<?php
require_once __DIR__ . '/functions.php';
$cfg = require __DIR__ . '/../config.php';
$user = current_user();
$pageTitle = ($title ?? '') ? $title . ' | ' . $cfg['site_name'] : $cfg['site_name'];
?><!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
<header class="site-header">
  <div class="container nav">
    <a class="brand" href="<?= url('index.php') ?>">📚 <?= e($cfg['site_name']) ?></a>
    <form class="search" action="<?= url('products.php') ?>" method="get">
      <input type="search" name="q" placeholder="Tìm sách, tác giả..." value="<?= e($_GET['q'] ?? '') ?>">
      <button type="submit">Tìm</button>
    </form>
    <nav class="menu">
      <a href="<?= url('index.php') ?>">Trang chủ</a>
      <a href="<?= url('products.php') ?>">Sách</a>
      <a href="<?= url('cart.php') ?>">Giỏ hàng <span class="badge"><?= cart_count() ?></span></a>
      <?php if ($user): ?>
        <a href="<?= url('orders.php') ?>">Đơn hàng</a>
        <a href="<?= url('profile.php') ?>">👤 <?= e($user['full_name']) ?></a>
        <?php if ($user['role'] === 'admin'): ?><a href="<?= url('admin/index.php') ?>">Quản trị</a><?php endif; ?>
        <a href="<?= url('logout.php') ?>">Đăng xuất</a>
      <?php else: ?>
        <a href="<?= url('login.php') ?>">Đăng nhập</a>
        <a class="btn btn-sm" href="<?= url('register.php') ?>">Đăng ký</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="container">
<?= flash_render() ?>
