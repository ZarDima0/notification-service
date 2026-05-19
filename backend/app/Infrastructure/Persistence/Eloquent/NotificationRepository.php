<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Notification\DTO\CreateBulkNotificationDTO;
use App\Domain\Notification\DTO\NotificationResult;
use App\Domain\Notification\Enums\NotificationStatus;
use App\Domain\Notification\Repository\IdempotencyRepositoryInterface;
use App\Domain\Notification\Repository\NotificationRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\NotificationBatchModel;
use App\Infrastructure\Persistence\Eloquent\Models\NotificationModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

readonly class NotificationRepository implements NotificationRepositoryInterface
{
    public function __construct(
        private IdempotencyRepositoryInterface $idempotency
    ) {}

    public function findBatchByIdempotencyKey(string $key): ?string
    {
        $cached = $this->idempotency->get($key);
        if ($cached) {
            return $cached;
        }

        $batchId = NotificationBatchModel::query()
            ->where('idempotency_key', $key)
            ->value('id');

        if ($batchId) {
            $this->idempotency->set($key, $batchId);
        }

        return $batchId;
    }

    public function createBatchWithNotifications(CreateBulkNotificationDTO $dto): array
    {
        try {
            return DB::transaction(function () use ($dto) {
                $batchId = (string) Str::uuid();
                NotificationBatchModel::query()->create([
                    'id' => $batchId,
                    'channel' => $dto->channel,
                    'message' => $dto->message,
                    'priority' => $dto->priority->value,
                    'status' => 'queued',
                    'idempotency_key' => $dto->idempotencyKey,
                ]);
                $notificationIds = [];
                foreach ($dto->recipients as $recipientId) {
                    $notificationId = (string) Str::uuid();
                    NotificationModel::query()->create([
                        'id' => $notificationId,
                        'batch_id' => $batchId,
                        'recipient_id' => $recipientId,
                        'status' => NotificationStatus::QUEUED->value,
                    ]);
                    $notificationIds[] = $notificationId;
                }
                $this->idempotency->set($dto->idempotencyKey, $batchId);

                return [
                    'batch_id' => $batchId,
                    'notification_ids' => $notificationIds,
                ];
            });

        } catch (QueryException $queryException) {
            if ($this->isDuplicateKeyException($queryException)) {
                return [
                    'batch_id' => $this->findBatchByIdempotencyKey(
                        $dto->idempotencyKey
                    ),
                    'notification_ids' => [],
                ];
            }
            throw $queryException;
        }
    }

    public function findNotificationById(string $id): ?NotificationModel
    {
        return NotificationModel::query()
            ->with('batch')
            ->find($id);
    }

    public function updateNotificationResult(string $id, NotificationResult $result): void
    {
        $data = [
            'status' => $result->status->value,
            'provider_response' => $result->providerResponse,
        ];

        if ($result->sentAt) {
            $data['sent_at'] = $result->sentAt;
        }

        if ($result->deliveredAt) {
            $data['delivered_at'] = $result->deliveredAt;
        }

        if ($result->status === NotificationStatus::FAILED) {
            $data['retry_count'] = DB::raw('retry_count + 1');
        }

        NotificationModel::query()
            ->where('id', $id)
            ->update($data);
    }

    public function updateNotificationStatusConditionally(string $id, array $from, string $to): bool
    {
        $updatedRows = NotificationModel::query()
            ->where('id', $id)
            ->whereIn('status', $from)
            ->update([
                'status' => $to,
            ]);

        return $updatedRows > 0;
    }

    public function getRecipientNotifications(int $recipientId, int $perPage): LengthAwarePaginator
    {
        return NotificationModel::query()
            ->select([
                'notifications.id',
                'notifications.recipient_id',
                'notifications.status',
                'notifications.sent_at',
                'notifications.delivered_at',
                'notification_batches.channel as channel',
                'notification_batches.message as message',
                'notification_batches.priority as priority',
            ])
            ->join(
                'notification_batches',
                'notification_batches.id',
                '=',
                'notifications.batch_id'
            )
            ->where('notifications.recipient_id', $recipientId)
            ->orderByDesc('notifications.created_at')
            ->paginate($perPage);
    }

    private function isDuplicateKeyException(QueryException $queryException): bool
    {
        return str_contains(
            strtolower($queryException->getMessage()),
            'duplicate key'
        );
    }
}
