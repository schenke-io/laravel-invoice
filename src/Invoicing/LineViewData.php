<?php

namespace SchenkeIo\Invoice\Invoicing;

use SchenkeIo\Invoice\Contracts\InvoiceLineView;
use SchenkeIo\Invoice\Money\Currency;

final readonly class LineViewData extends LineViewBase implements InvoiceLineView
{
    private function __construct(
        // financial elements
        public null|int|string $quantity,
        public string $name,
        public ?string $singlePrice,
        public string $totalPrice,
        bool $isBold,
        public bool $isEmpty
    ) {
        parent::__construct($isBold);
    }

    public static function lineItem(LineData $lineItem, bool $isGross = true): self
    {
        if ($isGross) {
            $singlePrice = $lineItem->itemGrossPrice->str();
            $totalPrice = $lineItem->lineTotalGrossPrice->str();
        } else {
            $singlePrice = $lineItem->itemNetPrice->str();
            $totalPrice = $lineItem->lineTotalNetPrice->str();
        }

        return new self(
            $lineItem->quantity,
            $lineItem->name,
            $singlePrice,
            $totalPrice,
            false,
            $lineItem->lineTotalGrossPrice->isEmpty()
        );
    }

    public static function footerTotal(Currency $amount, string $text, bool $isBold): self
    {
        return new self(null, $text, null, $amount->str(), $isBold, false);
    }

    public static function header(string $pricePrefix): self
    {
        return new self(
            'Menge',
            'Position',
            $pricePrefix.' pro Stk.',
            $pricePrefix.' Gesamt',
            true,
            false
        );
    }

    /**
     * key and custom definitions per column
     *
     * @return array<string,mixed>
     */
    public function columns(): array
    {
        return [
            // key => align right
            'quantity' => false,
            'name' => false,
            'singlePrice' => true,
            'totalPrice' => true,
        ];
    }
}
