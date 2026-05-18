<?php
declare(strict_types=1);

namespace App\Domain\Notification\UseCase;
use App\Domain\Notification\DTO\CreateBulkNotificationDTO;
use App\Domain\Notification\Repository\NotificationRepositoryInterface;
use App\Jobs\ProcessNotificationJob;

final readonly class CreateBulkNotificationUseCase
{
    public function __construct(
        private NotificationRepositoryInterface $repository
    ) {}

    public function execute(CreateBulkNotificationDTO $bulkNotificationDTO): string
    {
        $existingBatchId = $this->repository->findBatchByIdempotencyKey($bulkNotificationDTO->idempotencyKey);
        if ($existingBatchId) {
            return $existingBatchId;
        }
        $result = $this->repository->createBatchWithNotifications($bulkNotificationDTO);

        foreach ($result['notification_ids'] as $notificationId) {
            ProcessNotificationJob::dispatch($notificationId)
                ->onQueue($bulkNotificationDTO->priority->value);
        }
        return $result['batch_id'];
    }
}
