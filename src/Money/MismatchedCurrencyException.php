<?php

namespace SchenkeIo\Invoice\Money;

/**
 * Exception thrown when arithmetic operations are attempted between currencies with different ISO 4217 codes.
 *
 * This exception ensures that mismatched currency codes are caught explicitly,
 * preventing silent mixing of different currencies in calculations.
 */
class MismatchedCurrencyException extends \Exception
{
    public static function create(string $codeA, string $codeB): self
    {
        return new self("Cannot perform arithmetic between different currencies: '{$codeA}' and '{$codeB}'.");
    }
}
