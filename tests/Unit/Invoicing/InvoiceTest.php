<?php

use Carbon\Carbon;
use SchenkeIo\Invoice\Banking\SepaCode;
use SchenkeIo\Invoice\Invoicing\Customer;
use SchenkeIo\Invoice\Invoicing\InvoiceNumericData;
use SchenkeIo\Invoice\Invoicing\LineData;
use SchenkeIo\Invoice\Money\Vat;

it('can generate an invoice', function () {
    // Arrange
    $customer = new Customer('John Doe', '123 Main St', '12345', 'New York', 'US');
    $vat20 = Vat::fromRate(0.2);
    $vat10 = Vat::fromRate(0.1);
    $lineItem1 = LineData::fromTotalGrossPrice(3, 'Product A', 100, $vat20);
    $lineItem2 = LineData::fromItemNetPrice(2, 'Product B', 12.5, $vat10);
    $lineItem3 = LineData::fromItemGrossPrice(5, 'Product C', 10.6, $vat20);
    $lineItem4 = LineData::fromTotalGrossPrice(5, 'Product C', 60, $vat10);
    $invoice = new InvoiceNumericData('INV-123', Carbon::parse('2020-01-01'), $customer);

    // Act
    $invoice->addLine($lineItem1);
    $invoice->addLine($lineItem2);
    $invoice->addLine($lineItem3);
    $invoice->addLine($lineItem4);

    $invoice->addWeight(123);

    // Assert
    expect($invoice->totalGrossPrice->toFloat())->toBe(240.5);
    $vats = $invoice->vats();
    $vatKeys = array_keys($vats);
    $invoiceViewDataGross = $invoice->invoiceViewData(true);
    $invoiceViewDataNet = $invoice->invoiceViewData(false);
    expect($vats[$vatKeys[0]]->toFloat())->toBe(7.95)
        ->and($vats[$vatKeys[1]]->toFloat())->toBe(25.5)
        ->and($invoice->payMe())->toBeTrue()
        ->and($invoice->isEmpty())->toBeFalse()
        ->and($invoiceViewDataGross)->toBeObject()
        ->and($invoiceViewDataNet->html())->toBeString()
        ->and(strlen(json_encode($invoiceViewDataGross)))->toBe(1160);

});

it('calculates VAT correctly', function () {
    $customer = new Customer('John Doe', '123 Main St', '12345', 'New York', 'US');
    $vat = Vat::deStandard();
    $lineItem = LineData::fromTotalGrossPrice(3, 'Product A', 100, $vat);
    $invoice = new InvoiceNumericData('INV-123', Carbon::parse('2020-01-01'), $customer);
    $invoice->addLine($lineItem);
    $sepa = SepaCode::fromInvoice($invoice, 'name', 'IBAN', 'BIC');
    expect($invoice->vats()['19%']->centValue)->toBe(1597)
        ->and($invoice->totalGrossPrice->centValue)->toBe(10000)
        ->and($invoice->totalNetPrice->centValue)->toBe(8403)
        ->and($sepa->dataUri())->toBestring();

});
