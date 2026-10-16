<?php
require_once __DIR__ . '/functions.php';
$admin = require_admin();
$cur = basename($_SERVER['SCRIPT_NAME']);
$menu = [
    'index.php' => '📊 Tổng quan',
    'products.php' => '📚 Quản lý sách',
    'categories.php' => '🏷️ Danh mục',
    'orders.php' => '🧾 Đơn hàng',
    'users.php' => '👥 Người dùng',
];
?><!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(($title ?? 'Quản trị') . ' | Admin BookNest') ?></title>
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
<div class="admin">
  <aside class="admin-side">
    <a class="brand" href="<?= url('admin/index.php') ?>">⚙️ BookNest Admin</a>
    <?php foreach ($menu as $file => $label): ?>
      <a href="<?= url('admin/' . $file) ?>" class="<?= $cur === $file ? 'active' : '' ?>"><?= $label ?></a>
    <?php endforeach; ?>
    <a href="<?= url('index.php') ?>">↗ Xem website</a>
    <a href="<?= url('logout.php') ?>">Đăng xuất (<?= e($admin['full_name']) ?>)</a>
  </aside>
  <section class="admin-main">
    <?= flash_render() ?>
