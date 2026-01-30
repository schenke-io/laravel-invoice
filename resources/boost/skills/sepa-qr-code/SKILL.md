---
name: sepa-qr-code
description: Generating SEPA-compliant QR codes (BezahlCode) for bank transfers and payments.
---
### When to use
- When you need to provide a QR code for bank transfers on an invoice.
- When facilitating payments via SEPA QR codes.
### Features
- **Invoice Integration**: Directly create SEPA codes from `InvoiceNumeric` instances.
- **QR Code Generation**: Standard-compliant QR codes for easy payment scanning.
- **BIC Support**: Optional BIC for international transfers.
- **Data URI Export**: Exports to PNG Data URI for use in any HTML image tag.
- **Manual Construction**: Create SEPA codes from manual account details when an invoice object isn't available.
