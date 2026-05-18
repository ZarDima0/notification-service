<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Redis;
use App\Domain\Notification\Repository\IdempotencyRepositoryInterface;
use Illuminate\Support\Facades\Redis;
class RedisIdempotencyRepository implements IdempotencyRepositoryInterface
{
    private const PREFIX = 'idempotency:';

    public function get(string $key): ?string
    {
        return Redis::get(self::PREFIX . $key) ?: null;
    }

    public function set(string $key, string $value, int $ttl = 86400): void
    {
        Redis::setex(self::PREFIX . $key, $ttl, $value);
    }
}
