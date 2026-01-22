# Usage Examples

Here are some examples of how to use the `laravel-invoice` package, ranging from basic currency operations to complex multi-tax invoices.

### 1. Basic Currency Operations

The `Currency` class ensures precision by using cents internally.

```php
use SchenkeIo\Invoice\Money\Currency;

// Create from various formats
$price = Currency::fromFloat(19.99);
$price2 = Currency::fromAny('12,50 €');

// Arithmetic
$total = $price->plus($price2); // 32.49 €
$discounted = $total->times(0.9); // 10% discount

echo $discounted->str(); // "29,24 €"
```

### 2. Simple Invoice Generation

Creating a basic invoice with a single tax rate.

```php
use SchenkeIo\Invoice\Invoicing\InvoiceNumeric;
use SchenkeIo\Invoice\Invoicing\Customer;
use SchenkeIo\Invoice\Invoicing\LineData;
use SchenkeIo\Invoice\Enum\InvoiceLineType;
use Carbon\Carbon;

$customer = new Customer('Jane Doe', 'Main Street 1', '12345', 'Berlin', 'DE');
$invoice = new InvoiceNumeric('INV-2023-001', Carbon::now(), $customer);

// Add items (Gross price automatically calculates Net based on type)
$invoice->addLine(LineData::fromTotalGrossPrice(
    'Web Design Service', 
    119.00, 
    InvoiceLineType::SalesDE
));

// Get HTML table for the invoice
echo $invoice->invoiceTableView(isGrossInvoice: true)->html();
```

### 3. Complex Multi-Tax Invoice

Invoices can contain items with different tax categories. The package automatically groups them in the summary.

```php
use SchenkeIo\Invoice\Invoicing\InvoiceNumeric;
use SchenkeIo\Invoice\Invoicing\LineData;
use SchenkeIo\Invoice\Enum\InvoiceLineType;

$invoice = new InvoiceNumeric('INV-002', Carbon::now(), $customer);

// Standard VAT (19% for DE)
$invoice->addLine(LineData::fromTotalNetPrice('Laptop', 1000.00, InvoiceLineType::SalesDE));

// Reduced VAT (7% for DE)
$invoice->addLine(LineData::fromTotalGrossPrice('Technical Book', 10.70, InvoiceLineType::SaleBooksDE));

// Non-taxable deposit
$invoice->addLine(LineData::fromTotalGrossPrice('Security Deposit', 500.00, InvoiceLineType::Deposit));

// The generated HTML will include a detailed tax breakdown section
echo $invoice->invoiceTableView(false)->html();
```

### 4. Multi-Country Support

The package supports VAT rates for various countries. You can specify the country code when adding lines.

```php
use SchenkeIo\Invoice\Invoicing\LineData;
use SchenkeIo\Invoice\Enum\InvoiceLineType;

// Austrian Invoice (20% Standard VAT)
$lineAT = LineData::fromTotalNetPrice(
    'Consulting', 
    100.00, 
    InvoiceLineType::SalesDE,  // Base type
    'AT'                       // Country code
);

// French Invoice (20% Standard VAT)
$lineFR = LineData::fromTotalNetPrice(
    'Service', 
    100.00, 
    InvoiceLineType::SalesDE, 
    'FR'
);
```

### 5. SEPA QR Code Integration

Generate a SEPA-compliant QR code for easy payments.

```php
use SchenkeIo\Invoice\Banking\SepaCode;

$sepa = SepaCode::fromInvoice(
    $invoice,
    'Schenke Io',
    'DE12345678901234567890',
    'Invoice'
);

// Get a Data URI for an <img> tag
echo '<img src="' . $sepa->dataUri() . '" />';
```
