<?php

namespace Cactus\Notifications;

use Cactus\Notifications\Clients\MNotifySmsClient;
use Illuminate\Support\Str;
use  Illuminate\Http\Client\Factory as Http;
use RuntimeException;
use MNotify\Client;

class MNotify
{
    /**
     * The MNotify configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * The HttpClient instance, if provided.
     *
     * @var \Illuminate\Http\Client\Factory
     */
    protected $client;

    /**
     * Create a new MNotify instance.
     *
     * @param  array  $config
     * @param  \Illuminate\Http\Client\Factory|null  $client
     * @return void
     */
    public function __construct(array $config = [], ?Http $client = null)
    {
        $this->config = $config;
        $this->client = $client;
    }

    /**
     * Create a new MNotify instance.
     *
     * @param  array  $config
     * @param  \Illuminate\Http\Client\Factory|null  $client
     * @return static
     */
    public static function make(array $config, ?Http $client = null)
    {
        return new static($config, $client);
    }

    /**
     * Create a new MNotify Client.
     *
     * @return \MNotify\Client
     *
     * @throws \RuntimeException
     */
    public function client()
    {
        $apiKey = $this->config['api_key'] ?? null;


        if (empty($apiKey)) {
            $combinations = [
                'api_key',
            ];

            throw new RuntimeException(
                'Please provide your MNotify API credentials. Possible combinations: '
                .join(', ', $combinations)
            );
        }
        
        return new MNotifySmsClient($apiKey, $this->client);
    }
}
