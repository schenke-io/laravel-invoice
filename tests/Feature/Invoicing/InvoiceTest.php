<?php

use Carbon\Carbon;
use SchenkeIo\Invoice\Enum\InvoiceLineType;
use SchenkeIo\Invoice\Invoicing\Customer;
use SchenkeIo\Invoice\Invoicing\InvoiceNumeric;
use SchenkeIo\Invoice\Invoicing\LineData;
use SchenkeIo\Invoice\Money\Vat;

it('can generate an invoice', function () {
    // Arrange
    $customer = new Customer('John Doe', '123 Main St', '12345', 'New York', 'DE');
    $vatType = InvoiceLineType::SalesDE;
    $lineItem1 = LineData::fromTotalGrossPrice('Product A', 100, $vatType);
    $lineItem2 = LineData::fromTotalNetPrice('Product B', 12.5, $vatType);
    $lineItem3 = LineData::fromTotalNetPrice('Product C', 10.6, $vatType);
    $lineItem4 = LineData::fromTotalGrossPrice('Product C', 60, $vatType);
    $invoice = new InvoiceNumeric('INV-123', Carbon::parse('2020-01-01'), $customer);

    // Act
    $invoice->addLine($lineItem1);
    $invoice->addLine($lineItem2);
    $invoice->addLine($lineItem3);
    $invoice->addLine($lineItem4);

    $invoice->addWeight(123);

    // Assert
    expect($invoice->getTotalGrossPrice()->toFloat())->toBe(187.49);
    $invoiceViewDataGross = $invoice->invoiceTableView(true);
    $invoiceViewDataNet = $invoice->invoiceTableView(false);
    expect($invoice->payMe())->toBeTrue()
        ->and($invoice->isEmpty())->toBeFalse()
        ->and($invoiceViewDataGross)->toBeObject()
        ->and($invoiceViewDataNet->html())->toBeString();

    // Test multi-vat invoice view
    $lineItem5 = LineData::fromTotalGrossPrice('Product D', 50, InvoiceLineType::SaleBooksDE);
    $invoice->addLine($lineItem5);
    $multiVatView = $invoice->invoiceTableView(true);
    expect($multiVatView->html())->toBeString()
        ->and($multiVatView->html())->toContain(__('invoice::invoice.vat_categories.taxable_reduced_vat_rate'));
});

it('calculates VAT correctly', function () {
    $customer = new Customer('John Doe', '123 Main St', '12345', 'New York', 'DE');
    $invoice = new InvoiceNumeric('INV-123', Carbon::parse('2020-01-01'), $customer);

    $lineItem1 = LineData::fromTotalGrossPrice('Product A', 100, InvoiceLineType::RentalFee);
    $invoice->addLine($lineItem1);

    // 100 Gross at 19% VAT (Standard DE)
    // Net = 100 / 1.19 = 84.0336... -> 84.03
    // VAT = 100 - 84.03 = 15.97

    expect($invoice->getTotalGrossPrice()->toFloat())->toBe(100.0)
        ->and($invoice->getTotalNetPrice()->toFloat())->toBe(84.03)
        ->and($invoice->getTotalGrossPrice()->minus($invoice->getTotalNetPrice())->toFloat())->toBe(15.97);

    // add a second line with reduced VAT (7%)
    $lineItem2 = LineData::fromTotalGrossPrice('Book B', 10, InvoiceLineType::SaleBooksDE);
    $invoice->addLine($lineItem2);

    // 10 Gross at 7% VAT (Reduced DE)
    // Net = 10 / 1.07 = 9.345... -> 9.35
    // VAT = 10 - 9.35 = 0.65

    // Total:
    // Gross: 100 + 10 = 110.00
    // Net: 84.03 + 9.35 = 93.38
    // VAT: 15.97 + 0.65 = 16.62

    expect($invoice->getTotalGrossPrice()->toFloat())->toBe(110.0)
        ->and($invoice->getTotalNetPrice()->toFloat())->toBe(93.38)
        ->and($invoice->getTotalGrossPrice()->minus($invoice->getTotalNetPrice())->toFloat())->toBe(16.62);
});
