<?php

namespace App\Domain\Notification\DTO;

use App\Domain\Notification\Enums\NotificationStatus;
use Carbon\Carbon;

final class NotificationResult
{
    private function __construct(
        public readonly NotificationStatus $status,
        public readonly string $providerResponse,
        public readonly ?Carbon $sentAt = null,
        public readonly ?Carbon $deliveredAt = null,
    ) {}

    public static function delivered(string $providerResponse): self
    {
        return new self(
            status: NotificationStatus::DELIVERED,
            providerResponse: $providerResponse,
            sentAt: now(),
            deliveredAt: now(),
        );
    }

    public static function failed(string $providerResponse): self
    {
        return new self(
            status: NotificationStatus::FAILED,
            providerResponse: $providerResponse,
        );
    }
}
