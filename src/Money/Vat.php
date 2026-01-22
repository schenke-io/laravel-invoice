<?php

namespace SchenkeIo\Invoice\Money;

use SchenkeIo\Invoice\Contracts\VatInterface;
use SchenkeIo\Invoice\Exceptions\VatException;

/**
 * Represents a Value Added Tax (VAT) rate and provides factory methods for countries.
 *
 * This class handles VAT rate calculations and identification, allowing for
 * the creation of VAT instances based on specific rates or country-specific
 * defaults.
 *
 * @method static VatInterface at()
 * @method static VatInterface be()
 * @method static VatInterface bg()
 * @method static VatInterface cy()
 * @method static VatInterface cz()
 * @method static VatInterface de()
 * @method static VatInterface dk()
 * @method static VatInterface ee()
 * @method static VatInterface el()
 * @method static VatInterface es()
 * @method static VatInterface fi()
 * @method static VatInterface fr()
 * @method static VatInterface hr()
 * @method static VatInterface hu()
 * @method static VatInterface ie()
 * @method static VatInterface it()
 * @method static VatInterface lt()
 * @method static VatInterface lu()
 * @method static VatInterface lv()
 * @method static VatInterface mt()
 * @method static VatInterface nl()
 * @method static VatInterface pl()
 * @method static VatInterface pt()
 * @method static VatInterface ro()
 * @method static VatInterface se()
 * @method static VatInterface si()
 * @method static VatInterface sk()
 * @method static VatInterface ch()
 */
readonly class Vat
{
    public string $id;

    public string $name;

    public float $rate;

    public function __construct(float|string $rate)
    {
        if (is_string($rate)) {
            $this->rate = 0.001 * (int) ltrim($rate, '0');
        } else {
            $this->rate = $rate;
        }
        $this->id = sprintf('%03d', (int) round($this->rate * 1000));
        $decimals = str_ends_with($this->id, '0') ? 0 : 1;
        $this->name = number_format($this->rate * 100, $decimals, ',').'%';
    }

    /**
     * Get a country instance by its ISO code.
     *
     * @throws VatException
     */
    public static function country(string $isoCode): VatInterface
    {
        $isoCode = strtoupper($isoCode);
        if (isset(Country::DATA[$isoCode])) {
            return new Country($isoCode);
        }

        throw VatException::countryNotFound($isoCode);
    }

    /**
     * @param  array<int, mixed>  $arguments
     *
     * @throws VatException
     */
    public static function __callStatic(string $name, array $arguments): VatInterface
    {
        return self::country($name);
    }

    /**
     * Create a VAT instance from a numeric ID (e.g. '190' for 19.0%).
     */
    public static function fromId(string $id): self
    {
        return new self($id);
    }

    /**
     * Create a VAT instance from a float rate (e.g. 0.19 for 19%).
     *
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
     * Returns the name of the VAT rate (e.g. "19%").
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
