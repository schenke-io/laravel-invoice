---
name: laravel-invoice
type: Agent Skill
title: laravel-invoice
description: Build invoices with cent-precise Currency math, country VAT rates, HTML invoice tables and SEPA QR codes using schenke-io/laravel-invoice. Activate when working with Currency, CurrencyCast, Vat, Country, InvoiceNumeric, LineData, InvoiceLineType, InvoiceTableView, SepaCode, or any class in the SchenkeIo\Invoice namespace.
timestamp: 2026-10-28
---

# Laravel Invoice

`schenke-io/laravel-invoice` provides four building blocks under the `SchenkeIo\Invoice` namespace:

| Area | Classes | Reference |
|------|---------|-----------|
| Money | `Money\Currency`, `Casts\CurrencyCast`, `Money\MismatchedCurrencyException` | [references/currency-handling.md](references/currency-handling.md) |
| VAT | `Money\Vat`, `Money\Country`, `Enum\VatRate`, `Enum\VatCategory`, `Exceptions\VatException` | [references/vat-management.md](references/vat-management.md) |
| Invoicing | `Invoicing\InvoiceNumeric`, `Invoicing\LineData`, `Invoicing\Customer`, `Enum\InvoiceLineType`, `Invoicing\Views\InvoiceTableView` | [references/invoice-management.md](references/invoice-management.md) |
| Payment | `Banking\SepaCode` | [references/sepa-qr-code.md](references/sepa-qr-code.md) |

## When to use

- Parsing, calculating, or formatting money amounts.
- Storing money on Eloquent models or binding it in Livewire components.
- Looking up VAT rates for a country or deciding the VAT treatment of a line item.
- Building an invoice from line items and rendering it as an HTML table.
- Embedding a SEPA credit-transfer QR code on an invoice.

## Decision workflow

- Need a money value? Use `Currency::fromAny()` (or `fromFloat()` / `fromCents()`). Never calculate with floats.
- Need a VAT rate for a country? `Vat::country('DE')->getVat(VatRate::Standard)`.
- Need an invoice line? `LineData::fromTotalGrossPrice()` or `LineData::fromTotalNetPrice()` with an `InvoiceLineType`.
- Need a full invoice? `new InvoiceNumeric($id, $date, $customer)`, then `addLine()` and `invoiceTableView(bool)->html()`.
- Need a payment QR code? `SepaCode::fromInvoice(...)->dataUri()`.

## Quick start

```php
use Carbon\Carbon;
use SchenkeIo\Invoice\Banking\SepaCode;
use SchenkeIo\Invoice\Enum\InvoiceLineType;
use SchenkeIo\Invoice\Invoicing\Customer;
use SchenkeIo\Invoice\Invoicing\InvoiceNumeric;
use SchenkeIo\Invoice\Invoicing\LineData;

$customer = new Customer('Acme GmbH', 'Hauptstraße 1', '10115', 'Berlin', 'DE');
$invoice = new InvoiceNumeric('RE-2026-001', Carbon::today(), $customer);

$invoice->addLine(LineData::fromTotalGrossPrice('Consulting', 119.00, InvoiceLineType::SalesDE));
$invoice->addLine(LineData::fromTotalNetPrice('Book', 10.00, InvoiceLineType::SaleBooksDE));
$invoice->addWeight(500);

$html = $invoice->invoiceTableView(isGrossInvoice: true)->html();
$qr = SepaCode::fromInvoice($invoice, 'Acme GmbH', 'DE89370400440532013000', 'RE')->dataUri();
```

## Key rules

- `Currency` is `final readonly`; every method returns a new instance. Mixing ISO codes in `plus()` / `minus()` throws `MismatchedCurrencyException`.
- `Currency::fromAny(int)` treats the integer as whole units (`123` → 123,00). Use `fromCents()` for cent values.
- `str()` formats without a thousands separator (`1234,56 €`); `formattedStr()` adds it (`1.234,56 €`).
- `Vat` exposes `rate` (`0.19`), `id` (`'190'`) and `name` (`'19%'`). There is no `percent()` method.
- `Country::getVat()` takes a `VatRate` (`Standard`, `Reduced`, `None`), not a `VatCategory`.
- `LineData` has a private constructor; always use its static factories. Its `countryCode` (default `DE`) selects the rate table.
- EU reverse-charge line types (`SalesEU`, `SaleBooksEU`, `ReturnElectronicsEU`, `ReturnBooksEU`) set net = gross and VAT = 0.
- `InvoiceTableView::$vatTableView` is only initialised when the invoice has more than one VAT category; guard with `isset()`.
- Translations use the `invoice::invoice.*` keys; pass a `TranslationInterface` to the view factories to bypass Laravel's `__()`.
