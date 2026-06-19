<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Ném ra khi vi phạm UNIQUE constraint (MySQL error 1062),
 * ví dụ trùng email lead hoặc trùng mã thanh toán.
 */
class DuplicateRecordException extends RuntimeException
{
}
