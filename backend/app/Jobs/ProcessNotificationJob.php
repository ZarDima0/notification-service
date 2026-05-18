<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Notification\UseCase\SendNotificationUseCase;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 5;
    public int $backoff = 10;
    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly string $notificationId
    ) {}

    /**
     * Execute the job.
     * @throws Throwable
     */
    public function handle(SendNotificationUseCase $useCase): void
    {
        $useCase->execute($this->notificationId);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Notification failed after all retries', [
            'notification_id' => $this->notificationId,
            'error' => $exception->getMessage(),
        ]);
    }
}
