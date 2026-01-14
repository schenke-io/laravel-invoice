<?php

use Carbon\Carbon;
use SchenkeIo\Invoice\Banking\SepaCode;
use SchenkeIo\Invoice\Invoicing\Customer;
use SchenkeIo\Invoice\Invoicing\InvoiceNumeric;

it('can generate a sepa code', function () {
    $code = new SepaCode('name', '84493', 12.34, 'information');
    expect($code->dataUri())->toBeString();
});

it('can create from invoice', function () {
    $customer = new Customer('John Doe', '123 Main St', '12345', 'New York', 'US');
    $invoice = new InvoiceNumeric('INV-123', Carbon::parse('2020-01-01'), $customer);
    $code = SepaCode::fromInvoice($invoice, 'name', 'IBAN123', 'prefix');

    expect($code)->toBeInstanceOf(SepaCode::class)
        ->and($code->dataUri())->toBeString();
});
