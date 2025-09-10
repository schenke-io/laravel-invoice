<?php

namespace SchenkeIo\Invoice\Data;

readonly class LineDisplay
{
    private function __construct(
        public ?int    $quantity,
        public string  $name,
        public ?string $singlePrice,
        public string  $totalPrice,
        public bool    $isBold,
        public bool    $isEmpty
    )
    {
    }

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
            false,
            $lineItem->itemGrossPrice->isEmpty()
        );
    }

    public static function footerTotal(Currency $amount, string $text, bool $isBold): self
    {
        return new self(null, $text, null, $amount->str(), $isBold, $amount->isEmpty());
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'quantity' => $this->quantity,
            'name' => $this->name,
            'price' => $this->singlePrice,
            'total' => $this->totalPrice,
            'isBold' => $this->isBold,
            'isEmpty' => $this->isEmpty,
        ];
    }
}
