<?php

namespace SchenkeIo\Invoice\Data;

readonly class LineItem
{
    public Currency $itemGrossPrice;

    public Currency $itemNetPrice;

    public Currency $lineTotalGrossPrice;

    public Currency $lineTotalNetPrice;

    public Currency $lineVatAmount;

    public function __construct(public int $quantity, public string $name, float $totalGrossPrice, public Vat $vat)
    {
        $this->lineTotalGrossPrice = Currency::fromFloat($totalGrossPrice);
        $this->lineTotalNetPrice = $this->lineTotalGrossPrice->fromGrossToNet($vat);
        $this->itemGrossPrice = Currency::fromFloat($totalGrossPrice / $quantity);
        $this->itemNetPrice = $this->itemGrossPrice->fromGrossToNet($vat);
        $this->lineVatAmount = Currency::fromCents($this->lineTotalGrossPrice->centValue - $this->itemNetPrice->centValue);
    }

    public static function fromTotalGrossPrice(int $quantity, string $name, float $totalGrossPrice, Vat $vat): self
    {
        return new self($quantity, $name, $totalGrossPrice, $vat);
    }

    public static function fromTotalNetPrice(int $quantity, string $name, float $totalNetPrice, Vat $vat): self
    {
        return new self($quantity, $name,
            Currency::fromFloat($totalNetPrice)->fromNetToGross($vat)->toFloat(),
            $vat);
    }

    public static function fromItemGrossPrice(int $quantity, string $name, float $grossItemPrice, Vat $vat): self
    {
        return self::fromTotalGrossPrice($quantity, $name, $grossItemPrice * $quantity, $vat);
    }

    public static function fromItemNetPrice(int $quantity, string $name, float $netItemPrice, Vat $vat): self
    {
        return self::fromTotalNetPrice($quantity, $name, $netItemPrice * $quantity, $vat);
    }
}
