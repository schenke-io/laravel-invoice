<?php

namespace SchenkeIo\Invoice\Money;

use SchenkeIo\Invoice\Contracts\VatInterface;
use SchenkeIo\Invoice\Enum\VatRate;
use SchenkeIo\Invoice\Exceptions\VatException;

/**
 * Data provider for country-specific VAT information.
 *
 * This class stores and provides VAT rates for various countries, mainly
 * within the EU and Switzerland. It implements VatInterface to offer a
 * consistent way to access standard, reduced, and zero VAT rates.
 */
class Country implements VatInterface
{
    public const array DATA = [
        'AT' => ['name' => 'austria', 'vat' => ['200', '100', '000'], 'isEu' => true],
        'BE' => ['name' => 'belgium', 'vat' => ['210', '120', '000'], 'isEu' => true],
        'BG' => ['name' => 'bulgaria', 'vat' => ['200', '090', '000'], 'isEu' => true],
        'CY' => ['name' => 'cyprus', 'vat' => ['190', '090', '000'], 'isEu' => true],
        'CZ' => ['name' => 'czechia', 'vat' => ['210', '120', '000'], 'isEu' => true],
        'DE' => ['name' => 'germany', 'vat' => ['190', '070', '000'], 'isEu' => true],
        'DK' => ['name' => 'denmark', 'vat' => ['250', '000', '000'], 'isEu' => true],
        'EE' => ['name' => 'estonia', 'vat' => ['220', '130', '000'], 'isEu' => true],
        'EL' => ['name' => 'greece', 'vat' => ['240', '130', '000'], 'isEu' => true],
        'ES' => ['name' => 'spain', 'vat' => ['210', '100', '000'], 'isEu' => true],
        'FI' => ['name' => 'finland', 'vat' => ['240', '140', '000'], 'isEu' => true],
        'FR' => ['name' => 'france', 'vat' => ['200', '100', '000'], 'isEu' => true],
        'HR' => ['name' => 'croatia', 'vat' => ['250', '130', '000'], 'isEu' => true],
        'HU' => ['name' => 'hungary', 'vat' => ['270', '180', '000'], 'isEu' => true],
        'IE' => ['name' => 'ireland', 'vat' => ['230', '135', '000'], 'isEu' => true],
        'IT' => ['name' => 'italy', 'vat' => ['220', '100', '000'], 'isEu' => true],
        'LT' => ['name' => 'lithuania', 'vat' => ['210', '090', '000'], 'isEu' => true],
        'LU' => ['name' => 'luxembourg', 'vat' => ['170', '140', '000'], 'isEu' => true],
        'LV' => ['name' => 'latvia', 'vat' => ['210', '120', '000'], 'isEu' => true],
        'MT' => ['name' => 'malta', 'vat' => ['180', '070', '000'], 'isEu' => true],
        'NL' => ['name' => 'netherlands', 'vat' => ['210', '090', '000'], 'isEu' => true],
        'PL' => ['name' => 'poland', 'vat' => ['230', '080', '000'], 'isEu' => true],
        'PT' => ['name' => 'portugal', 'vat' => ['230', '130', '000'], 'isEu' => true],
        'RO' => ['name' => 'romania', 'vat' => ['190', '090', '000'], 'isEu' => true],
        'SE' => ['name' => 'sweden', 'vat' => ['250', '120', '000'], 'isEu' => true],
        'SI' => ['name' => 'slovenia', 'vat' => ['220', '095', '000'], 'isEu' => true],
        'SK' => ['name' => 'slovakia', 'vat' => ['200', '100', '000'], 'isEu' => true],
        'AE' => ['name' => 'united arab emirates', 'vat' => ['050', '050', '000'], 'isEu' => false],
        'AU' => ['name' => 'australia', 'vat' => ['100', '100', '000'], 'isEu' => false],
        'BH' => ['name' => 'bahrain', 'vat' => ['100', '100', '000'], 'isEu' => false],
        'CA' => ['name' => 'canada', 'vat' => ['050', '050', '000'], 'isEu' => false],
        'CH' => ['name' => 'switzerland', 'vat' => ['081', '026', '000'], 'isEu' => false],
        'EG' => ['name' => 'egypt', 'vat' => ['140', '050', '000'], 'isEu' => false],
        'GB' => ['name' => 'united kingdom', 'vat' => ['200', '050', '000'], 'isEu' => false],
        'IL' => ['name' => 'israel', 'vat' => ['180', '180', '000'], 'isEu' => false],
        'IR' => ['name' => 'iran', 'vat' => ['090', '090', '000'], 'isEu' => false],
        'IQ' => ['name' => 'iraq', 'vat' => ['000', '000', '000'], 'isEu' => false],
        'JO' => ['name' => 'jordan', 'vat' => ['160', '080', '000'], 'isEu' => false],
        'JP' => ['name' => 'japan', 'vat' => ['100', '080', '000'], 'isEu' => false],
        'KR' => ['name' => 'south korea', 'vat' => ['100', '100', '000'], 'isEu' => false],
        'KW' => ['name' => 'kuwait', 'vat' => ['000', '000', '000'], 'isEu' => false],
        'LB' => ['name' => 'lebanon', 'vat' => ['110', '110', '000'], 'isEu' => false],
        'OM' => ['name' => 'oman', 'vat' => ['050', '050', '000'], 'isEu' => false],
        'PS' => ['name' => 'palestine', 'vat' => ['160', '160', '000'], 'isEu' => false],
        'QA' => ['name' => 'qatar', 'vat' => ['000', '000', '000'], 'isEu' => false],
        'SA' => ['name' => 'saudi arabia', 'vat' => ['150', '150', '000'], 'isEu' => false],
        'SY' => ['name' => 'syria', 'vat' => ['000', '000', '000'], 'isEu' => false],
        'TR' => ['name' => 'turkey', 'vat' => ['200', '100', '000'], 'isEu' => false],
        'US' => ['name' => 'united states', 'vat' => ['000', '000', '000'], 'isEu' => false],
        'YE' => ['name' => 'yemen', 'vat' => ['050', '050', '000'], 'isEu' => false],
    ];

    /**
     * @var array{vat: array{0: string, 1: string, 2: string}, name: string, isEu: bool}
     */
    protected array $countryData;

    /**
     * @throws VatException
     */
    public function __construct(public readonly string $isoCode)
    {
        if (! isset(self::DATA[strtoupper($isoCode)])) {
            throw VatException::countryNotFound($isoCode);
        }
        $this->countryData = self::DATA[strtoupper($isoCode)];
    }

    public function getVat(VatRate $vatRate): Vat
    {
        return match ($vatRate) {
            VatRate::Standard => $this->standard(),
            VatRate::Reduced => $this->reduced(),
            VatRate::None => $this->none(),
        };
    }

    public function standard(): Vat
    {
        return Vat::fromId($this->countryData['vat'][0]);
    }

    public function reduced(): Vat
    {
        return Vat::fromId($this->countryData['vat'][1]);
    }

    public function none(): Vat
    {
        return Vat::fromId($this->countryData['vat'][2]);
    }

    /**
     * Get the reference URL for VAT rates of the country.
     */
    public function referenceUrl(): string
    {
        $name = $this->countryData['name'];
        if ($name === 'switzerland') {
            return 'https://www.estv.admin.ch/estv/en/home/value-added-tax.html';
        }

        if ($this->countryData['isEu']) {
            return "https://taxation-customs.ec.europa.eu/taxation/vat/vat-rules-rates/{$name}_en";
        }

        return 'https://taxsummaries.pwc.com/quick-charts/value-added-tax-vat-rates';
    }

    /**
     * Get a human-readable status of the VAT rates.
     */
    public function getStatus(): string
    {
        $std = $this->standard()->name;
        $red = $this->reduced()->name;

        /*
         * if both rates are equal, we assume there is no reduced rate
         */
        if ($std === $red) {
            return sprintf('Standard rate %s, No reduced rates', $std);
        }

        return sprintf('Standard rate %s, Reduced rate %s', $std, $red);
    }
}
