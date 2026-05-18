<?php
declare(strict_types=1);

namespace App\Domain\Notification\Enums;

class Notification
{
    public function __construct(
        public readonly string $id,
        public readonly string $batchId,
        public readonly int $recipientId,
        public NotificationStatus $status = NotificationStatus::QUEUED,
    ) {}
}
