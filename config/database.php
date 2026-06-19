<?php
declare(strict_types=1);

/**
 * Cấu hình kết nối MySQL/MariaDB cho Lab05.
 * Khớp với docker-compose.yml (MySQL trong Docker): root / root.
 * Nếu dùng XAMPP/Laragon: đổi 'password' => '' cho phù hợp.
 */
return [
    'host'     => '127.0.0.1',
    'port'     => '3306',
    'database' => 'training_center_crm',
    'username' => 'root',
    'password' => 'root',
    'charset'  => 'utf8mb4',
];
