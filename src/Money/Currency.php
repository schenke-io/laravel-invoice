<?php

namespace SchenkeIo\Invoice\Money;

use Livewire\Wireable;

/**
 * Value object representing a monetary currency.
 *
 * This class handles all currency-related calculations using an internal
 * integer representation (cents) to avoid floating-point errors. It
 * provides methods for arithmetic operations and VAT-based conversions.
 */
final readonly class Currency implements Wireable
{
    public int $centValue;

    public function __construct(int $cents)
    {
        $this->centValue = $cents;
    }

    /**
     * static constructor from any value
     */
    public static function fromAny(mixed $value): Currency
    {
        if ($value instanceof self) {
            return $value;
        }

        // Normalize nulls and pure numerics early
        if ($value === null) {
            return new self(0);
        }

        if (is_int($value)) {
            return new self($value * 100);
        }

        if (is_float($value)) {
            return self::fromFloat($value);
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

        return self::fromFloat((float) $clean);
    }

    /**
     * static constructor from a float value
     */
    public static function fromFloat(?float $value): self
    {
        return new self((int) round(($value ?? 0.0) * 100));
    }

    /**
     * static constructor from cents
     */
    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    /**
     * VAT amount from the gross price, given a VAT rate.
     */
    public function vatFromGross(Vat $vat): self
    {
        // The formula is: VAT = Gross * (Rate / (1 + Rate))
        // To maximize precision, we use centValue and multiply by Rate, then divide by (1 + Rate)
        return new self((int) round($this->centValue * $vat->rate / (1 + $vat->rate)));
    }

    /**
     * Calculate the VAT amount from the net price, given a VAT rate.
     */
    public function vatFromNet(Vat $vat): self
    {
        // The formula is: VAT = Net * Rate
        return $this->times($vat->rate);
    }

    /**
     * convert a gross value to a net value using VAT
     */
    public function fromGrossToNet(Vat $vat): self
    {
        // The formula is: Net = Gross / (1 + Rate)
        return new self((int) round($this->centValue / (1 + $vat->rate)));
    }

    /**
     * Convert a net value to a gross value using VAT
     */
    public function fromNetToGross(Vat $vat): self
    {
        // The formula is: Gross = Net * (1 + Rate)
        return $this->times(1 + $vat->rate);
    }

    /**
     * exports to float
     */
    public function toFloat(): float
    {
        return round(0.01 * $this->centValue, 2);
    }

    /**
     * exports to formatted currency string
     */
    public function str(): string
    {
        return $this->__toString().' €';
    }

    /**
     * formats the value in local way
     */
    public function __toString(): string
    {
        return number_format($this->toFloat(), 2, ',', '');
    }

    /**
     * adds two objects
     */
    public function plus(Currency $add): self
    {
        return self::fromCents($this->centValue + $add->centValue);
    }

    /**
     * subtracts two objects
     */
    public function minus(Currency $sub): self
    {
        return self::fromCents($this->centValue - $sub->centValue);
    }

    /**
     * multiplies the object by a factor
     */
    public function times(float $factor): self
    {
        return self::fromCents((int) round($this->centValue * $factor));
    }

    /**
     * exports to Livewire format (numeric scalar)
     */
    public function toLivewire(): float
    {
        return $this->toFloat();
    }

    /**
     * static constructor from Livewire format (numeric scalar)
     *
     * @param  float|int|string|null  $value
     */
    public static function fromLivewire($value): self
    {
        return self::fromAny($value);
    }

    /**
     * Check if the object is empty (zero)
     */
    public function isEmpty(): bool
    {
        return $this->centValue === 0;
    }
}
