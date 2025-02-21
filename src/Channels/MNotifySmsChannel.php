<?php

namespace Cactus\Notifications\Channels;

use Cactus\Notifications\Messages\MNotifyMessage;
use Illuminate\Notifications\Notification;
use Cactus\Notifications\Clients\MNotifySmsClient as MNotifyClient;

class MNotifySmsChannel
{
    /**
     * The MNotify client instance.
     *
     * @var \MNotify\Client
     */
    protected $client;

    /**
     * The Sender ID notifications should be sent from.
     *
     * @var string
     */
    protected $from;

    /**
     * Create a new MNotify channel instance.
     *
     * @param  \MNotify\Client  $client
     * @param  string  $from
     * @return void
     */
    public function __construct(MNotifyClient $client, $from)
    {
        $this->from = $from;
        $this->client = $client;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Cactus\Notifications\Notification  $notification
     * @return \MNotify\SMS\Collection|null
     */
    public function send($notifiable, Notification $notification)
    {
        if (! $to = $notifiable->routeNotificationFor('mnotify', $notification)) {
            return;
        }

        $message = $notification->toMNotify($notifiable);

        if (is_string($message)) {
            $message = new MNotifyMessage($message);
        }


        return ($message->client ?? $this->client)->sendSms(
            to: $to,
            message: trim($message->content),
            senderId: $message->from ?: $this->from
        );
    }
}
