<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Notification\Repository\IdempotencyRepositoryInterface;
use App\Domain\Notification\Repository\NotificationRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\NotificationRepository;
use App\Infrastructure\Persistence\Redis\RedisIdempotencyRepository;
use App\Infrastructure\Providers\EmailProvider;
use App\Infrastructure\Providers\NotificationProviderResolver;
use App\Infrastructure\Providers\SmsProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            NotificationProviderResolver::class,
            function ($app) {
                return new NotificationProviderResolver(
                    $app->make(SmsProvider::class),
                    $app->make(EmailProvider::class),
                );
            }
        );

        $this->app->bind(
            NotificationRepositoryInterface::class,
            NotificationRepository::class
        );
        $this->app->bind(
            IdempotencyRepositoryInterface::class,
            RedisIdempotencyRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
