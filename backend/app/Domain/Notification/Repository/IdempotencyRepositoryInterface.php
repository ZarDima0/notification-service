<?php

declare(strict_types=1);

namespace App\Domain\Notification\Repository;

interface IdempotencyRepositoryInterface
{
    public function get(string $key): ?string;

    public function set(string $key, string $value, int $ttl = 86400): void;
}
