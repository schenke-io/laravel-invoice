<?php

use Carbon\Carbon;
use SchenkeIo\Invoice\Invoicing\Customer;
use SchenkeIo\Invoice\Invoicing\InvoiceNumeric;

it('can render using a blade view', function () {
    $customer = new Customer('John Doe', '123 Main St', '12345', 'New York', 'US');
    $invoice = new InvoiceNumeric('INV-123', Carbon::parse('2020-01-01'), $customer);
    $viewData = $invoice->invoiceTableView(true);

    $viewMock = Mockery::mock(\Illuminate\Contracts\View\View::class);
    $viewMock->shouldReceive('render')->andReturn('Rendered 1');

    View::shouldReceive('exists')->andReturn(true);
    View::shouldReceive('make')
        ->with('test-view', Mockery::type('array'), Mockery::any())
        ->andReturn($viewMock);

    $html = $viewData->html(['blade-view' => 'test-view']);

    expect($html)->toContain('Rendered');
});
