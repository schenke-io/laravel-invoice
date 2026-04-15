---
name: laravel-invoice-invoice-management
description: Creating, calculating, and rendering invoices using InvoiceNumeric and InvoiceCalculator.
---

### When to use
- When creating new invoices with line items.
- When calculating totals, VAT breakdowns, or weight.
- When rendering an invoice as an HTML table.
- When building a reusable invoice view for Blade or direct HTML output.

### Key classes
- `SchenkeIo\LaravelInvoice\Invoicing\InvoiceNumeric` — top-level invoice object
- `SchenkeIo\LaravelInvoice\Invoicing\InvoiceCalculator` — low-level aggregator (used internally)
- `SchenkeIo\LaravelInvoice\Invoicing\LineData` — a single invoice line item (readonly DTO)
- `SchenkeIo\LaravelInvoice\Invoicing\Customer` — customer data (readonly DTO)
- `SchenkeIo\LaravelInvoice\Enum\InvoiceLineType` — enum specifying the sales/rental/return type
- `SchenkeIo\LaravelInvoice\Invoicing\Views\InvoiceTableView` — view data for HTML rendering

### Building an invoice
```php
use Carbon\Carbon;
use SchenkeIo\LaravelInvoice\Invoicing\{InvoiceNumeric, Customer, LineData};
use SchenkeIo\LaravelInvoice\Enum\InvoiceLineType;

$customer = new Customer(
    name: 'Acme GmbH',
    address: 'Hauptstraße 1',
    zip: '10115',
    city: 'Berlin',
    countryCode: 'DE'
);

$invoice = new InvoiceNumeric(
    invoiceId: 'RE-2024-001',
    invoiceDate: Carbon::today(),
    customer: $customer
);

// Add line items
$invoice->addLine(LineData::fromTotalGrossPrice(
    name: 'Consulting',
    totalGrossPrice: 119.00,   // also accepts Currency or string
    invoiceLineType: InvoiceLineType::SalesDE,
    countryCode: 'DE'
));

$invoice->addLine(LineData::fromTotalNetPrice(
    name: 'Book',
    totalNetPrice: 10.00,
    invoiceLineType: InvoiceLineType::SaleBooksDE,
    countryCode: 'DE'
));

// Optional: add package weight
$invoice->addWeight(500); // grams

// Read totals
$invoice->getTotalGrossPrice();  // Currency
$invoice->getTotalNetPrice();    // Currency
$invoice->payMe();               // bool — gross > 0
$invoice->isEmpty();             // bool — gross == 0
```

### InvoiceLineType enum (sales context)
```php
use SchenkeIo\LaravelInvoice\Enum\InvoiceLineType;

// Sales
InvoiceLineType::SalesDE          // 10 — domestic goods/services (19% DE)
InvoiceLineType::SalesEU          // 11 — EU B2B (reverse charge standard)
InvoiceLineType::SaleBooksDE      // 12 — books/food domestic (7% DE)
InvoiceLineType::SaleBooksEU      // 13 — books EU B2B (reverse charge reduced)

// Rental
InvoiceLineType::RentalFee        // 20

// Returns
InvoiceLineType::ReturnElectronicsDE  // 30
InvoiceLineType::ReturnElectronicsEU  // 31
InvoiceLineType::ReturnBooksDE        // 32
InvoiceLineType::ReturnBooksEU        // 33

// Special
InvoiceLineType::Deposit          // 40 — security deposit
InvoiceLineType::Damage           // 50 — damage compensation (non-taxable)
InvoiceLineType::Refund           // 51
InvoiceLineType::Fine             // 52
InvoiceLineType::Reimbursement    // 53 — cost pass-through (non-taxable)

// Helper methods
$type->vatCategory();  // VatCategory enum
$type->vatRate();      // VatRate enum
```

### Rendering to HTML
```php
// $isGrossInvoice = true  → display gross prices (consumer invoices)
// $isGrossInvoice = false → display net prices   (B2B invoices)
$view = $invoice->invoiceTableView(isGrossInvoice: true);

// Minimal HTML output (no Blade required)
echo $view->html();

// Custom CSS classes
echo $view->html([
    'table-class'          => 'table table-striped',
    'invoice-cell-right'   => 'text-end',
    'invoice-cell-bold'    => 'fw-bold',
]);

// Blade view override
echo $view->html(['blade-view' => 'invoices.table']);
```

### InvoiceTableView properties
```php
$view->invoiceId;          // string
$view->invoiceDate;        // Carbon
$view->customer;           // Customer
$view->totalGrossPrice;    // Currency
$view->totalWeightGrams;   // int
$view->totalWeightText;    // formatted string
$view->vatTableView;       // VatTableView (VAT breakdown section)

// Table structure (from parent TableView)
$view->header;   // LineViewInterface — column headers
$view->body;     // LineViewInterface[] — line items
$view->footer;   // LineViewInterface[] — totals rows
```

### Reverse charge handling
Lines with `SalesEU` or `SaleBooksEU` automatically set `lineTotalNetPrice = lineTotalGrossPrice`
and `lineVatAmount = 0`. No extra configuration required.

### InvoiceCalculator (low-level)
`InvoiceCalculator` is used internally by `InvoiceNumeric` but can be used standalone:
```php
$calc = new InvoiceCalculator();
$calc->addLine($lineData);
$calc->getTotalGrossPrice();
$calc->getTotalNetPrice();
$calc->getTotalWeightGrams();
$calc->getLineItems();
$calc->getVatCategories();     // array<int, array<int, LineData>> grouped by category
$calc->getCategoryGrossSum();  // array<int, Currency>
$calc->getCategoryNetSum();    // array<int, Currency>
```
