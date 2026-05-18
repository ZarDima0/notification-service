<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Notification\Enums\NotificationStatus;
use App\Domain\Notification\UseCase\SendNotificationUseCase;
use App\Infrastructure\Persistence\Eloquent\Models\NotificationModel;
use App\Infrastructure\Providers\EmailProvider;
use App\Infrastructure\Providers\SmsProvider;
use App\Jobs\ProcessNotificationJob;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class BulkNotificationTest extends TestCase
{
    public function test_bulk_sms_notification_is_created_and_delivered(): void
    {
        $smsProvider = \Mockery::mock(SmsProvider::class);

        $smsProvider
            ->shouldReceive('send')
            ->times(3);

        $this->app->instance(SmsProvider::class, $smsProvider);

        $response = $this->postJson('/api/notifications/bulk', [
            'channel' => 'sms',
            'message' => 'Your code: 1234',
            'priority' => 'high',
            'recipients' => [1, 2, 3],
            'idempotency_key' => 'test-key-001',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'batch_id',
                'status',
            ]);

        $this->assertDatabaseHas('notification_batches', [
            'channel' => 'sms',
            'idempotency_key' => 'test-key-001',
        ]);

        $this->assertDatabaseCount('notifications', 3);

        $this->assertDatabaseHas('notifications', [
            'recipient_id' => 1,
            'status' => NotificationStatus::DELIVERED->value,
        ]);

        $this->assertDatabaseHas('notifications', [
            'recipient_id' => 2,
            'status' => NotificationStatus::DELIVERED->value,
        ]);

        $this->assertDatabaseHas('notifications', [
            'recipient_id' => 3,
            'status' => NotificationStatus::DELIVERED->value,
        ]);
    }

    public function test_duplicate_request_returns_same_batch_id(): void
    {
        $smsProvider = \Mockery::mock(SmsProvider::class);

        $smsProvider
            ->shouldReceive('send')
            ->times(3);

        $this->app->instance(SmsProvider::class, $smsProvider);

        $payload = [
            'channel' => 'sms',
            'message' => 'Your code: 1234',
            'priority' => 'high',
            'recipients' => [1, 2, 3],
            'idempotency_key' => 'duplicate-key-001',
        ];

        $firstResponse = $this->postJson(
            '/api/notifications/bulk',
            $payload
        );

        $secondResponse = $this->postJson(
            '/api/notifications/bulk',
            $payload
        );

        $this->assertEquals(
            $firstResponse->json('batch_id'),
            $secondResponse->json('batch_id')
        );

        $this->assertDatabaseCount('notification_batches', 1);

        $this->assertDatabaseCount('notifications', 3);
    }

    public function test_sms_provider_is_called_for_sms_channel(): void
    {
        $smsProvider = \Mockery::mock(SmsProvider::class);

        $smsProvider
            ->shouldReceive('send')
            ->times(2);

        $this->app->instance(SmsProvider::class, $smsProvider);

        $this->postJson('/api/notifications/bulk', [
            'channel' => 'sms',
            'message' => 'SMS message',
            'priority' => 'high',
            'recipients' => [1, 2],
            'idempotency_key' => 'sms-provider-test',
        ])->assertStatus(200);
    }

    public function test_email_provider_is_called_for_email_channel(): void
    {
        $emailProvider = \Mockery::mock(EmailProvider::class);

        $emailProvider
            ->shouldReceive('send')
            ->times(2);

        $this->app->instance(EmailProvider::class, $emailProvider);

        $this->postJson('/api/notifications/bulk', [
            'channel' => 'email',
            'message' => 'Email message',
            'priority' => 'low',
            'recipients' => [1, 2],
            'idempotency_key' => 'email-provider-test',
        ])->assertStatus(200);
    }

    public function test_validation_fails_without_required_fields(): void
    {
        $this->postJson('/api/notifications/bulk', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'channel',
                'message',
                'priority',
                'recipients',
                'idempotency_key',
            ]);
    }

    public function test_high_priority_notifications_dispatched_to_high_queue(): void
    {
        Queue::fake();

        $this->postJson('/api/notifications/bulk', [
            'channel' => 'sms',
            'message' => 'Urgent message',
            'priority' => 'high',
            'recipients' => [1],
            'idempotency_key' => 'priority-high-test',
        ])->assertStatus(200);

        Queue::assertPushedOn(
            'high',
            ProcessNotificationJob::class
        );
    }

    public function test_low_priority_notifications_dispatched_to_low_queue(): void
    {
        Queue::fake();

        $this->postJson('/api/notifications/bulk', [
            'channel' => 'email',
            'message' => 'Marketing message',
            'priority' => 'low',
            'recipients' => [1],
            'idempotency_key' => 'priority-low-test',
        ])->assertStatus(200);

        Queue::assertPushedOn(
            'low',
            ProcessNotificationJob::class
        );
    }

    public function test_notification_becomes_failed_when_provider_throws_exception(): void
    {
        $smsProvider = \Mockery::mock(SmsProvider::class);

        $smsProvider
            ->shouldReceive('send')
            ->andThrow(
                new RuntimeException('SMS provider unavailable')
            );

        $this->app->instance(SmsProvider::class, $smsProvider);

        try {
            $this->postJson('/api/notifications/bulk', [
                'channel' => 'sms',
                'message' => 'Failure test',
                'priority' => 'high',
                'recipients' => [1],
                'idempotency_key' => 'failed-test',
            ]);
        } catch (\Throwable) {
        }

        $this->assertDatabaseHas('notifications', [
            'recipient_id' => 1,
            'status' => NotificationStatus::FAILED->value,
        ]);
    }

    public function test_notification_is_not_sent_twice_after_delivery(): void
    {
        $smsProvider = \Mockery::mock(SmsProvider::class);

        $smsProvider
            ->shouldReceive('send')
            ->once();

        $this->app->instance(SmsProvider::class, $smsProvider);

        $this->postJson('/api/notifications/bulk', [
            'channel' => 'sms',
            'message' => 'Exactly once test',
            'priority' => 'high',
            'recipients' => [1],
            'idempotency_key' => 'exactly-once-test',
        ]);

        $notification = NotificationModel::query()->first();

        $useCase = app(
            SendNotificationUseCase::class
        );

        $useCase->execute((string)$notification->id);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'status' => NotificationStatus::DELIVERED->value,
        ]);
    }

    public function test_notification_retry_count_is_incremented_on_failure(): void
    {
        $smsProvider = \Mockery::mock(SmsProvider::class);

        $smsProvider
            ->shouldReceive('send')
            ->andThrow(
                new RuntimeException('Temporary provider error')
            );

        $this->app->instance(SmsProvider::class, $smsProvider);

        try {
            $this->postJson('/api/notifications/bulk', [
                'channel' => 'sms',
                'message' => 'Retry test',
                'priority' => 'high',
                'recipients' => [1],
                'idempotency_key' => 'retry-test',
            ]);
        } catch (\Throwable) {
        }

        $this->assertDatabaseHas('notifications', [
            'recipient_id' => 1,
            'retry_count' => 1,
        ]);
    }

    public function test_notification_contains_provider_response(): void
    {
        $smsProvider = \Mockery::mock(SmsProvider::class);

        $smsProvider
            ->shouldReceive('send')
            ->once();

        $this->app->instance(SmsProvider::class, $smsProvider);

        $this->postJson('/api/notifications/bulk', [
            'channel' => 'sms',
            'message' => 'Provider response test',
            'priority' => 'high',
            'recipients' => [1],
            'idempotency_key' => 'provider-response-test',
        ]);

        $notification = NotificationModel::query()->first();

        $this->assertEquals(
            NotificationStatus::DELIVERED,
            $notification->status
        );

        $this->assertNotNull($notification->delivered_at);
    }
}
