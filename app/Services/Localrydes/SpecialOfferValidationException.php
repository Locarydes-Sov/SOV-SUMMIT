<?php

namespace App\Services\Localrydes;

/**
 * Thrown when the LocalRydes API rejects booking data (HTTP 422).
 */
class SpecialOfferValidationException extends SpecialOfferApiException
{
    /**
     * @param  array<string, array<int, string>>  $errors  Field name => messages, as returned by the API.
     */
    public function __construct(string $message, public readonly array $errors = [])
    {
        parent::__construct($message);
    }
}
