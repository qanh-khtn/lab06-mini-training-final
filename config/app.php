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
    
    // Facebook Webhook configs (mô phỏng)
    'fb_verify_token' => 'my_fb_verify_token_123',
    'fb_app_secret'   => 'my_fb_app_secret_123',
];
