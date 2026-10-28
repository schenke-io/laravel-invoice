# Currency Handling

Precise money operations and formatting with the immutable `Currency` value object.

## When to use

- Performing financial arithmetic (addition, subtraction, multiplication, division).
- Converting between floats, strings, integers, cents and `Currency`.
- Formatting money for display.
- Storing money on Eloquent models (`CurrencyCast`) or binding it in Livewire (`Wireable`).
- Converting between gross and net prices with a `Vat` rate.

## Key classes

- `SchenkeIo\Invoice\Money\Currency` — `final readonly`, implements `Stringable` and `Livewire\Wireable`; stores `int $centValue` plus an ISO 4217 code.
- `SchenkeIo\Invoice\Money\MismatchedCurrencyException` — thrown by `plus()` / `minus()` when the codes differ.
- `SchenkeIo\Invoice\Casts\CurrencyCast` — Eloquent cast for a float/decimal column.

## Creation

```php
use SchenkeIo\Invoice\Money\Currency;

new Currency(1999);                     // cents, EUR
new Currency(1999, 'USD');              // cents, USD

Currency::fromCents(1999);              // 19,99 €
Currency::fromFloat(19.99);             // 19,99 €  (null → 0)
Currency::fromAny('1.234,56 €');        // EU format, symbols stripped
Currency::fromAny('1,234.56');          // US format
Currency::fromAny(-10.5);               // negative float
Currency::fromAny(123);                 // int = whole units → 123,00 € (NOT cents)
Currency::fromAny(null);                // 0,00 €  (also '' and [])
Currency::fromAny(['cents' => 1999]);   // array from toLivewire()/JSON
Currency::fromAny($currency);           // returns the same instance

// Every factory accepts an optional ISO code as second argument
Currency::fromFloat(10.5, 'GBP');
Currency::fromAny('12,34', 'CHF');
```

## Currency code

```php
$eur = Currency::fromCents(500);
$eur->code();                 // 'EUR'
$usd = $eur->withCode('USD'); // new instance, same cents, code USD
```

Known symbols: EUR €, USD $, GBP £, CHF, JPY ¥, CNY ¥, SEK/NOK/DKK kr, PLN zł, CZK Kč, HUF Ft, RON lei, BGN лв, HRK kn, RUB ₽, TRY ₺, INR ₹, BRL R$, AUD $. Unknown codes are printed verbatim (`1.234,50 XYZ`).

## Arithmetic (immutable, returns a new instance)

```php
$a = Currency::fromFloat(10.00);
$b = Currency::fromFloat(3.50);

$a->plus($b);          // 13,50 €
$a->minus($b);         // 6,50 €
$a->times(1.5);        // 15,00 €   (float factor, rounded half-up to cents)
$a->dividedBy(3);      // 3,33 €    (int divisor, rounded half-up)
$a->dividedBy(0);      // throws DivisionByZeroError

$a->plus(Currency::fromFloat(1, 'USD')); // throws MismatchedCurrencyException
```

The result keeps the code of the left operand.

## Output

```php
$price = Currency::fromCents(123456);

$price->centValue;        // 123456 (public int)
$price->toFloat();        // 1234.56
(string) $price;          // "1234,56"      comma decimal, no thousands separator
$price->str();            // "1234,56 €"    (string) + symbol
$price->formatted();      // "1.234,56"     German thousands separator
$price->formattedStr();   // "1.234,56 €"
$price->isEmpty();        // false (true only for exactly 0 cents)
```

## VAT conversions (require a `Vat`)

```php
use SchenkeIo\Invoice\Money\Vat;

$vat = Vat::fromRate(0.19);

$gross = Currency::fromFloat(119.00);
$gross->vatFromGross($vat);    // 19,00 €   Gross × Rate / (1 + Rate)
$gross->fromGrossToNet($vat);  // 100,00 €  Gross / (1 + Rate)

$net = Currency::fromFloat(100.00);
$net->vatFromNet($vat);        // 19,00 €   Net × Rate
$net->fromNetToGross($vat);    // 119,00 €  Net × (1 + Rate)
```

## Eloquent cast

```php
use SchenkeIo\Invoice\Casts\CurrencyCast;

protected function casts(): array
{
    return ['price' => CurrencyCast::class];
}
```

- `get()` returns `Currency::fromAny($value)` (null and `''` become 0,00 €).
- `set()` stores `toFloat()`; use a `DECIMAL(10,2)` or float column.
- `serialize()` (JSON / `toArray()`) emits the `(string)` form, e.g. `"1234,56"`.
- The cast has no code column; stored values are always read back as EUR.

## Livewire

`Currency` implements `Wireable`, so it can be a public property of a Livewire component.

```php
$currency->toLivewire();                      // ['cents' => 1999, 'code' => 'EUR']
Currency::fromLivewire(['cents' => 1999, 'code' => 'USD']); // 19,99 $
Currency::fromLivewire(['cents' => 1999]);    // code defaults to EUR
Currency::fromLivewire('12,34');              // any scalar falls back to fromAny()
```

## Key constraints

- All values are integer cents; rounding is half-up at cent precision.
- Only `plus()` and `minus()` check the code. `times()`, `dividedBy()` and the VAT helpers keep the code unchanged.
- `fromAny()` strips every character except digits, `.`, `,` and `-` before parsing.
