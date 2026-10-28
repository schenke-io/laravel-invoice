# SEPA QR Code

Generate an EPC/SEPA credit-transfer QR code (BezahlCode / GiroCode) as a PNG data URI.

## When to use

- Embedding a scannable bank-transfer QR code on an invoice or payment page.
- Building the QR code from an `InvoiceNumeric` or from raw account details.

## Key class

- `SchenkeIo\Invoice\Banking\SepaCode` — wraps `smhg/sepa-qr-data` for the payload and `endroid/qr-code` for the PNG.

## From an invoice

```php
use SchenkeIo\Invoice\Banking\SepaCode;

$qr = SepaCode::fromInvoice(
    invoice: $invoice,                 // InvoiceNumeric
    name: 'Acme GmbH',                 // beneficiary account holder
    iban: 'DE89370400440532013000',
    infoPrefix: 'RE',                  // remittance text becomes "RE RE-2026-001"
    bic: 'COBADEFFXXX',                // optional
);
```

The amount is `$invoice->getTotalGrossPrice()->toFloat()`. The remittance text is `$infoPrefix.' '.$invoice->invoiceId` (space-joined).

## From raw data

```php
$qr = new SepaCode(
    name: 'Acme GmbH',
    iban: 'DE89370400440532013000',
    amountEuro: 119.00,
    information: 'RE-2026-001',
    bic: null,
);
```

Amounts below 0.01 are raised to 0.01 EUR. SEPA transfers are EUR only; the `Currency` code is not transmitted.

## Export

```php
$dataUri = $qr->dataUri();                          // black on white
$dataUri = $qr->dataUri(red: 0, green: 0, blue: 128); // colour of the label text, each clamped to 0–255
```

`dataUri()` returns `data:image/png;base64,...` with error-correction level *Medium* and the remittance text printed as a label under the code. Embed it directly:

```html
<img src="{{ $dataUri }}" alt="SEPA QR code" width="200" height="200">
```

## Key constraints

- Minimum amount 0.01 EUR; no upper bound is enforced here.
- BIC is optional inside SEPA but some banks still require it for cross-border transfers.
- Only PNG output is offered; for PDF generation embed the data URI as an image.
