<?php

use SchenkeIo\Invoice\Contracts\TranslationInterface;
use SchenkeIo\Invoice\Enum\InvoiceLineType;
use SchenkeIo\Invoice\Invoicing\LineData;
use SchenkeIo\Invoice\Invoicing\Views\InvoiceLineView;
use SchenkeIo\Invoice\Money\Currency;

it('can create a header with a translator', function () {
    $translator = \Mockery::mock(TranslationInterface::class);
    $translator->shouldReceive('translate')->with('invoice::invoice.pos')->andReturn('Position');
    $translator->shouldReceive('translate')->with('invoice::invoice.description')->andReturn('Desc');
    $translator->shouldReceive('translate')->with('invoice::invoice.total')->andReturn('Sum');

    $view = InvoiceLineView::header('Net', $translator);

    expect($view->lineId)->toBe('Position')
        ->and($view->name)->toBe('Desc')
        ->and($view->totalPrice)->toBe('Net Sum');
});

it('can create a line item', function () {
    $lineData = LineData::fromTotalNetPrice('Test item', 1.0, InvoiceLineType::SalesDE);
    $view = InvoiceLineView::lineItem(1, $lineData, false);

    expect($view->lineId)->toBe(1)
        ->and($view->name)->toBe('Test item')
        ->and($view->totalPrice)->toBe('1,00 €');
});

it('can create a footer total', function () {
    $view = InvoiceLineView::footerTotal(new Currency(100), 'Total', true);

    expect($view->lineId)->toBe('')
        ->and($view->name)->toBe('Total')
        ->and($view->totalPrice)->toBe('1,00 €')
        ->and($view->isBold)->toBeTrue();
});

it('returns correct columns', function () {
    $view = InvoiceLineView::footerTotal(new Currency(100), 'Total', true);
    expect($view->columns())->toBe([
        'lineId' => false,
        'name' => false,
        'totalPrice' => true,
    ]);
});
