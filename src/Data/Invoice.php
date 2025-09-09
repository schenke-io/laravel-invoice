<?php

namespace SchenkeIo\Invoice\Data;

use Carbon\Carbon;

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

    /**
     * cent based calculation to avoid numeric glitches in calculations
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
     * @return array<string,Currency>
     */
    public function vats(): array
    {
        $return = [];
        foreach ($this->vatCents as $id => $cents) {
            $return[$id] = Currency::fromCents($cents);
        }

        return $return;
    }
}
