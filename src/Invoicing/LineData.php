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
        public InvoiceLineType $invoiceLineType,
        public string $countryCode = 'DE')
    {
        $vat = Vat::country($this->countryCode)->getVat($this->invoiceLineType->vatRate());
        $this->lineTotalGrossPrice = Currency::fromFloat($totalGrossPrice);

        if ($this->invoiceLineType->vatCategory()->isReverseCharge()) {
            /*
             * for reverse charge the price is the net price
             * and no VAT is added to the invoice
             */
            $this->lineTotalNetPrice = $this->lineTotalGrossPrice;
            $this->lineVatAmount = Currency::fromCents(0);
        } else {
            $this->lineTotalNetPrice = $this->lineTotalGrossPrice->fromGrossToNet($vat);
            $this->lineVatAmount = self::calculateVatAmount($this->lineTotalGrossPrice, $this->lineTotalNetPrice);
        }
    }

    protected static function calculateVatAmount(Currency $gross, Currency $net): Currency
    {
        return Currency::fromCents($gross->centValue - $net->centValue);
    }

    /**
     * Create a line item from its total gross price.
     */
    public static function fromTotalGrossPrice(string $name, float $totalGrossPrice, InvoiceLineType $invoiceLineType, string $countryCode = 'DE'): self
    {
        return new self($name, $totalGrossPrice, $invoiceLineType, $countryCode);
    }

    /**
     * Create a line item from its total net price.
     */
    public static function fromTotalNetPrice(string $name, float $totalNetPrice, InvoiceLineType $invoiceLineType, string $countryCode = 'DE'): self
    {
        $vat = Vat::country($countryCode)->getVat($invoiceLineType->vatRate());

        if ($invoiceLineType->vatCategory()->isReverseCharge()) {
            $grossFloat = $totalNetPrice;
        } else {
            $grossFloat = Currency::fromFloat($totalNetPrice)->fromNetToGross($vat)->toFloat();
        }

        return new self($name,
            $grossFloat,
            $invoiceLineType,
            $countryCode
        );
    }
}
