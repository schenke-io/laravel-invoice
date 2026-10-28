# VAT Management

Country-specific VAT rates and the category/rate enums that drive invoice tax logic.

## When to use

- Looking up the standard, reduced or zero VAT rate of a country.
- Creating a `Vat` from a rate or ID for `Currency` conversions.
- Deciding the tax treatment of a line item (`VatCategory`, `VatRate`).
- Handling reverse charge or non-taxable items.

## Key classes and enums

- `SchenkeIo\Invoice\Money\Vat` — readonly rate object: `float $rate`, `string $id`, `string $name`.
- `SchenkeIo\Invoice\Money\Country` — implements `VatInterface`; holds the rate table `Country::DATA`.
- `SchenkeIo\Invoice\Contracts\VatInterface` — `getVat()`, `standard()`, `reduced()`, `none()`, `referenceUrl()`, `getStatus()`.
- `SchenkeIo\Invoice\Enum\VatRate` — `Standard` (2), `Reduced` (1), `None` (0).
- `SchenkeIo\Invoice\Enum\VatCategory` — semantic tax category of a line item.
- `SchenkeIo\Invoice\Exceptions\VatException` — invalid rate or unknown country.

## Creating a Vat

```php
use SchenkeIo\Invoice\Money\Vat;

$vat = Vat::fromRate(0.19);   // validated: 0.0 ≤ rate ≤ 0.5
$vat = Vat::fromId('190');    // ID = rate × 1000, zero-padded to 3 digits
$vat = new Vat(0.07);         // float or ID string, no validation

$vat->rate;       // 0.19
$vat->id;         // '190'
$vat->name;       // '19%'   ('8,1%' for 0.081, '13,5%' for 0.135)
(string) $vat;    // same as ->name

Vat::fromRate(19.0);   // throws VatException::rateToHigh() — pass 0.19, not 19
Vat::fromRate(-0.1);   // throws VatException::rateToLow()
```

## Looking up country rates

```php
use SchenkeIo\Invoice\Enum\VatRate;
use SchenkeIo\Invoice\Money\Country;
use SchenkeIo\Invoice\Money\Vat;

$de = Vat::country('DE');     // case-insensitive ISO code → Country
$de = Vat::de();              // magic static per ISO code (lowercase)
$de = new Country('DE');      // direct construction

$de->standard();              // Vat 19%
$de->reduced();               // Vat 7%
$de->none();                  // Vat 0%
$de->getVat(VatRate::Reduced); // Vat 7%
$de->isoCode;                 // 'DE'
$de->referenceUrl();          // official rate source (EU, CH or PwC summary)
$de->getStatus();             // 'Standard rate 19%, Reduced rate 7%'

Vat::country('XX');           // throws VatException::countryNotFound('XX')
```

`Country::DATA` holds 50 countries: all 27 EU members plus AE, AU, BH, CA, CH, EG, GB, IL, IR, IQ, JO, JP, KR, KW, LB, OM, PS, QA, SA, SY, TR, US, YE. Entry shape:

```php
'DE' => ['name' => 'germany', 'vat' => ['190', '070', '000'], 'isEu' => true]
//                                       standard reduced none
```

Countries without a reduced rate repeat the standard ID (e.g. `AU` → `['100', '100', '000']`).

## VatRate enum

```php
use SchenkeIo\Invoice\Enum\VatRate;

VatRate::Standard;  // 2
VatRate::Reduced;   // 1
VatRate::None;      // 0
```

## VatCategory enum

```php
use SchenkeIo\Invoice\Enum\VatCategory;

VatCategory::Taxable;                  // 1 — own service, standard rate
VatCategory::NonTaxableDamages;        // 2 — damage compensation, no VAT
VatCategory::NonTaxableTransitory;     // 3 — pass-through costs, no VAT
VatCategory::Deposits;                 // 4 — security deposits, no VAT
VatCategory::ReverseChargingStandard;  // 5 — EU B2B, standard rate, VAT 0
VatCategory::ReverseChargingReduced;   // 6 — EU B2B, reduced rate, VAT 0
VatCategory::TaxableReducedVatRate;    // 9 — books, food, etc.

$category->vatRate();          // VatRate
$category->hasVat();           // true only for Taxable and TaxableReducedVatRate
$category->isReverseCharge();  // true for the two ReverseCharging cases
$category->description();      // __('invoice::invoice.vat_categories.*')
$category->description($translator); // via a TranslationInterface instead of __()
```

Category → rate mapping: `Taxable`, `ReverseChargingStandard` → `Standard`; `TaxableReducedVatRate`, `ReverseChargingReduced` → `Reduced`; all others → `None`.

## Using Vat with Currency

```php
use SchenkeIo\Invoice\Money\Currency;
use SchenkeIo\Invoice\Money\Vat;

$vat = Vat::de()->standard();

Currency::fromFloat(119.00)->fromGrossToNet($vat);  // 100,00 €
Currency::fromFloat(119.00)->vatFromGross($vat);    // 19,00 €
Currency::fromFloat(100.00)->fromNetToGross($vat);  // 119,00 €
Currency::fromFloat(100.00)->vatFromNet($vat);      // 19,00 €
```

## Exceptions

`SchenkeIo\Invoice\Exceptions\VatException` factories:

- `rateToLow()` — `fromRate()` with a rate below 0.
- `rateToHigh()` — `fromRate()` with a rate above 0.5 (message reminds to pass `0.19`, not `19`).
- `countryNotFound(string $code)` — ISO code missing from `Country::DATA`.
