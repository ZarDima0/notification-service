<?php

declare(strict_types=1);

namespace App\Domain\Notification\UseCase;

use App\Domain\Notification\DTO\NotificationResult;
use App\Domain\Notification\Enums\NotificationChannel;
use App\Domain\Notification\Enums\NotificationStatus;
use App\Domain\Notification\Repository\NotificationRepositoryInterface;
use App\Infrastructure\Providers\NotificationProviderResolver;
use Throwable;

final readonly class SendNotificationUseCase
{
    const string OK_STATUS = 'OK';

    public function __construct(
        private NotificationRepositoryInterface $repository,
        private NotificationProviderResolver $resolver,
    ) {}

    /**
     * @throws Throwable
     */
    public function execute(string $notificationId): void
    {
        $notification = $this->repository->findNotificationById($notificationId);
        if (! $notification) {
            return;
        }
        if ($notification->status == NotificationStatus::DELIVERED->value) {
            return;
        }

        $acquired = $this->repository->updateNotificationStatusConditionally(
            $notificationId,
            from: [NotificationStatus::QUEUED->value, NotificationStatus::FAILED->value],
            to: NotificationStatus::SENT->value
        );

        if (! $acquired) {
            return;
        }

        try {
            $provider = $this->resolver->resolve(NotificationChannel::from($notification->batch->channel));
            $provider->send($notification);
            $this->repository->updateNotificationResult(
                $notificationId,
                NotificationResult::delivered(self::OK_STATUS)
            );
        } catch (Throwable $exception) {
            $this->repository->updateNotificationResult(
                $notificationId,
                NotificationResult::failed($exception->getMessage())
            );

            throw $exception;
        }
    }
}
