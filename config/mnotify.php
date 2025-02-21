<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sender ID
    |--------------------------------------------------------------------------
    |
    | This configuration option defines the sender that will be used as
    | the "from" number for all outgoing text messages. You should provide
    | the senderID you have already reserved within your MNotify dashboard.
    |
    */

    'sms_from' => env('MNOTIFY_SMS_FROM'),

    /*
    |--------------------------------------------------------------------------
    | API Credentials
    |--------------------------------------------------------------------------
    |
    | The following configuration options contain your API credentials, which
    | may be accessed from your MNotify dashboard. These credentials may be
    | used to authenticate with the MNotify API so you may send messages.
    |
    */

    'api_key' => env('MNOTIFY_KEY'),

];
