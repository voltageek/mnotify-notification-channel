<?php

namespace Cactus\Notifications\Tests\Feature;

use Cactus\Notifications\MNotifyChannelServiceProvider;
use Orchestra\Testbench\TestCase;

abstract class FeatureTestCase extends TestCase
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [MNotifyChannelServiceProvider::class];
    }
}
