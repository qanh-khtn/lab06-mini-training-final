<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Repositories\LeadRepository;
use App\Services\LeadService;
use App\Support\Response;

class WebhookController
{
    /**
     * GET /webhooks/facebook
     * Xác thực Webhook Challenge theo chuẩn Facebook Messenger
     */
    public function verify(): void
    {
        $config = require root_path('config/app.php');
        $verifyToken = $config['fb_verify_token'] ?? '';

        $mode = $_GET['hub_mode'] ?? '';
        $token = $_GET['hub_verify_token'] ?? '';
        $challenge = $_GET['hub_challenge'] ?? '';

        if ($mode === 'subscribe' && $token === $verifyToken) {
            header('Content-Type: text/plain');
            echo $challenge;
            exit;
        }

        http_response_code(403);
        echo 'Verification token mismatch';
        exit;
    }

    /**
     * POST /webhooks/facebook
     * Nhận payload tin nhắn & tạo lead mới (xác thực chữ ký HMAC SHA256)
     */
    public function handle(): void
    {
        $config = require root_path('config/app.php');
        $appSecret = $config['fb_app_secret'] ?? '';

        // Chặn cứng nếu secret rỗng — nếu không, hash_hmac('sha256', $body, '')
        // vẫn tạo ra chữ ký "hợp lệ" mà kẻ tấn công tự tính được (secret rỗng
        // tương đương không xác thực gì cả).
        if ($appSecret === '') {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Webhook chưa được cấu hình secret.']);
            exit;
        }

        // 1. Lấy chữ ký X-Hub-Signature-256 từ Header
        $signatureHeader = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
        if (str_starts_with($signatureHeader, 'sha256=')) {
            $signature = substr($signatureHeader, 7);
        } else {
            $signature = $signatureHeader;
        }

        // 2. Lấy dữ liệu raw body
        $rawBody = file_get_contents('php://input');

        // 3. Xác thực HMAC SHA256 sử dụng app secret và so sánh an toàn bằng hash_equals chống timing attack
        $calculated = hash_hmac('sha256', $rawBody, $appSecret);
        if (!hash_equals($calculated, $signature)) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Chữ ký HMAC không hợp lệ.']);
            exit;
        }

        // 4. Giải mã JSON
        $data = json_decode($rawBody, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            http_response_code(400);
            echo 'Invalid JSON payload';
            exit;
        }

        // 5. Ánh xạ dữ liệu sang Lead
        $leadData = $this->parseLeadFromPayload($data);

        // 5b. Validate bằng đúng logic LeadService::validate() trước khi ghi DB —
        // dữ liệu từ webhook (đặc biệt course_interest/full_name trích qua regex)
        // không đáng tin như form nội bộ, không được bỏ qua bước này.
        $errors = (new LeadService(new LeadRepository(Database::connection())))->validate($leadData);
        if ($errors !== []) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Dữ liệu lead không hợp lệ.', 'details' => $errors]);
            exit;
        }

        // 6. Lưu lead vào CSDL bằng Repository
        try {
            $repo = new LeadRepository(Database::connection());
            $leadId = $repo->create($leadData);

            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode(['ok' => true, 'id' => $leadId, 'message' => 'Đã lưu lead thành công.']);
        } catch (\App\Core\DuplicateRecordException $e) {
            http_response_code(409);
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Lỗi hệ thống khi lưu lead.']);
        }
        exit;
    }

    /**
     * Trích xuất thông tin Lead từ payload Facebook Messenger hoặc JSON trực tiếp
     */
    private function parseLeadFromPayload(array $payload): array
    {
        // Kiểm tra nếu là Direct JSON Payload (để test nhanh qua curl)
        if (isset($payload['full_name']) || isset($payload['email'])) {
            return [
                'full_name'       => trim((string)($payload['full_name'] ?? 'Facebook User')),
                'email'           => trim((string)($payload['email'] ?? ('fb_' . time() . '@facebook.com'))),
                'phone'           => trim((string)($payload['phone'] ?? '')),
                'course_interest' => trim((string)($payload['course_interest'] ?? 'web')),
                'care_status'     => 'new',
                'note'            => trim((string)($payload['note'] ?? 'Nguồn: Facebook Messenger (Direct)')),
                'assigned_to'     => null, // Admin sẽ phân công sau
            ];
        }

        // Trường hợp là payload chuẩn Facebook Messenger
        $name = 'Facebook User';
        $email = 'fb_' . time() . '@facebook.com';
        $phone = '';
        $course = 'web';
        $note = 'Nguồn: Facebook Messenger';

        if (isset($payload['entry'][0]['messaging'][0]['message']['text'])) {
            $text = $payload['entry'][0]['messaging'][0]['message']['text'];
            
            // Regex trích xuất thông tin từ message text
            // Ví dụ: "lead: Nguyen Van A, email: anguyen@gmail.com, sdt: 0987654321, khoa: web, note: Can tu van gap"
            if (preg_match('/(?:lead|họ tên|name)[:\-]\s*([^,]+)/iu', $text, $matches)) {
                $name = trim($matches[1]);
            }
            if (preg_match('/(?:email|thư điện tử)[:\-]\s*([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/iu', $text, $matches)) {
                $email = trim($matches[1]);
            }
            if (preg_match('/(?:phone|sđt|sdt|đt|điện thoại)[:\-]\s*([0-9\s]+)/iu', $text, $matches)) {
                $phone = trim(str_replace(' ', '', $matches[1]));
            }
            if (preg_match('/(?:course|khóa học|khoa hoc|khóa)[:\-]\s*([a-z0-9]+)/iu', $text, $matches)) {
                $course = trim(strtolower($matches[1]));
            }
            if (preg_match('/(?:note|ghi chú|ghi chu)[:\-]\s*(.+)$/iu', $text, $matches)) {
                $note .= ' - ' . trim($matches[1]);
            } else {
                $note .= ' - Nội dung: ' . $text;
            }
        }

        return [
            'full_name'       => $name,
            'email'           => $email,
            'phone'           => $phone,
            'course_interest' => $course,
            'care_status'     => 'new',
            'note'            => $note,
            'assigned_to'     => null,
        ];
    }
}
