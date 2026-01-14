<?php

namespace SchenkeIo\Invoice\Invoicing;

use SchenkeIo\Invoice\Enum\InvoiceLineType;
use SchenkeIo\Invoice\Money\Currency;
use SchenkeIo\Invoice\Money\Vat;

/**
 * Representation of a single line item on an invoice.
 *
 * This class stores the details of an individual invoice line, including
 * its name, price (gross/net), and type. It automatically calculates
 * the corresponding tax amounts based on the provided prices and type.
 */
readonly class LineData
{
    public Currency $lineTotalGrossPrice;

    public Currency $lineTotalNetPrice;

    public Currency $lineVatAmount;

    private function __construct(
        public string $name,
        float $totalGrossPrice,
        public InvoiceLineType $invoiceLineType)
    {
        $vat = Vat::de()->getVat($this->invoiceLineType->vatRate());
        $this->lineTotalGrossPrice = Currency::fromFloat($totalGrossPrice);
        $this->lineTotalNetPrice = $this->lineTotalGrossPrice->fromGrossToNet($vat);

        $this->lineVatAmount = self::calculateVatAmount($this->lineTotalGrossPrice, $this->lineTotalNetPrice);
    }

    protected static function calculateVatAmount(Currency $gross, Currency $net): Currency
    {
        return Currency::fromCents($gross->centValue - $net->centValue);
    }

    public static function fromTotalGrossPrice(string $name, float $totalGrossPrice, InvoiceLineType $invoiceLineType): self
    {
        return new self($name, $totalGrossPrice, $invoiceLineType);
    }

    public static function fromTotalNetPrice(string $name, float $totalNetPrice, InvoiceLineType $invoiceLineType): self
    {
        $vat = Vat::de()->getVat($invoiceLineType->vatRate());

        return new self($name,
            Currency::fromFloat($totalNetPrice)->fromNetToGross($vat)->toFloat(),
            $invoiceLineType
        );
    }
}
