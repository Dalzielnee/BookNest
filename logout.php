<?php
require_once __DIR__ . '/includes/functions.php';
$_SESSION = [];
session_destroy();
session_start();
flash('success', 'Bạn đã đăng xuất.');
redirect(url('index.php'));
