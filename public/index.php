<?php
declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\HealthController;
use App\Controllers\HomeController;
use App\Controllers\LeadController;
use App\Controllers\PaymentController;
use App\Controllers\StatsController;
use App\Core\Router;
use App\Support\Response;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$appConfig = require dirname(__DIR__) . '/config/app.php';

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'");

// Cho php -S phục vụ file tĩnh (css/js) trực tiếp
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $file = __DIR__ . $path;

    if ($path !== '/' && is_file($file)) {
        return false;
    }
}

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';

session_name('TC_CRM_SESSID');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

check_remember_me();
check_session_timeout();

$router = new Router();

$router->get('/', [HomeController::class, 'index']);
$router->get('/health', [HealthController::class, 'index']);
$router->get('/login', [AuthController::class, 'loginView']);
$router->post('/login', [AuthController::class, 'handleLogin']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/register', [AuthController::class, 'registerView']);
$router->post('/register', [AuthController::class, 'handleRegister']);
$router->get('/stats', [StatsController::class, 'index']);

// Admin
$router->get('/admin/users/pending', [AdminController::class, 'pendingUsers']);
$router->post('/admin/users/approve', [AdminController::class, 'approve']);
$router->post('/admin/users/reject', [AdminController::class, 'reject']);

// Module A - Lead tư vấn
$router->get('/leads', [LeadController::class, 'index']);
$router->get('/leads/create', [LeadController::class, 'create']);
$router->post('/leads/store', [LeadController::class, 'store']);
$router->get('/leads/edit', [LeadController::class, 'edit']);
$router->post('/leads/update', [LeadController::class, 'update']);
$router->post('/leads/delete', [LeadController::class, 'delete']);

// Module B - Thanh toán học phí
$router->get('/payments', [PaymentController::class, 'index']);
$router->get('/payments/create', [PaymentController::class, 'create']);
$router->post('/payments/store', [PaymentController::class, 'store']);
$router->get('/payments/edit', [PaymentController::class, 'edit']);
$router->post('/payments/update', [PaymentController::class, 'update']);
$router->post('/payments/delete', [PaymentController::class, 'delete']);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

try {
    $router->dispatch($method, $path);
} catch (Throwable $e) {
    // Ghi log chi tiết cho developer
    $logDir = dirname(__DIR__) . '/storage/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0775, true);
    }
    error_log(
        '[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage()
        . ' in ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL,
        3,
        $logDir . '/app.log'
    );

    if (!empty($appConfig['debug'])) {
        // development: hiện chi tiết để debug
        http_response_code(500);
        header('Content-Type: text/plain; charset=UTF-8');
        echo '[DEBUG] ' . $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine();
        exit;
    }

    // production: chỉ hiện safe message, KHÔNG lộ SQLSTATE
    Response::view('errors/500', ['title' => 'Lỗi hệ thống'], 500);
}
