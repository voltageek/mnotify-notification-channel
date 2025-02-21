<?php

declare(strict_types=1);

namespace Cactus\Notifications\Validators;

use Cactus\Notifications\Exceptions\InvalidPhoneNumberException;

class PhoneNumberValidator
{
    /**
     * Valid Ghana phone number prefixes
     */
    private const GHANA_PREFIXES = [
        '020', '023', '024', '025', '026', '027', '028', '029', // MTN
        '050', '053', '054', '055', '056', '057', '058', '059', // Vodafone
        '026', '027', '056', '057', // AirtelTigo
        '233' // International format
    ];

    public static function validate(string $phone): string
    {
        // Remove any spaces or special characters
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);

        // Convert international format to local if needed
        if (str_starts_with($cleaned, '+233')) {
            $cleaned = '0' . substr($cleaned, 4);
        } else if (str_starts_with($cleaned, '233')) {
            $cleaned = '0' . substr($cleaned, 3);
        }

        // Check if the number matches Ghana format
        if (!preg_match('/^0[2-5][0-9]{8}$/', $cleaned)) {
            throw new InvalidPhoneNumberException(
                'Invalid phone number format. Must be a valid Ghana phone number.',
                1005
            );
        }

        // Validate prefix
        $prefix = substr($cleaned, 0, 3);
        if (!in_array($prefix, self::GHANA_PREFIXES)) {
            throw new InvalidPhoneNumberException(
                "Invalid phone number prefix: {$prefix}",
                1005
            );
        }

        return $cleaned;
    }
}
