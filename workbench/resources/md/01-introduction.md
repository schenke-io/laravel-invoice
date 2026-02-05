
### Key Benefits

- **Precise Financial Calculations**: Uses cent-based integer arithmetic via the `Currency` value object, eliminating common floating-point precision errors in financial data.
- **Automated VAT Logic**: Built-in support for VAT rules across multiple countries (including DE, AT, FR, CH, and more) with a hierarchical system of `InvoiceLineType`, `VatCategory`, and `VatRate`.
- **Flexible Rendering**: Generate clean HTML invoice tables out of the box, with full support for custom Blade templates when you need a personalized look.
- **SEPA Integration**: Easily generate SEPA-compliant QR codes (BezahlCode) to facilitate faster and more accurate payments.
- **Developer Friendly**: 100% test coverage, strict typing, and comprehensive documentation ensure a reliable and maintainable integration into your Laravel projects.
- **Eloquent Integration**: Custom casts for `Currency` make it easy to work with monetary values directly in your models.

### Core Components

#### Currency Value Object

The `Currency` class (`src/Money/Currency.php`) is the heart of all monetary operations. It stores values as integers (cents) and provides a rich set of methods for:
- **Parsing**: Use `Currency::fromAny($value)` to handle strings, floats, integers, and even other `Currency` instances. It automatically detects EU/US decimal formats.
- **VAT Conversions**: 
  - `vatFromGross(Vat $vat)`: Extract VAT amount from a gross total.
  - `vatFromNet(Vat $vat)`: Calculate VAT amount from a net total.
  - `fromGrossToNet(Vat $vat)`: Calculate net price from gross.
  - `fromNetToGross(Vat $vat)`: Calculate gross price from net.
- **Arithmetic**: Precise `plus()`, `minus()`, and `times()` operations that maintain integer integrity.

Example:
```php
$price = Currency::fromAny("1.234,56 €"); // EU format detected
$vat = new Vat(0.19);
$net = $price->fromGrossToNet($vat);
```

#### InvoiceNumeric

`InvoiceNumeric` (`src/Invoicing/InvoiceNumeric.php`) orchestrates the creation of an invoice. Its main responsibilities include:
- Managing invoice metadata (ID, Date, Customer).
- Collecting invoice lines (`addLine()`).
- Tracking total weights (`addWeight()`).
- Generating structured view data for rendering via `invoiceTableView()`.
- Providing a default rendering of the invoice header, body, and footer with automated VAT summaries.

