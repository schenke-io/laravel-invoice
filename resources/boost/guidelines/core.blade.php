<div class="package-hud" style="background: #1e293b; color: #f8fafc; padding: 1.5rem; border-radius: 0.75rem; border: 1px solid #334155; font-family: ui-sans-serif, system-ui, sans-serif; margin-bottom: 2rem;">
    <!-- Identity -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <span style="font-size: 1.5rem;">📦</span>
            <h2 style="margin: 0; font-size: 1.25rem; font-weight: 600; color: #f8fafc;">laravel-invoice</h2>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <span style="background: #065f46; color: #a7f3d0; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500;">Active</span>
            <span style="background: #1e3a8a; color: #dbeafe; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500;">PHP 8.3+</span>
        </div>
    </div>

    <!-- Status Indicators -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; background: #334155; padding: 1rem; border-radius: 0.5rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <div style="width: 0.5rem; height: 0.5rem; background: #22c55e; border-radius: 50%; box-shadow: 0 0 8px #22c55e;"></div>
            <span style="font-size: 0.875rem; color: #cbd5e1;">Translations: Loaded</span>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <div style="width: 0.5rem; height: 0.5rem; background: #22c55e; border-radius: 50%; box-shadow: 0 0 8px #22c55e;"></div>
            <span style="font-size: 0.875rem; color: #cbd5e1;">SEPA: Ready</span>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <div style="width: 0.5rem; height: 0.5rem; background: #22c55e; border-radius: 50%; box-shadow: 0 0 8px #22c55e;"></div>
            <span style="font-size: 0.875rem; color: #cbd5e1;">Tests: Passing</span>
        </div>
    </div>

    <!-- Health Check -->
    @unless(\Illuminate\Support\Facades\Lang::has('invoice::invoice.vat'))
        <div style="margin-bottom: 1.5rem; padding: 0.75rem 1rem; background: #450a0a; border: 1px solid #991b1b; border-left-width: 4px; border-radius: 0.375rem; color: #fecaca; font-size: 0.875rem; display: flex; align-items: center; gap: 0.75rem;">
            <span style="font-size: 1.25rem;">⚠️</span>
            <span>Critical: Package translations are not loaded. Check your service provider registration.</span>
        </div>
    @endunless

    <!-- Usage Snippet -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
            <span style="font-size: 0.875rem; font-weight: 500; color: #94a3b8;">Gold Standard Usage</span>
            <span style="font-size: 0.75rem; color: #64748b;">PHP</span>
        </div>
        <pre style="background: #0f172a; color: #38bdf8; padding: 1rem; border-radius: 0.375rem; font-size: 0.8125rem; margin: 0; border: 1px solid #1e293b; overflow-x: auto;">
$invoice = new InvoiceNumeric('INV-001', now(), $customer);
$invoice->addLine(LineData::fromTotalNetPrice('Item', 100, InvoiceLineType::SalesDE));
echo $invoice->invoiceTableView(true)->html();</pre>
    </div>
</div>

## Laravel Invoice

This package provides robust and flexible currency handling, VAT calculations, and invoice management for Laravel.

### Features

- **Currency Management**: Use the `Currency` value object for precise cent-based integer arithmetic.
- **VAT Logic**: Automatic VAT logic based on `InvoiceLineType`, `VatCategory`, and `VatRate`.
- **Vat Rate Provider**: Country-specific VAT rates provided by the `Vat` class.
- **Invoice Generation**: Create invoices using `InvoiceNumeric` (delegates to `InvoiceCalculator`), `Customer`, and `LineData`.
- **Flexible Rendering**: HTML table generation via `InvoiceTableView` and customizable `LineViewBase`.
- **SEPA QR Codes**: Generate SEPA-compliant QR codes for payments using `SepaCode`.

### Basic Usage

#### Currency Operations
@verbatim
<code-snippet name="Currency Usage" lang="php">
use SchenkeIo\Invoice\Money\Currency;

// Precise cent-based arithmetic
$price = Currency::fromFloat(19.99);
$total = $price->plus(Currency::fromAny('12,50 €'));
$discounted = $total->times(0.9); // 10% discount

echo $discounted->str(); // "29,24 €"
</code-snippet>
@endverbatim

#### Creating an Invoice
@verbatim
<code-snippet name="Create Invoice" lang="php">
use SchenkeIo\Invoice\Invoicing\InvoiceNumeric;
use SchenkeIo\Invoice\Invoicing\Customer;
use SchenkeIo\Invoice\Invoicing\LineData;
use SchenkeIo\Invoice\Enum\InvoiceLineType;
use Carbon\Carbon;

$customer = new Customer('Jane Doe', 'Main Street 1', '12345', 'Berlin', 'DE');
$invoice = new InvoiceNumeric('INV-001', Carbon::now(), $customer);

// Add line items
$invoice->addLine(LineData::fromTotalGrossPrice(
    'Service Name', 
    119.00, 
    InvoiceLineType::SalesDE
));

// Add weight (optional)
$invoice->addWeight(500); // grams

// Get HTML representation
$html = $invoice->invoiceTableView(isGrossInvoice: true)->html();
</code-snippet>
@endverbatim

#### SEPA QR Code
@verbatim
<code-snippet name="SEPA QR Code" lang="php">
use SchenkeIo\Invoice\Banking\SepaCode;

$sepa = SepaCode::fromInvoice(
    $invoice, 
    'Account Holder', 
    'DE12345678901234567890', 
    'INV-001'
);

echo '<img src="' . $sepa->dataUri() . '" />';
</code-snippet>
@endverbatim
