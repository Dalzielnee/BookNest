<?php
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('index.php'));
}
csrf_check();

$_SESSION = [];
session_destroy();
session_start();
flash('success', 'Bạn đã đăng xuất.');
redirect(url('index.php'));