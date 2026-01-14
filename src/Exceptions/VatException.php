<?php

namespace SchenkeIo\Invoice\Exceptions;

/**
 * Exception class for VAT-related errors.
 *
 * This exception is thrown when invalid VAT rates are provided or when
 * a requested country VAT configuration cannot be found, ensuring
 * that tax-related errors are handled explicitly.
 */
class VatException extends \Exception
{
    public static function rateToLow(): self
    {
        return new self('VAT rate is too low');
    }

    public static function rateToHigh(): self
    {
        return new self('VAT rate is too high. 19% should be given as 0.19');
    }

    public static function countryNotFound(string $country): self
    {
        return new self("Country VAT class for '$country' not found.");
    }
}
