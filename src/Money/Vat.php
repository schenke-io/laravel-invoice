<?php

namespace SchenkeIo\Invoice\Money;

use SchenkeIo\Invoice\Exceptions\VatException;

readonly class Vat
{
    public string $id;

    public string $name;

    private function __construct(public float $rate)
    {
        $this->id = sprintf('%03d', (int) round($rate * 1000));
        $decimals = str_ends_with($this->id, '0') ? 0 : 1;
        $this->name = number_format($rate * 100, $decimals, ',').'%';
    }

    public static function fromId(string $id): self
    {
        $rate = 0.001 * (int) ltrim($id, '0');

        return new self($rate);
    }

    /**
     * @throws VatException
     */
    public static function fromRate(float $rate): self
    {
        if ($rate < 0.0) {
            throw VatException::rateToLow();
        } elseif ($rate > 0.5) {
            throw VatException::rateToHigh();
        }

        return new self($rate);
    }

    /**
     * Germany Standard
     */
    public static function deStandard(): self
    {
        return new self(0.19);
    }
}
