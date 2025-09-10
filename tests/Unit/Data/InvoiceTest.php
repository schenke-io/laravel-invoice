<?php

use Carbon\Carbon;
use SchenkeIo\Invoice\Data\Customer;
use SchenkeIo\Invoice\Data\Invoice;
use SchenkeIo\Invoice\Data\LineItem;
use SchenkeIo\Invoice\Data\Vat;

it('can generate an invoice', function () {
    // Arrange
    $customer = new Customer('John Doe', '123 Main St', '12345', 'New York', 'US');
    $vat20 = Vat::fromRate(0.2);
    $vat10 = Vat::fromRate(0.1);
    $lineItem1 = new LineItem(3, 'Product A', 100, $vat20);
    $lineItem2 = LineItem::fromItemNetPrice(2, 'Product B', 12.5, $vat10);
    $lineItem3 = LineItem::fromItemGrossPrice(5, 'Product C', 10.6, $vat20);
    $lineItem4 = LineItem::fromTotalGrossPrice(5, 'Product C', 60, $vat10);
    $invoice = new Invoice('INV-123', Carbon::parse('2020-01-01'), $customer);

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
    expect($vats[$vatKeys[0]]->toFloat())->toBe(64.09)
        ->and($vats[$vatKeys[1]]->toFloat())->toBe(116.39)
        ->and($invoice->payMe())->toBeTrue()
        ->and($invoice->isEmpty())->toBeFalse()
        ->and(strlen(json_encode($invoice->display(true))))->toBe(1331)
        ->and(strlen(json_encode($invoice->display(false))))->toBe(1424);
});
