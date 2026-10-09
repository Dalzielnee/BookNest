<?php
// Cấu hình kết nối CSDL. Có thể ghi đè bằng biến môi trường (dùng cho Docker/hosting).
return [
    'db_host' => getenv('DB_HOST') ?: '127.0.0.1',
    'db_port' => getenv('DB_PORT') ?: '3306',
    'db_name' => getenv('DB_NAME') ?: 'bookstore',
    'db_user' => getenv('DB_USER') ?: 'root',
    'db_pass' => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',
    'site_name' => 'BookNest',
    'per_page' => 8,
];
