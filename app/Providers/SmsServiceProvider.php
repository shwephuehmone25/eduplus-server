<?php

namespace App\Providers;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\ServiceProvider;
use NotificationChannels\Smspoh\SmspohApi;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Smspoh\SmspohChannel;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->app->bind(SmspohApi::class, static fn () => new SmspohApi(
            config('services.smspoh.token'),
            app(HttpClient::class)
        ));

        Notification::resolved(static function (ChannelManager $service) {
            $service->extend('smspoh', static fn ($app) => new SmspohChannel(
                $app[SmspohApi::class],
                $app['config']['services.smspoh.sender'])
            );
        });
    }
}