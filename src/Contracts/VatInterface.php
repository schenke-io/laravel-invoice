<?php

namespace SchenkeIo\Invoice\Contracts;

use SchenkeIo\Invoice\Enum\VatRate;
use SchenkeIo\Invoice\Money\Vat;

/**
 * Interface for country-specific VAT configurations.
 *
 * Implementations of this interface provide the standard, reduced, and
 * zero VAT rates for a specific country, along with metadata like
 * status and reference URLs for tax laws. This contract ensures that
 * the invoicing system can correctly apply and display tax information
 * according to the regulations of different jurisdictions.
 */
interface VatInterface
{
    public function getVat(VatRate $vatRate): Vat;

    public function standard(): Vat;

    public function reduced(): Vat;

    public function none(): Vat;

    public function referenceUrl(): string;

    public function getStatus(): string;
}
