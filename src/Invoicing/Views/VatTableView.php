<?php

namespace SchenkeIo\Invoice\Invoicing\Views;

/**
 * Table view for displaying VAT breakdown sections.
 *
 * This class specializes TableView to handle the rendering of VAT-related
 * information, typically used when an invoice contains multiple VAT rates
 * that need to be summarized individually.
 *
 * It is often embedded within an `InvoiceTableView` or rendered as a
 * standalone summary table to provide a clear tax breakdown for compliance
 * and transparency.
 */
class VatTableView extends TableView {}
