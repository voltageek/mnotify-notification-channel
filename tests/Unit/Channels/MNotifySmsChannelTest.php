<?php

namespace Cactus\Notifications\Tests\Unit\Channels;

use Hamcrest\Core\IsEqual;
use Cactus\Notifications\Channels\MNotifySmsChannel;
use Cactus\Notifications\Messages\MNotifyMessage;
use Cactus\Notifications\Notifiable;
use Cactus\Notifications\Notification;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use MNotify\Client;
use MNotify\SMS\Message\SMS;

class MNotifySmsChannelTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testSmsIsSentViaMNotify()
    {
        $notification = new NotificationMNotifySmsChannelTestNotification;
        $notifiable = new NotificationMNotifySmsChannelTestNotifiable;

        $channel = new MNotifySmsChannel(
            $mnotify = m::mock(Client::class), '4444444444'
        );

        $mockSms = (new SMS(
            '5555555555',
            '4444444444',
            'this is my message',
            'text'
        ));

        $mnotify->shouldReceive('sms->send')
            ->with(IsEqual::equalTo($mockSms))
            ->once();

        $channel->send($notifiable, $notification);
    }

    public function testSmsWillSendAsUnicode()
    {
        $notification = new NotificationMNotifyUnicodeSmsChannelTestNotification;
        $notifiable = new NotificationMNotifySmsChannelTestNotifiable;

        $channel = new MNotifySmsChannel(
            $mnotify = m::mock(Client::class), '4444444444'
        );

        $mockSms = (new SMS(
            '5555555555',
            '4444444444',
            'this is my message',
            'unicode'
        ));

        $mnotify->shouldReceive('sms->send')
               ->with(IsEqual::equalTo($mockSms))
               ->once();

        $channel->send($notifiable, $notification);
    }

    public function testSmsIsSentViaMNotifyWithCustomClient()
    {
        $customMNotify = m::mock(Client::class);
        $customMNotify->shouldReceive('sms->send')
            ->with(IsEqual::equalTo(new SMS(
                '5555555555',
                '4444444444',
                'this is my message'
            )))
            ->once();

        $notification = new NotificationMNotifySmsChannelTestCustomClientNotification($customMNotify);
        $notifiable = new NotificationMNotifySmsChannelTestNotifiable;

        $channel = new MNotifySmsChannel(
            $mnotify = m::mock(Client::class), '4444444444'
        );

        $mnotify->shouldNotReceive('sms->send');

        $channel->send($notifiable, $notification);
    }

    public function testSmsIsSentViaMNotifyWithCustomFrom()
    {
        $notification = new NotificationMNotifySmsChannelTestCustomFromNotification;
        $notifiable = new NotificationMNotifySmsChannelTestNotifiable;

        $channel = new MNotifySmsChannel(
            $mnotify = m::mock(Client::class), '4444444444'
        );

        $mockSms = (new SMS(
            '5555555555',
            '5554443333',
            'this is my message'
        ));

        $mnotify->shouldReceive('sms->send')
            ->with(IsEqual::equalTo($mockSms))
            ->once();

        $channel->send($notifiable, $notification);
    }

    public function testSmsIsSentViaMNotifyWithCustomFromAndClient()
    {
        $customMNotify = m::mock(Client::class);

        $mockSms = new SMS(
            '5555555555',
            '5554443333',
            'this is my message',
        );

        $customMNotify->shouldReceive('sms->send')
            ->with(IsEqual::equalTo($mockSms))
            ->once();

        $notification = new NotificationMNotifySmsChannelTestCustomFromAndClientNotification($customMNotify);
        $notifiable = new NotificationMNotifySmsChannelTestNotifiable;

        $channel = new MNotifySmsChannel(
            $mnotify = m::mock(Client::class), '4444444444'
        );

        $mnotify->shouldNotReceive('sms->send');

        $channel->send($notifiable, $notification);
    }


    public function testCallbackIsApplied()
    {
        $notification = new NotificationMNotifySmsChannelTestCallback;
        $notifiable = new NotificationMNotifySmsChannelTestNotifiable;

        $channel = new MNotifySmsChannel(
            $mnotify = m::mock(Client::class), '4444444444'
        );

        $mockSms = (new SMS(
            '5555555555',
            '4444444444',
            'this is my message'
        ));

        $mockSms->setDeliveryReceiptCallback('https://example.com');

        $mnotify->shouldReceive('sms->send')
               ->with(IsEqual::equalTo($mockSms))
               ->once();

        $channel->send($notifiable, $notification);
    }
}

class NotificationMNotifySmsChannelTestNotifiable
{
    use Notifiable;

    public $phone_number = '5555555555';

    public function routeNotificationForMNotify($notification)
    {
        return $this->phone_number;
    }
}

class NotificationMNotifySmsChannelTestNotification extends Notification
{
    public function toMNotify($notifiable)
    {
        return new MNotifyMessage('this is my message');
    }
}

class NotificationMNotifyUnicodeSmsChannelTestNotification extends Notification
{
    public function toMNotify($notifiable)
    {
        return (new MNotifyMessage('this is my message'))->unicode();
    }
}

class NotificationMNotifySmsChannelTestCustomClientNotification extends Notification
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function toMNotify($notifiable)
    {
        return (new MNotifyMessage('this is my message'))->usingClient($this->client);
    }
}

class NotificationMNotifySmsChannelTestCustomFromNotification extends Notification
{
    public function toMNotify($notifiable)
    {
        return (new MNotifyMessage('this is my message'))->from('5554443333');
    }
}

class NotificationMNotifySmsChannelTestCustomFromAndClientNotification extends Notification
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function toMNotify($notifiable)
    {
        return (new MNotifyMessage('this is my message'))->from('5554443333')->usingClient($this->client);
    }
}


class NotificationMNotifySmsChannelTestCallback extends Notification
{
    public function toMNotify($notifiable)
    {
        return (new MNotifyMessage('this is my message'))->statusCallback('https://example.com');
    }
}
