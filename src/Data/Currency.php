<?php

namespace SchenkeIo\Invoice\Data;

use Livewire\Wireable;

final readonly class Currency implements Wireable
{
    public int $centValue;

    public function __construct(float $value)
    {
        // Convert the float value to cents by multiplying by 100 and rounding to the nearest integer.
        // This robustly handles floating-point precision issues.
        $this->centValue = (int) round((float) $value * 100);
    }

    /**
     * Calculate the VAT amount from the gross price, given a VAT rate.
     */
    public function vatFromGross(Vat $vat): self
    {
        // The formula is: VAT = Gross * (Rate / (1 + Rate))
        $factor = $vat->rate / (1 + $vat->rate);

        return $this->times($factor);
    }

    /**
     * Calculate the VAT amount from the net price, given a VAT rate.
     */
    public function vatFromNet(Vat $vat): self
    {
        // The formula is: VAT = Net * Rate
        return $this->times($vat->rate);
    }

    public function fromGrossToNet(Vat $vat): self
    {
        // The formula is: Net = Gross / (1 + Rate)
        $factor = 1 / (1 + $vat->rate);

        return $this->times($factor);
    }

    public function fromNetToGross(Vat $vat): self
    {
        // The formula is: Gross = Net * (1 + Rate)
        $factor = 1 + $vat->rate;

        return $this->times($factor);
    }

    public static function fromAny(mixed $value): Currency
    {
        // Normalize nulls and pure numerics early
        if ($value === null) {
            return new self(0);
        }

        if (is_int($value) || is_float($value)) {
            return new self((float) $value);
        }

        // Cast everything else to string and strip non-digit/decimal/separator chars
        $string = (string) $value;
        $clean = preg_replace('/[^0-9.,-]/', '', $string) ?? '';
        $clean = trim($clean);

        if ($clean === '') {
            return new self(0);
        }

        if (preg_match('/^.*?,*\d+\.\d+$/', $clean)) {
            // US format (comma as thousands separator, dot as decimal)
            $clean = str_replace(',', '', $clean);
        } elseif (preg_match('/^.*?\.*\d+,\d+$/', $clean)) {
            // EU format (dot as thousands separator, comma as decimal)
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        }

        return new self((float) $clean);
    }

    public static function fromFloat(?float $value): self
    {
        return new self($value ?? 0);
    }

    public static function fromCents(int $cents): self
    {
        return new self(0.01 * $cents);
    }

    public function toFloat(): float
    {
        return round(0.01 * $this->centValue, 2);
    }

    public function str(): string
    {
        return $this->__toString().' €';
    }

    public function __toString(): string
    {
        return number_format($this->toFloat(), 2, ',', '');
    }

    public function plus(Currency $add): self
    {
        return new self($this->toFloat() + $add->toFloat());
    }

    public function minus(Currency $sub): self
    {
        return new self($this->toFloat() - $sub->toFloat());
    }

    public function times(float $factor): self
    {
        return new self($this->toFloat() * $factor);
    }

    /**
     * @return array<string,int>
     */
    public function toLivewire(): array
    {
        return ['centValue' => $this->centValue];
    }

    /**
     * @param  array<string,int>  $value
     */
    public static function fromLivewire($value): self
    {
        return new self($value['centValue'] / 100);
    }
}
