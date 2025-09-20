<?php

namespace SchenkeIo\Invoice\Data;

use Carbon\Carbon;
use SchenkeIo\Invoice\Exceptions\VatException;

class Invoice
{
    /**
     * @var LineItem[]
     */
    protected array $lineItems = [];

    /**
     * @var array <string,int>
     */
    protected array $vatCents = [];

    protected int $totalGramm = 0;

    public Currency $totalGrossPrice;

    public Currency $totalNetPrice;

    public function __construct(
        public readonly string $invoiceId,
        public readonly Carbon $invoiceDate,
        public readonly Customer $customer)
    {
        $this->totalGrossPrice = Currency::fromCents(0);
        $this->totalNetPrice = Currency::fromCents(0);
    }

    public function addWeight(int $gramm): void
    {
        $this->totalGramm += $gramm;
    }

    /**
     * cent based calculation to avoid numeric glitches
     */
    public function addLine(LineItem $lineItem): void
    {
        $this->lineItems[] = $lineItem;
        $this->vatCents[$lineItem->vat->id] = $this->vatCents[$lineItem->vat->id] ?? 0;
        $this->vatCents[$lineItem->vat->id] += $lineItem->lineVatAmount->centValue;
        $this->totalNetPrice = $this->totalNetPrice->plus(Currency::fromCents($lineItem->lineTotalNetPrice->centValue));
        $this->totalGrossPrice = $this->totalGrossPrice->plus(Currency::fromCents($lineItem->lineTotalGrossPrice->centValue));
        // sort the vats always
        ksort($this->vatCents);
    }

    /**
     * formats the VAT values in a readable format
     *
     * @return array<string,Currency>
     *
     * @throws VatException
     */
    public function vats(): array
    {
        $return = [];
        foreach ($this->vatCents as $id => $cents) {
            $vat = Vat::fromId($id);
            $return[$vat->name] = Currency::fromCents($cents);
        }

        return $return;
    }

    /**
     * show pay me information
     */
    public function payMe(): bool
    {
        return $this->totalGrossPrice->centValue > 0;
    }

    /**
     * the total is zero
     */
    public function isEmpty(): bool
    {
        return $this->totalGrossPrice->isEmpty();
    }

    /**
     * data for blade templates
     *
     * @throws VatException
     */
    public function display(bool $isGrossInvoice): InvoiceDisplay
    {
        $bruttoLine = LineDisplay::footerTotal(
            $this->totalGrossPrice, 'Gesamtbetrag (Brutto)', true
        );
        $pricePrefix = $isGrossInvoice ? 'Preis' : 'Nettopreis';
        $vatPrefix = $isGrossInvoice ? 'darin enthalten ' : 'zzgl. ';

        $return = new InvoiceDisplay;
        $return->invoiceId = $this->invoiceId;
        $return->invoiceDate = $this->invoiceDate;
        $return->totalGramm = $this->totalGramm;
        $g = $this->totalGramm;
        $return->totalWeightText = $g > 1000 ? number_format($g / 1000, 1).' kg' : $g.' g';
        $return->customer = $this->customer;
        $return->totalGrossPrice = $this->totalGrossPrice;
        $return->header = LineDisplay::header($pricePrefix);
        $return->body = [];
        $return->footer = [];

        if ($isGrossInvoice) {
            // endkunden
            $return->footer[] = $bruttoLine;
        } else {
            // Geschäftskunden
            $return->footer[] = LineDisplay::footerTotal(
                $this->totalNetPrice, 'Summe Netto', true
            );
        }
        $return->header = LineDisplay::header($pricePrefix);

        foreach ($this->lineItems as $lineItem) {
            $return->body[] = LineDisplay::lineItem($lineItem, $isGrossInvoice);
        }

        foreach ($this->vatCents as $vatId => $cents) {
            $vat = Vat::fromId($vatId);
            $return->footer[] = LineDisplay::footerTotal(
                Currency::fromCents($cents),
                $vatPrefix.$vat->name.' MwSt.',
                false
            );
        }
        if (! $isGrossInvoice) {
            $return->footer[] = $bruttoLine;
        }

        return $return;
    }
}
