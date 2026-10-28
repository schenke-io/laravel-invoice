## schenke-io/laravel-invoice

- Cent-precise money (`Currency`), country VAT rates (`Vat`, `Country`), invoice building and HTML rendering (`InvoiceNumeric`, `LineData`, `InvoiceTableView`) and SEPA payment QR codes (`SepaCode`).
- IMPORTANT: Activate the `laravel-invoice` skill when working with currency, VAT, invoice lines, invoice tables, the `CurrencyCast`, or SEPA QR codes.

### Conventions

- The root namespace is `SchenkeIo\Invoice` (for example `SchenkeIo\Invoice\Money\Currency`). There is no `SchenkeIo\LaravelInvoice` namespace.
- Money is always a `Currency` value object: integer cents plus an ISO 4217 code (default `EUR`). Never do float arithmetic on prices.
- `Currency` is immutable. Every operation returns a new instance. `plus()` and `minus()` throw `MismatchedCurrencyException` when the codes differ.
- Resolve VAT via `Vat::country('DE')` (or `Vat::de()`), then `->getVat(VatRate::Standard)`, `->standard()`, `->reduced()` or `->none()`. Unknown ISO codes throw `VatException`.
- Create invoice lines only with `LineData::fromTotalGrossPrice()` or `LineData::fromTotalNetPrice()`. The `InvoiceLineType` decides the VAT category and rate; reverse-charge types carry no VAT.
- Rendering never needs Blade: `$invoice->invoiceTableView(isGrossInvoice: true)->html()` returns a complete `<table>`. Pass a config array to override CSS classes or a `blade-view` name.
- The `InvoiceServiceProvider` is auto-discovered and registers the `invoice::` translation namespace (`en`, `de`).

### Currency

@verbatim
<code-snippet name="Currency arithmetic and formatting" lang="php">
use SchenkeIo\Invoice\Money\Currency;

$price = Currency::fromFloat(19.99);
$total = $price->plus(Currency::fromAny('12,50 €'));
$discounted = $total->times(0.9);

echo $discounted->str();                              // "29,24 €"
echo Currency::fromCents(123450)->formattedStr();     // "1.234,50 €"
echo Currency::fromCents(123450, 'USD')->formattedStr(); // "1.234,50 $"
</code-snippet>
@endverbatim

### Invoice

@verbatim
<code-snippet name="Build and render an invoice" lang="php">
use Carbon\Carbon;
use SchenkeIo\Invoice\Enum\InvoiceLineType;
use SchenkeIo\Invoice\Invoicing\Customer;
use SchenkeIo\Invoice\Invoicing\InvoiceNumeric;
use SchenkeIo\Invoice\Invoicing\LineData;

$customer = new Customer('Jane Doe', 'Main Street 1', '12345', 'Berlin', 'DE');
$invoice = new InvoiceNumeric('INV-001', Carbon::now(), $customer);

$invoice->addLine(LineData::fromTotalGrossPrice('Service', 119.00, InvoiceLineType::SalesDE));
$invoice->addLine(LineData::fromTotalNetPrice('Book', 10.00, InvoiceLineType::SaleBooksDE));
$invoice->addWeight(500); // grams, optional

$html = $invoice->invoiceTableView(isGrossInvoice: true)->html();
</code-snippet>
@endverbatim

### SEPA QR code

@verbatim
<code-snippet name="SEPA payment QR code from an invoice" lang="php">
use SchenkeIo\Invoice\Banking\SepaCode;

$sepa = SepaCode::fromInvoice($invoice, 'Account Holder', 'DE89370400440532013000', 'RE', 'COBADEFFXXX');

echo '<img src="'.$sepa->dataUri().'" alt="SEPA QR">';
</code-snippet>
@endverbatim

### VAT

@verbatim
<code-snippet name="Country VAT rates" lang="php">
use SchenkeIo\Invoice\Enum\VatRate;
use SchenkeIo\Invoice\Money\Vat;

$vat = Vat::country('DE')->getVat(VatRate::Standard);
echo $vat->rate;  // 0.19
echo $vat->name;  // "19%"

$reduced = Vat::de()->reduced();   // 7%
$custom = Vat::fromRate(0.081);    // "8,1%"
</code-snippet>
@endverbatim
