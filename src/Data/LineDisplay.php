<?php

namespace SchenkeIo\Invoice\Data;

class LineDisplay
{
    private function __construct(
        public readonly ?int $quantity,
        public readonly string $name,
        public readonly ?string $singlePrice,
        public readonly string $totalPrice,
        public readonly bool $isBold
    ) {}

    public static function lineItem(LineItem $lineItem, bool $isGross = true): self
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
            $singlePrice, $totalPrice,
            false
        );
    }

    public static function footerTotal(Currency $amount, string $text, bool $isBold): self
    {
        return new self(null, $text, null, $amount->str(), $isBold);
    }
}
