<?php

namespace Cactus\Notifications\Facades;

use Illuminate\Support\Facades\Facade;
use MNotify\Client;

class MNotify extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Client::class;
    }
}
