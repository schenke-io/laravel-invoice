---
name: laravel-invoice-sepa-qr-code
description: Generating SEPA-compliant QR codes (BezahlCode) for bank transfers and payments.
---

### When to use
- When you need to embed a scannable bank-transfer QR code on an invoice.
- When facilitating SEPA credit transfer payments via QR scanning.
- When generating a QR code from an existing `InvoiceNumeric` instance.
- When building a QR code manually from raw account details.

### Key class
- `SchenkeIo\LaravelInvoice\Banking\SepaCode`

Uses the `sepa-qr` package internally to produce a standard SEPA credit transfer QR code
(EPC QR / BezahlCode), exported as a PNG Data URI.

### Creating from an invoice
```php
use SchenkeIo\LaravelInvoice\Banking\SepaCode;

$qr = SepaCode::fromInvoice(
    invoice:    $invoice,       // InvoiceNumeric — amount and reference extracted automatically
    name:       'Acme GmbH',   // beneficiary name
    iban:       'DE89370400440532013000',
    infoPrefix: 'RE',           // prepended to invoice ID in the transfer reference
    bic:        'COBADEFFXXX'  // optional — required for some non-SEPA banks
);
```

The factory reads `$invoice->getTotalGrossPrice()->toFloat()` as the amount and uses the
invoice ID (prefixed with `$infoPrefix`) as the payment reference.

### Creating manually (no invoice object)
```php
$qr = new SepaCode(
    name:        'Acme GmbH',
    iban:        'DE89370400440532013000',
    amountEuro:  119.00,
    information: 'RE-2024-001',
    bic:         null           // optional
);
```

Minimum enforced amount is **0.01 EUR**.

### Exporting as a Data URI
```php
// Default: black QR code
$dataUri = $qr->dataUri();

// Custom RGB colour (e.g. dark blue)
$dataUri = $qr->dataUri(red: 0, green: 0, blue: 128);
```

The return value is a PNG Data URI string (`data:image/png;base64,...`) ready for use
directly in an HTML `<img>` tag:

```html
<img src="{{ $dataUri }}" alt="SEPA QR Code" width="200" height="200">
```

### Key constraints
- Amount must be ≥ 0.01 EUR.
- BIC is optional for intra-SEPA transfers but recommended for cross-border payments.
- The QR code is generated as a PNG; use `dataUri()` for embedding in HTML or PDF.
