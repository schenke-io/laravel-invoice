<?php

namespace SchenkeIo\Invoice\Data;

use SchenkeIo\Invoice\Exceptions\VatException;

readonly class Vat
{
    public string $id;

    public string $name;

    /**
     * @throws VatException
     */
    public function __construct(public float $rate)
    {
        if ($rate <= 0.0) {
            throw VatException::rateToLow();
        } elseif ($rate >= 0.5) {
            throw VatException::rateToHigh();
        }
        $this->id = sprintf('%05d', (int) round($rate * 1000));
        $decimals = str_ends_with($this->id, '0') ? 0 : 1;
        $this->name = number_format($rate * 100, $decimals, ',').'%';
    }

    /**
     * @throws VatException
     */
    public static function fromId(string $id, string $localName = 'MwSt.'): self
    {
        $rate = 0.001 * (int) ltrim($id, '0');

        return new self($rate);
    }
}
