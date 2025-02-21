<?php

namespace Cactus\Notifications\Messages;

class MNotifyMessage
{
    /**
     * The message content.
     *
     * @var string
     */
    public $content;

    /**
     * The phone number the message should be sent from.
     *
     * @var string
     */
    public $from;

    /**
     * The message type.
     *
     * @var string
     */
    public $type = 'text';

    /**
     * The custom MNotify client instance.
     *
     * @var \MNotify\Client|null
     */
    public $client;


    /**
     * The webhook to be called with status updates.
     *
     * @var string
     */
    public $statusCallback = '';

    /**
     * Create a new message instance.
     *
     * @param  string  $content
     * @return void
     */
    public function __construct($content = '')
    {
        $this->content = $content;
    }

    /**
     * Set the message content.
     *
     * @param  string  $content
     * @return $this
     */
    public function content($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Set the phone number the message should be sent from.
     *
     * @param  string  $from
     * @return $this
     */
    public function from($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Set the message type.
     *
     * @return $this
     */
    public function unicode()
    {
        $this->type = 'unicode';

        return $this;
    }


    /**
     * Set the webhook callback URL to update the message status.
     *
     * @param  string  $callback
     * @return $this
     */
    public function statusCallback(string $callback)
    {
        $this->statusCallback = $callback;

        return $this;
    }

    /**
     * Set the MNotify client instance.
     *
     * @param  \MNotify\Client  $client
     * @return $this
     */
    public function usingClient($client)
    {
        $this->client = $client;

        return $this;
    }
}
