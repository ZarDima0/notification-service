<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Domain\Notification\Enums\NotificationChannel;
use App\Infrastructure\Persistence\Eloquent\Models\NotificationModel;
use App\Infrastructure\Providers\Contracts\NotificationProviderInterface;
use Random\RandomException;
use RuntimeException;

class SmsProvider implements NotificationProviderInterface
{
    /**
     * @throws RandomException
     */
    public function send(NotificationModel $notification): void
    {
        sleep(1);
        if (\random_int(1, 10) <= 3) {
            throw new RuntimeException(
                'SMS provider unavailable'
            );
        }
        logger()->info('Sending sms notification', [
            'notification_id' => $notification->id,
        ]);
    }

    public function supports(string $channel): bool
    {
        return $channel == NotificationChannel::SMS;
    }
}
