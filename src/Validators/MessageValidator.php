<?php

declare(strict_types=1);

namespace Cactus\Notifications\Validators;

use Cactus\Notifications\Exceptions\InvalidMessageException;

class MessageValidator
{
    /**
     * GSM 7-bit alphabet
     * @see https://en.wikipedia.org/wiki/GSM_03.38
     */
    private const GSM7_CHARS = '@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞ\x1BÆæßÉ !\"#¤%&\'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà';
    
    /**
     * Extended GSM 7-bit alphabet (each character takes up 2 bytes)
     */
    private const GSM7_EXTENDED = '|^€{}[]~\\';

    /**
     * Characters that need to be escaped in GSM 7-bit
     */
    private const GSM7_ESCAPED = [
        '@' => '\u0000',
        '£' => '\u0001',
        '$' => '\u0002',
        '¥' => '\u0003',
        'è' => '\u0004',
        'é' => '\u0005',
        'ù' => '\u0006',
        'ì' => '\u0007',
        'ò' => '\u0008',
        'Ç' => '\u0009',
        'Ø' => '\u000B',
        'ø' => '\u000C',
        'Å' => '\u000E',
        'å' => '\u000F',
    ];

    public static function validate(string $message): string
    {
        if (empty($message)) {
            throw new InvalidMessageException(
                'Message cannot be empty',
                1008
            );
        }

        $messageLength = 0;
        $invalidChars = [];

        foreach (mb_str_split($message) as $char) {
            if (str_contains(self::GSM7_CHARS, $char)) {
                $messageLength++;
            } else if (str_contains(self::GSM7_EXTENDED, $char)) {
                $messageLength += 2; // Extended characters count as 2
            } else {
                $invalidChars[] = $char;
            }
        }

        if (!empty($invalidChars)) {
            $invalidCharsStr = implode(', ', array_unique($invalidChars));
            throw new InvalidMessageException(
                "Message contains invalid characters for GSM-7 encoding: {$invalidCharsStr}",
                1008
            );
        }

        // Calculate number of SMS parts needed
        $parts = self::calculateMessageParts($messageLength);
        
        return $message;
    }

    public static function calculateMessageParts(int $length): int
    {
        // Single SMS can contain 160 characters
        // Concatenated SMS can contain 153 characters per part
        if ($length <= 160) {
            return 1;
        }

        return (int) ceil($length / 153);
    }

    public static function escapeGsm7(string $message): string
    {
        return strtr($message, self::GSM7_ESCAPED);
    }
}