<?php
declare(strict_types=1);
namespace App\Domain\Notification\DTO;
use App\Domain\Notification\Enums\NotificationChannel;
use App\Domain\Notification\Enums\Priority;

readonly class CreateBulkNotificationDTO
{
    public function __construct(
        public NotificationChannel $channel,
        public string   $message,
        public Priority $priority,
        public array    $recipients,
        public string   $idempotencyKey,
    ) {}
}
