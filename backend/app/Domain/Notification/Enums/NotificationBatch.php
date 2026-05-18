<?php
declare(strict_types=1);

namespace App\Domain\Notification\Enums;

readonly class NotificationBatch
{
    public function __construct(
        public string   $id,
        public string   $channel,
        public string   $message,
        public Priority $priority,
    ) {}
}
