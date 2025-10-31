<?php

declare(strict_types=1);

namespace Cactus\Notifications\Clients;

use Illuminate\Http\Client\Factory as Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Cactus\Notifications\Exceptions;
use Cactus\Notifications\Exceptions\{
    MNotifyException,
    InsufficientBalanceException,
    InvalidApiKeyException,
    InvalidPhoneNumberException,
    InvalidSenderIdException,
    EmptyMessageException,
    DateRangeException,
    UnregisteredSenderIdException
};
use Cactus\Notifications\Validators\{
    MessageValidator,
    PhoneNumberValidator
};

class MNotifySmsClient
{
    private const BASE_URL = 'https://api.mnotify.com/api';
    
    private const ERROR_CODES = [
        1000 => 'Message submitted successfully',
        1002 => 'SMS sending failed',
        1003 => 'Insufficient balance',
        1004 => 'Invalid API key',
        1005 => 'Invalid phone number',
        1006 => 'Invalid Sender ID. Sender ID must not be more than 11 Characters',
        1007 => 'Message scheduled for later delivery',
        1008 => 'Empty message',
        1009 => 'Empty from date and to date',
        1010 => 'No messages has been sent on the specified dates',
        1011 => 'Numeric Sender IDs are not allowed',
        1012 => 'Sender ID is not registered'
    ];

    public function __construct(
        private readonly string $apiKey,
        private readonly ?Http $http = new Http,
    ) {}

    public function sendSms(
        string $to,
        string $message,
        string $senderId,
    ): array {
        // Validate phone number
        $validatedPhone = PhoneNumberValidator::validate($to);
        
        // Validate and escape message
        $validatedMessage = MessageValidator::validate($message);
        $escapedMessage = MessageValidator::escapeGsm7($validatedMessage);
        
        // Calculate message parts for pricing/validation
        $messageLength = strlen($validatedMessage);
        $messageParts = MessageValidator::calculateMessageParts($messageLength);

        return $this->post('sms/quick', [
            'recipient' => [$validatedPhone],
            'message' => $escapedMessage,
            'sender' => $this->validateSenderId($senderId),
        ]);
    }

    public function getBalance(): array
    {
        return $this->get('balance', [
            'key' => $this->apiKey,
        ]);
    }

    public function getApiUsage(Carbon $from, Carbon $to): array
    {
        if ($from->isAfter($to)) {
            throw new DateRangeException('From date must be before to date', 1009);
        }

        return $this->get('api_usage', [
            'key' => $this->apiKey,
            'from' => $from->format('Y-m-d H:i:s'),
            'to' => $to->format('Y-m-d H:i:s'),
        ]);
    }

    private function validatePhoneNumber(string $phone): string
    {
        // Basic validation for Ghana phone numbers
        // You might want to enhance this based on your needs
        if (!preg_match('/^(?:\+233|0)[2-9][0-9]{8}$/', $phone)) {
            throw new InvalidPhoneNumberException(
                'Invalid phone number format', 
                1005
            );
        }

        return $phone;
    }

    private function validateSenderId(string $senderId): string
    {
        if (strlen($senderId) > 11) {
            throw new InvalidSenderIdException(
                'Sender ID must not be more than 11 characters',
                1006
            );
        }

        if (is_numeric($senderId)) {
            throw new InvalidSenderIdException(
                'Numeric Sender IDs are not allowed',
                1011
            );
        }

        return $senderId;
    }

    private function post(string $endpoint, array $data): array
    {
        $response = $this->http->withQueryParameters([
            'key' => $this->apiKey
        ])->post($this->url($endpoint), $data);
        
        return $this->handleResponse($response);
    }

    private function get(string $endpoint, array $query): array
    {
        $response = $this->http->get($this->url($endpoint), $query);
        
        return $this->handleResponse($response);
    }

    private function url(string $endpoint): string
    {
        return self::BASE_URL . ($endpoint ? "/{$endpoint}" : '');
    }

    private function handleResponse(Response $response): array
    {
        $data = $response->json();

        if (!isset($data['code'])) {
            throw new MNotifyException(
                'Unexpected API response format',
                500
            );
        }

        $code = (int) $data['code'];

        if ($code !== 2000) {
            $message = self::ERROR_CODES[$code] ?? 'Unknown error';
            
            throw match($code) {
                1003 => new InsufficientBalanceException($message, $code),
                1004 => new InvalidApiKeyException($message, $code),
                1005 => new InvalidPhoneNumberException($message, $code),
                1006, 1011 => new InvalidSenderIdException($message, $code),
                1008 => new EmptyMessageException($message, $code),
                1009, 1010 => new DateRangeException($message, $code),
                1012 => new UnregisteredSenderIdException($message, $code),
                default => new MNotifyException($message, $code),
            };
        }

        return $data;
    }
}