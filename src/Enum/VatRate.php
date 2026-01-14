<?php

namespace SchenkeIo\Invoice\Enum;

/**
 * Enum representing the different levels of VAT rates.
 *
 * This enum classifies VAT rates into Standard, Reduced, or None, which
 * are then used to look up the actual percentage for a specific country.
 */
enum VatRate: int
{
    case Standard = 2;
    case Reduced = 1;
    case None = 0;
}
