<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Domain\Notification\Enums\NotificationChannel;
use App\Domain\Notification\Enums\NotificationStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property NotificationStatus $status
 * @property NotificationChannel $channel
 * @property NotificationBatchModel $batch
 * @property Carbon $delivered_at
 */
class NotificationModel extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'id',
        'batch_id',
        'recipient_id',
        'status',
        'channel',
        'retry_count',
        'provider_response',
        'sent_at',
        'delivered_at',
    ];

    protected $casts = [
        'channel' => NotificationChannel::class,
        'status'  => NotificationStatus::class,
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function batch(): BelongsTo
    {
        return $this->belongsTo(
            NotificationBatchModel::class,
            'batch_id'
        );
    }
}
