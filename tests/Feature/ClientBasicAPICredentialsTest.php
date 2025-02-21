<?php

namespace Cactus\Notifications\Tests\Feature;

use MNotify\Client;
use MNotify\Client\Credentials\Basic;

class ClientBasicAPICredentialsTest extends FeatureTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('mnotify.api_key', 'my_api_key');
        $app['config']->set('mnotify.api_secret', 'my_secret');
    }

    public function testClientCreatedWithBasicAPICredentials()
    {
        $credentials = $this->app->make(Client::class)->getCredentials();

        $this->assertInstanceOf(Basic::class, $credentials);
        $this->assertEquals(['api_key' => 'my_api_key', 'api_secret' => 'my_secret'], $credentials->asArray());
    }
}
