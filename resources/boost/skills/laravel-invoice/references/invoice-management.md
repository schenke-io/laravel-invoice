# Invoice Management

Creating, calculating and rendering invoices with `InvoiceNumeric`, `LineData` and `InvoiceTableView`.

## When to use

- Building an invoice from line items and a customer.
- Reading totals, VAT breakdowns or shipment weight.
- Rendering the invoice as an HTML table, with or without a custom Blade view.
- Writing a custom line view class.

## Key classes

- `SchenkeIo\Invoice\Invoicing\Customer` — readonly DTO: `name`, `address`, `zip`, `city`, `countryCode`.
- `SchenkeIo\Invoice\Invoicing\InvoiceNumeric` — invoice aggregate; delegates math to `InvoiceCalculator`.
- `SchenkeIo\Invoice\Invoicing\InvoiceCalculator` — sums lines, groups them by `VatCategory`, tracks weight.
- `SchenkeIo\Invoice\Invoicing\LineData` — readonly line item with gross, net and VAT amounts.
- `SchenkeIo\Invoice\Enum\InvoiceLineType` — business type of a line; maps to `VatCategory` and `VatRate`.
- `SchenkeIo\Invoice\Invoicing\Views\InvoiceTableView` — DTO for rendering (extends `TableView`).
- `SchenkeIo\Invoice\Invoicing\Views\VatTableView` — VAT breakdown table used when several categories exist.
- `SchenkeIo\Invoice\Invoicing\LineViewBase` — base class for custom row renderers.
- `SchenkeIo\Invoice\Contracts\TranslationInterface` / `Support\LaravelTranslation` — optional translator for labels.

## Building an invoice

```php
use Carbon\Carbon;
use SchenkeIo\Invoice\Enum\InvoiceLineType;
use SchenkeIo\Invoice\Invoicing\Customer;
use SchenkeIo\Invoice\Invoicing\InvoiceNumeric;
use SchenkeIo\Invoice\Invoicing\LineData;

$customer = new Customer(
    name: 'Acme GmbH',
    address: 'Hauptstraße 1',
    zip: '10115',
    city: 'Berlin',
    countryCode: 'DE',
);

$invoice = new InvoiceNumeric(
    invoiceId: 'RE-2026-001',
    invoiceDate: Carbon::today(),
    customer: $customer,
);

$invoice->addLine(LineData::fromTotalGrossPrice(
    name: 'Consulting',
    totalGrossPrice: 119.00,            // Currency|float|int|string → Currency::fromAny()
    invoiceLineType: InvoiceLineType::SalesDE,
    countryCode: 'DE',                  // default 'DE'; selects the VAT rate table
));

$invoice->addLine(LineData::fromTotalNetPrice(
    name: 'Book',
    totalNetPrice: 10.00,
    invoiceLineType: InvoiceLineType::SaleBooksDE,
));

$invoice->addWeight(500); // grams, cumulative

$invoice->invoiceId;             // 'RE-2026-001' (public readonly)
$invoice->invoiceDate;           // Carbon
$invoice->customer;              // Customer
$invoice->getTotalGrossPrice();  // Currency
$invoice->getTotalNetPrice();    // Currency
$invoice->payMe();               // bool — gross > 0
$invoice->isEmpty();             // bool — gross == 0
```

## LineData

`LineData` has a private constructor; use the two factories. Properties:

```php
$line->name;                // string
$line->invoiceLineType;     // InvoiceLineType
$line->countryCode;         // string
$line->lineTotalGrossPrice; // Currency
$line->lineTotalNetPrice;   // Currency
$line->lineVatAmount;       // Currency = gross − net
```

The VAT rate is `Vat::country($countryCode)->getVat($invoiceLineType->vatRate())`. For reverse-charge types net equals gross and VAT is 0.

## InvoiceLineType enum

| Case | Value | VatCategory | Rate |
|------|-------|-------------|------|
| `SalesDE` | 10 | Taxable | Standard |
| `SalesEU` | 11 | ReverseChargingStandard | Standard, VAT 0 |
| `SaleBooksDE` | 12 | TaxableReducedVatRate | Reduced |
| `SaleBooksEU` | 13 | ReverseChargingReduced | Reduced, VAT 0 |
| `RentalFee` | 20 | Taxable | Standard |
| `ReturnElectronicsDE` | 30 | Taxable | Standard |
| `ReturnElectronicsEU` | 31 | ReverseChargingStandard | Standard, VAT 0 |
| `ReturnBooksDE` | 32 | TaxableReducedVatRate | Reduced |
| `ReturnBooksEU` | 33 | ReverseChargingReduced | Reduced, VAT 0 |
| `Deposit` | 40 | Deposits | None |
| `Damage` | 50 | NonTaxableDamages | None |
| `Refund` | 51 | Taxable | Standard |
| `Fine` | 52 | NonTaxableTransitory | None |
| `Reimbursement` | 53 | NonTaxableTransitory | None |

```php
$type->vatCategory();  // VatCategory
$type->vatRate();      // VatRate (shortcut for ->vatCategory()->vatRate())
```

## Rendering to HTML

```php
// true  → gross prices per line, footer "Gross Total / incl. VAT"
// false → net prices per line,   footer "Net Total / plus VAT / Gross Total"
$view = $invoice->invoiceTableView(isGrossInvoice: true);

echo $view->html();   // complete <table> with thead/tbody/tfoot, no Blade needed

echo $view->html([
    'table-class'        => 'table table-striped',
    'invoice-row-thead'  => '',
    'invoice-row-tbody'  => '',
    'invoice-row-tfoot'  => '',
    'invoice-row-empty'  => 'empty-line',   // spacer row between body and footer
    'invoice-cell-left'  => 'text-start',
    'invoice-cell-right' => 'text-end',
    'invoice-cell-bold'  => 'fw-bold',
]);

// Delegate to a Blade view; it receives $view (InvoiceTableView) and $config (merged array)
echo $view->html(['blade-view' => 'invoices.table']);
```

Unknown config keys are silently dropped; only the keys above (plus `blade-view`) are honoured.

## InvoiceTableView properties

```php
$view->invoiceId;          // string
$view->invoiceDate;        // Carbon
$view->customer;           // Customer
$view->totalGrossPrice;    // Currency
$view->totalWeightGrams;   // int
$view->totalWeightText;    // '500 g' or '1.5 kg' (above 1000 g)

$view->header;             // LineViewInterface — column headers (Pos., Description, Price/Net price Total)
$view->body;               // LineViewInterface[] — one InvoiceLineView per line, keyed by position (1-based)
$view->footer;             // LineViewInterface[] — total rows

isset($view->vatTableView) // VatTableView — ONLY set when the invoice has more than one VatCategory
```

Footer VAT line: with a single VAT category the label is text such as `Taxable - 19% VAT incl.`. With several categories the label embeds `$view->vatTableView->html()` (positions, description, amount, rate, VAT amount per category).

## Translations

Header and footer labels come from `__('invoice::invoice.*')` (`pos`, `description`, `total`, `price`, `net_price`, `gross_total`, `net_total`, `contained_vat`, `plus_vat`, `vat`, `amount`, `vat_rate`, `vat_amount`, `vat_categories.*`). `en` and `de` ship with the package; publish or override them in `lang/vendor/invoice/`.

Outside Laravel's `__()` helper, pass a `TranslationInterface` to `InvoiceLineView::header()`, `VatLineView::header()`, `VatLineView::lineItem()` and `VatCategory::description()`.

## Custom line views

```php
use SchenkeIo\Invoice\Contracts\LineViewInterface;
use SchenkeIo\Invoice\Invoicing\LineViewBase;

final readonly class MyLineView extends LineViewBase implements LineViewInterface
{
    public function __construct(public string $sku, public string $name, public string $price, bool $isBold = false)
    {
        parent::__construct($isBold);
    }

    /** @return array<string,bool>  property name => align right */
    public function columns(): array
    {
        return ['sku' => false, 'name' => false, 'price' => true];
    }
}
```

Each key of `columns()` must be a public property; `LineViewBase::html()` renders one cell per key. Assign instances to `$view->header`, `$view->body[]` and `$view->footer[]` of a fresh `InvoiceTableView`.

## InvoiceCalculator (low-level)

```php
use SchenkeIo\Invoice\Invoicing\InvoiceCalculator;

$calc = new InvoiceCalculator;
$calc->addLine($lineData);
$calc->addWeight(250);

$calc->getTotalGrossPrice();   // Currency
$calc->getTotalNetPrice();     // Currency
$calc->getTotalWeightGrams();  // int
$calc->getLineItems();         // array<int, LineData> keyed by 1-based position
$calc->getVatCategories();     // array<int, array<int, LineData>> keyed by VatCategory value, then position
$calc->getCategoryGrossSum();  // array<int, Currency>
$calc->getCategoryNetSum();    // array<int, Currency>
```
