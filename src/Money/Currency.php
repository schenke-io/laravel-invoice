<?php

namespace SchenkeIo\Invoice\Money;

use Livewire\Wireable;
use Stringable;

/**
 * Value object representing a monetary currency.
 *
 * This class handles all currency-related calculations using an internal
 * integer representation (cents) to avoid floating-point errors. It
 * provides methods for arithmetic operations and VAT-based conversions.
 */
final readonly class Currency implements Stringable, Wireable
{
    /**
     * Symbol map for common ISO 4217 currency codes.
     */
    private const array SYMBOLS = [
        'EUR' => '€', 'USD' => '$', 'GBP' => '£', 'CHF' => 'CHF',
        'JPY' => '¥', 'CNY' => '¥', 'SEK' => 'kr', 'NOK' => 'kr',
        'DKK' => 'kr', 'PLN' => 'zł', 'CZK' => 'Kč', 'HUF' => 'Ft',
        'RON' => 'lei', 'BGN' => 'лв', 'HRK' => 'kn', 'RUB' => '₽',
        'TRY' => '₺', 'INR' => '₹', 'BRL' => 'R$', 'AUD' => '$',
    ];

    public int $centValue;

    private string $code;

    public function __construct(int $cents, string $code = 'EUR')
    {
        $this->centValue = $cents;
        $this->code = $code;
    }

    /**
     * static constructor from any value
     */
    public static function fromAny(mixed $value, string $code = 'EUR'): Currency
    {
        if ($value instanceof self) {
            return $value;
        }

        if (is_array($value)) {
            return new self((int) ($value['cents'] ?? $value['centValue'] ?? 0), $code);
        }

        if ($value === null || $value === '') {
            return new self(0, $code);
        }

        if (is_int($value)) {
            return new self($value * 100, $code);
        }

        if (is_float($value)) {
            return self::fromFloat($value, $code);
        }

        // Cast everything else to string and strip non-digit/decimal/separator chars
        $clean = preg_replace('/[^0-9.,-]/', '', (string) $value) ?? '';
        $clean = trim($clean);

        if ($clean === '') {
            return new self(0, $code);
        }

        if (preg_match('/^.*?,*\d+\.\d+$/', $clean)) {
            // US format (comma as thousand separator, dot as decimal)
            $clean = str_replace(',', '', $clean);
        } elseif (preg_match('/^.*?\.*\d+,\d+$/', $clean)) {
            // EU format (dot as thousand separator, comma as decimal)
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        }

        return self::fromFloat((float) $clean, $code);
    }

    /**
     * static constructor from a float value
     */
    public static function fromFloat(?float $value, string $code = 'EUR'): self
    {
        return new self((int) round(($value ?? 0.0) * 100), $code);
    }

    /**
     * static constructor from cents
     */
    public static function fromCents(int $cents, string $code = 'EUR'): self
    {
        return new self($cents, $code);
    }

    /**
     * Returns the ISO 4217 currency code.
     */
    public function code(): string
    {
        return $this->code;
    }

    /**
     * Returns a new Currency instance with the specified currency code.
     */
    public function withCode(string $code): self
    {
        return new self($this->centValue, $code);
    }

    /**
     * VAT amount from the gross price, given a VAT rate.
     */
    public function vatFromGross(Vat $vat): self
    {
        // The formula is: VAT = Gross * (Rate / (1 + Rate))
        // To maximize precision, we use centValue and multiply by Rate, then divide by (1 + Rate)
        return new self((int) round($this->centValue * $vat->rate / (1 + $vat->rate)), $this->code);
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
        return new self((int) round($this->centValue / (1 + $vat->rate)), $this->code);
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
     * Returns the formatted value with thousands separator (German format).
     */
    public function formatted(): string
    {
        return number_format($this->toFloat(), 2, ',', '.');
    }

    /**
     * Returns the formatted value with thousands separator and currency symbol.
     */
    public function formattedStr(): string
    {
        $symbol = self::SYMBOLS[$this->code] ?? $this->code;

        return $this->formatted().' '.$symbol;
    }

    /**
     * Divides the currency value by the given divisor with half-up rounding.
     */
    public function dividedBy(int $divisor): self
    {
        if ($divisor === 0) {
            throw new \DivisionByZeroError('Cannot divide by zero');
        }

        return self::fromCents((int) round($this->centValue / $divisor), $this->code);
    }

    /**
     * exports to formatted currency string
     */
    public function str(): string
    {
        $symbol = self::SYMBOLS[$this->code] ?? $this->code;

        return $this->__toString().' '.$symbol;
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
        if ($this->code !== $add->code) {
            throw MismatchedCurrencyException::create($this->code, $add->code);
        }

        return self::fromCents($this->centValue + $add->centValue, $this->code);
    }

    /**
     * subtracts two objects
     */
    public function minus(Currency $sub): self
    {
        if ($this->code !== $sub->code) {
            throw MismatchedCurrencyException::create($this->code, $sub->code);
        }

        return self::fromCents($this->centValue - $sub->centValue, $this->code);
    }

    /**
     * multiplies the object by a factor
     */
    public function times(float $factor): self
    {
        return self::fromCents((int) round($this->centValue * $factor), $this->code);
    }

    /**
     * exports to Livewire format (numeric scalar)
     * must be an array even when not explicitly documented
     *
     * @return array<string, mixed>
     */
    public function toLivewire(): array
    {
        return [
            'cents' => $this->centValue,
            'code' => $this->code,
        ];
    }

    public static function fromLivewire(mixed $value): self
    {
        if (is_array($value)) {
            $code = $value['code'] ?? 'EUR';

            return self::fromAny($value, $code);
        }

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
