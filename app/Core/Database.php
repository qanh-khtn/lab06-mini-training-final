<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Quản lý kết nối PDO tới MySQL theo chuẩn Lab05.
 * Dùng singleton để tránh mở lại kết nối nhiều lần trong một request.
 */
class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        /** @var array{host:string,port:string,database:string,username:string,password:string,charset:string} $config */
        $config = require root_path('config/database.php');

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        self::$pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        return self::$pdo;
    }
}
