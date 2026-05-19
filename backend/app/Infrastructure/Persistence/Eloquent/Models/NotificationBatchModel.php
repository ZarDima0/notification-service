<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $channel
 * @property string $message
 * @property string $priority
 * @property string $status
 * @property string $idempotency_key
 */
class NotificationBatchModel extends Model
{
    protected $table = 'notification_batches';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'channel',
        'message',
        'priority',
        'status',
        'idempotency_key',
    ];

    public function notifications(): HasMany
    {
        return $this->hasMany(
            NotificationModel::class,
            'batch_id'
        );
    }
}
