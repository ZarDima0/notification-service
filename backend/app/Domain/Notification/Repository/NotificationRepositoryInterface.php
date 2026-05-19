<?php

declare(strict_types=1);

namespace App\Domain\Notification\Repository;

use App\Domain\Notification\DTO\CreateBulkNotificationDTO;
use App\Domain\Notification\DTO\NotificationResult;
use App\Infrastructure\Persistence\Eloquent\Models\NotificationModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationRepositoryInterface
{
    public function findBatchByIdempotencyKey(string $key): ?string;

    public function createBatchWithNotifications(CreateBulkNotificationDTO $dto): array;

    public function findNotificationById(string $id): ?NotificationModel;

    public function updateNotificationResult(string $id, NotificationResult $result): void;

    public function updateNotificationStatusConditionally(string $id, array $from, string $to): bool;

    public function getRecipientNotifications(int $recipientId, int $perPage): LengthAwarePaginator;
}
