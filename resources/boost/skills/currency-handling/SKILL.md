---
name: currency-handling
description: Precise money operations and formatting using the Currency value object.
---

### When to use
- When performing financial arithmetic (addition, subtraction, multiplication).
- When converting between floats, strings, and integer cents.
- When formatting currency for display.

### Features
- **Precision Arithmetic**: Avoid floating-point issues by using integer cents.
- **Flexible Creation**: Create from floats, strings, or other `Currency` objects.
- **Arithmetic Operations**: Methods for `plus`, `minus`, and `times` (factor-based).
- **Formatting**: Localized currency string representation (e.g., "19,99 €").
- **Livewire Support**: Implements `Wireable` for seamless use in Livewire components.
- **VAT Conversions**: Utilities to convert between gross and net values using a `Vat` rate.
