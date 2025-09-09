<?php

namespace SchenkeIo\Invoice\Exceptions;

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
}
