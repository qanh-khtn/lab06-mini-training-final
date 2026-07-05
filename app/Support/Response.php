<?php
declare(strict_types=1);

namespace App\Support;

class Response
{
    public static function view(string $view, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=UTF-8');
        extract($data, EXTR_SKIP);

        if (!is_file(\view_path($view))) {
            self::notFound();
            return;
        }

        require \view_path('layout');
        exit;
    }

    public static function json(int $status, array $data): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function redirect(string $url, int $status = 302): void
    {
        \redirect($url, $status);
    }

    /**
     * Xuất CSV cho Excel (UTF-8 BOM để hiển thị đúng tiếng Việt).
     * @param string[] $headers Tên cột (dòng đầu)
     * @param array<int, array<int, string>> $rows Mỗi dòng là mảng giá trị theo đúng thứ tự $headers
     */
    public static function csv(string $filename, array $headers, array $rows): void
    {
        http_response_code(200);
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo "\xEF\xBB\xBF"; // BOM UTF-8

        $out = fopen('php://output', 'w');
        fputcsv($out, $headers);
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }

    public static function notFound(string $message = 'Không tìm thấy trang bạn yêu cầu.'): void
    {
        self::view('errors/404', [
            'title' => 'Không tìm thấy trang',
            'message' => $message,
        ], 404);
    }

    public static function methodNotAllowed(array $allowedMethods = []): void
    {
        if ($allowedMethods !== []) {
            header('Allow: ' . implode(', ', $allowedMethods));
        }

        self::view('errors/405', [
            'title' => 'Phương thức không được hỗ trợ',
            'allowedMethods' => $allowedMethods,
        ], 405);
    }
}
