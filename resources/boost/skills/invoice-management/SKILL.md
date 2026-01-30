---
name: invoice-management
description: Creating, calculating, and rendering invoices using InvoiceNumeric and InvoiceCalculator.
---

### When to use
- When creating new invoices.
- When adding line items or weight to an invoice.
- When rendering an invoice as an HTML table.

### Features
- **Invoice Creation**: Instantiate `InvoiceNumeric` with ID, date, and customer.
- **Line Management**: Add lines using `LineData` and `InvoiceLineType`.
- **Automated Calculations**: Calculates total gross, total net, and weight automatically.
- **Tax Breakdown**: Handles multiple VAT categories and provides a summary.
- **Rendering**: Generate `InvoiceTableView` for HTML display.
- **Weight Tracking**: Accumulates total weight in grams for shipping purposes.
