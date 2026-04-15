---
name: laravel-invoice-vat-management
description: Handle VAT calculations, rates, and categories across different countries.
---

### When to use
- When checking VAT rates for specific countries.
- When assigning VAT categories to invoice line items.
- When calculating VAT amounts from gross or net prices.
- When handling reverse charge or non-taxable line items.

### Key classes & enums
- `SchenkeIo\LaravelInvoice\Money\Vat` — a single VAT rate (id, name, float rate)
- `SchenkeIo\LaravelInvoice\Money\Country` — country VAT config (implements `VatInterface`)
- `SchenkeIo\LaravelInvoice\Enum\VatRate` — `Standard` | `Reduced` | `None`
- `SchenkeIo\LaravelInvoice\Enum\VatCategory` — semantic tax category for a line item
- `SchenkeIo\LaravelInvoice\Contracts\VatInterface` — contract for country objects

### Creating a Vat rate
```php
use SchenkeIo\LaravelInvoice\Money\Vat;

Vat::fromRate(0.19);    // 19% standard German rate → Vat(id:'190', name:'19,0%', rate:0.19)
Vat::fromId('070');     // reduced rate by numeric ID → 7%
```

### Looking up country rates
```php
use SchenkeIo\LaravelInvoice\Money\Vat;
use SchenkeIo\LaravelInvoice\Money\Country;
use SchenkeIo\LaravelInvoice\Enum\VatRate;

// Magic static method per ISO country code (lowercase)
$de = Vat::de();    // returns Country object for Germany
$at = Vat::at();    // Austria
$fr = Vat::fr();    // France

// From a Country object
$de->standard();    // Vat(19%)
$de->reduced();     // Vat(7%)
$de->none();        // Vat(0%)
$de->getVat(VatRate::Standard);  // same as standard()
$de->referenceUrl();             // URL to official VAT regulations
$de->getStatus();                // human-readable description

// Country data is in Country::DATA (45+ countries)
// Structure: ['DE' => ['name' => 'germany', 'vat' => ['190','070','000'], 'isEu' => true], ...]
```

### VatRate enum (rate tier)
```php
use SchenkeIo\LaravelInvoice\Enum\VatRate;

VatRate::Standard   // value 2 — normal rate (e.g. 19% DE)
VatRate::Reduced    // value 1 — reduced rate (e.g. 7% DE)
VatRate::None       // value 0 — zero / exempt
```

### VatCategory enum (semantic line-item category)
```php
use SchenkeIo\LaravelInvoice\Enum\VatCategory;

VatCategory::Taxable                  // 1 — standard VAT on goods/services
VatCategory::NonTaxableDamages        // 2 — damage compensation (no VAT)
VatCategory::NonTaxableTransitory     // 3 — cost reimbursement (no VAT)
VatCategory::Deposits                 // 4 — security deposits
VatCategory::ReverseChargingStandard  // 5 — EU B2B reverse charge, standard rate
VatCategory::ReverseChargingReduced   // 6 — EU B2B reverse charge, reduced rate
VatCategory::TaxableReducedVatRate    // 9 — reduced rate (books, food, etc.)

// Helper methods on VatCategory
$cat->hasVat();          // bool — whether VAT is applied
$cat->isReverseCharge(); // bool — B2B EU reverse charge?
$cat->vatRate();         // VatRate enum value
$cat->description();     // translated label (optional TranslationInterface)
```

### Using Vat in Currency calculations
```php
use SchenkeIo\LaravelInvoice\Money\{Currency, Vat};

$vat   = Vat::fromRate(0.19);
$gross = Currency::fromFloat(119.00);

$gross->fromGrossToNet($vat);   // → 100.00
$gross->vatFromGross($vat);     // →  19.00

$net = Currency::fromFloat(100.00);
$net->fromNetToGross($vat);     // → 119.00
$net->vatFromNet($vat);         // →  19.00
```

### Exceptions
`SchenkeIo\LaravelInvoice\Exceptions\VatException` is thrown when:
- Rate is below 0 (`VatException::rateToLow()`)
- Rate exceeds 0.5 (`VatException::rateToHigh()`)
- Country ISO code is not in the database (`VatException::countryNotFound($code)`)
