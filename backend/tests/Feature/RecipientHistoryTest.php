<?php
declare(strict_types=1);

namespace Feature;

use App\Infrastructure\Providers\SmsProvider;
use Tests\TestCase;

class RecipientHistoryTest extends TestCase
{
    public function test_notifications_flow_bulk_and_history(): void
    {
        $smsProvider = \Mockery::mock(SmsProvider::class);
        $smsProvider
            ->shouldReceive('send')
            ->times(3);
        $this->app->instance(SmsProvider::class, $smsProvider);
        $this->postJson('/api/notifications/bulk', [
            'channel' => 'sms',
            'message' => 'Your code: 1234',
            'priority' => 'high',
            'recipients' => [1, 2, 3],
            'idempotency_key' => 'test-key-001',
        ]);
        $this->assertDatabaseCount('notifications', 3);
        $historyResponse = $this->getJson('/api/notifications/recipient/1?per_page=10');
        $historyResponse->assertOk();
        $historyResponse->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'recipient_id',
                    'status',
                    'channel',
                    'message',
                    'priority',
                    'sent_at',
                    'delivered_at',
                    'created_at',
                ],
            ],
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);

        $this->assertEquals(1, $historyResponse->json('meta.total'));
        $this->assertEquals(
            1,
            $historyResponse->json('data.0.recipient_id')
        );
    }
}
