<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Domain\Notification\Enums\NotificationChannel;
use App\Infrastructure\Providers\Contracts\NotificationProviderInterface;
use InvalidArgumentException;

readonly class NotificationProviderResolver
{
    public function __construct(
        private SmsProvider $smsProvider,
        private EmailProvider $emailProvider,
    ) {}

    public function resolve(NotificationChannel $channel): NotificationProviderInterface
    {
        return match ($channel) {
            NotificationChannel::SMS => $this->smsProvider,
            NotificationChannel::EMAIL => $this->emailProvider,
            default => throw new InvalidArgumentException('Unsupported channel'),
        };
    }
}
