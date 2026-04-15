---
name: laravel-invoice-currency-handling
description: Precise money operations and formatting using the Currency value object.
---

### When to use
- When performing financial arithmetic (addition, subtraction, multiplication).
- When converting between floats, strings, integers, and cents.
- When formatting currency for display.
- When integrating currency fields in Laravel Eloquent models or Livewire components.
- When converting between gross and net prices using a VAT rate.

### Key classes
- `SchenkeIo\LaravelInvoice\Money\Currency` — immutable value object storing cents as `int`
- `SchenkeIo\LaravelInvoice\Casts\CurrencyCast` — Eloquent cast for storing as float column

### Creation (factory methods)
```php
use SchenkeIo\LaravelInvoice\Money\Currency;

Currency::fromAny(19.99);          // from float, int, or EU/US-formatted string
Currency::fromFloat(19.99);        // explicit float
Currency::fromCents(1999);         // from integer cents
Currency::fromLivewire($value);    // restore from Livewire wire:model
```

### Arithmetic (immutable — returns new instance)
```php
$a = Currency::fromFloat(10.00);
$b = Currency::fromFloat(3.50);

$a->plus($b);          // Currency(13.50)
$a->minus($b);         // Currency(6.50)
$a->times(1.5);        // Currency(15.00)
```

### Output
```php
$price = Currency::fromFloat(1234.56);

$price->toFloat();       // 1234.56
$price->str();           // "1.234,56 €"   (German locale, EUR symbol)
(string) $price;         // "1.234,56"     (Stringable interface)
$price->isEmpty();       // false
$price->toLivewire();    // 1234.56  (for Livewire serialization)
```

### VAT conversions (requires a Vat instance)
```php
use SchenkeIo\LaravelInvoice\Money\Vat;

$gross = Currency::fromFloat(119.00);
$vat   = Vat::fromRate(0.19);

$gross->vatFromGross($vat);     // VAT portion  → 19.00  (Gross × Rate / (1 + Rate))
$gross->fromGrossToNet($vat);   // Net price    → 100.00

$net = Currency::fromFloat(100.00);
$net->vatFromNet($vat);         // VAT portion  → 19.00  (Net × Rate)
$net->fromNetToGross($vat);     // Gross price  → 119.00
```

### Eloquent cast
```php
// migration: float column  (e.g., price FLOAT)
// model:
protected $casts = [
    'price' => CurrencyCast::class,
];

// reads as Currency, writes as float, serializes as formatted string
```

### Livewire support
`Currency` implements the `Wireable` interface — use `toLivewire()` / `fromLivewire()` for
two-way binding in Livewire components without additional boilerplate.

### Key constraints
- Internally stored as **integer cents** to avoid floating-point errors.
- All arithmetic returns a **new** `Currency` instance (readonly/immutable).
- `fromAny()` accepts both US (`1,234.56`) and EU (`1.234,56`) number formats.
