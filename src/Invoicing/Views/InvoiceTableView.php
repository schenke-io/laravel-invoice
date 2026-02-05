<?php

namespace SchenkeIo\Invoice\Invoicing\Views;

use Carbon\Carbon;
use SchenkeIo\Invoice\Invoicing\Customer;
use SchenkeIo\Invoice\Money\Currency;

/**
 * Data transfer object for the complete invoice table view.
 *
 * This class extends TableView to hold all the necessary information
 * for a full invoice display, including invoice metadata, customer
 * details, line items, and an optional VAT breakdown table.
 *
 * Properties:
 * - `invoiceId`: Human-readable invoice identifier.
 * - `invoiceDate`: Issue date of the invoice.
 * - `totalWeightGrams` / `totalWeightText`: Aggregated shipment weight.
 * - `customer`: Recipient data (name, address, country).
 * - `totalGrossPrice`: Grand total in gross.
 * - `vatTableView`: Optional summary table with VAT breakdown.
 */
class InvoiceTableView extends TableView
{
    public string $invoiceId = '';

    public Carbon $invoiceDate;

    public int $totalWeightGrams = 0;

    public string $totalWeightText = '';

    public Customer $customer;

    public Currency $totalGrossPrice;

    public VatTableView $vatTableView;
}
