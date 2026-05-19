<?php

declare(strict_types=1);

namespace App\Domain\Notification\UseCase;

use App\Domain\Notification\Repository\NotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class GetRecipientNotificationsUseCase
{
    public function __construct(private NotificationRepositoryInterface $repository) {}

    public function execute(int $recipientId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->repository
            ->getRecipientNotifications($recipientId, $perPage);
    }
}
