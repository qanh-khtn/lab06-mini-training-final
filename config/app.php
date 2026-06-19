<?php
declare(strict_types=1);

/**
 * Cấu hình ứng dụng Mini Training Center CRM (Lab05).
 * Tách khỏi Controller/View để dễ đổi khi deploy.
 */
return [
    'name'  => 'Mini Training Center CRM',
    // Đổi 'production' khi deploy thật
    'env'   => 'development',
    'debug' => true,
];
