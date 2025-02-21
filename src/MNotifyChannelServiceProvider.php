<?php

namespace Cactus\Notifications;

use Cactus\Notifications\Channels\MNotifySmsChannel;
use Illuminate\Http\Client\Factory as Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\ServiceProvider;
use RuntimeException;
use MNotify\Client;

class MNotifyChannelServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mnotify.php', 'mnotify');

        $this->app->singleton(Client::class, function ($app) {
            $config = $app['config']['mnotify'];

            $httpClient = null;

            if ($httpClient = $config['http_client'] ?? null) {
                $httpClient = $app->make($httpClient);
            } elseif (! class_exists('GuzzleHttp\Client')) {
                throw new RuntimeException(
                    'The MNotify client requires a "psr/http-client-implementation" class such as Guzzle.'
                );
            }

            return MNotify::make($app['config']['mnotify'], $httpClient ?? new Http())->client();
        });

        $this->app->bind(MNotifySmsChannel::class, function ($app) {
            return new MNotifySmsChannel(
                $app->make(Client::class),
                $app['config']['mnotify.sms_from']
            );
        });

        Notification::resolved(function (ChannelManager $service) {
            $service->extend('mnotify', function ($app) {
                return $app->make(MNotifySmsChannel::class);
            });
        });
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/mnotify.php' => $this->app->configPath('mnotify.php'),
            ], 'mnotify');
        }
    }
}
