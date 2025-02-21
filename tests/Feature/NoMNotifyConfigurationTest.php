<?php

namespace Cactus\Notifications\Tests\Feature;

use RuntimeException;
use MNotify\Client;

class NoMNotifyConfigurationTest extends FeatureTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('mnotify.api_key', 'my_api_key');
    }

    public function testWhenNoConfigurationIsGivenExceptionIsRaised()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Please provide your MNotify API credentials.');

        app(Client::class);
    }
}
