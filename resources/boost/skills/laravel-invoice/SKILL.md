---
name: laravel-invoice
description: Comprehensive Laravel package for invoice management, precise currency handling, SEPA QR code generation, and VAT calculations.
---

# Laravel Invoice Skill

The `laravel-invoice` skill provides guidance and code references for working with the `schenke-io/laravel-invoice` package.

## Subfiles / Specifics

- **Currency Handling**: Precise money operations, immutable arithmetic, and formatting using the `Currency` value object and `CurrencyCast`. See [currency-handling.md](currency-handling.md).
- **Invoice Management**: Creating, calculating, and rendering structured invoices via `InvoiceNumeric`, `InvoiceCalculator`, `LineData`, and `InvoiceTableView`. See [invoice-management.md](invoice-management.md).
- **SEPA QR Code**: Generating SEPA-compliant QR codes (BezahlCode) for bank transfers and payments via `SepaCode`. See [sepa-qr-code.md](sepa-qr-code.md).
- **VAT Management**: Handling VAT rates, country-specific rules, and categories (`Vat`, `Country`, `VatRate`, `VatCategory`). See [vat-management.md](vat-management.md).
