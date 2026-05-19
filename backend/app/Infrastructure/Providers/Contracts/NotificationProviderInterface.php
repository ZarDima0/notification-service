<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers\Contracts;

use App\Infrastructure\Persistence\Eloquent\Models\NotificationModel;

interface NotificationProviderInterface
{
    public function send(NotificationModel $notification): void;

    public function supports(string $channel): bool;
}
