<?php

namespace Tests\Unit\Invoicing\Views;

use SchenkeIo\Invoice\Contracts\TranslationInterface;
use SchenkeIo\Invoice\Enum\VatCategory;
use SchenkeIo\Invoice\Invoicing\Views\VatLineView;
use SchenkeIo\Invoice\Money\Currency;

it('can create a vat line item using a translator', function () {
    $positions = [1, 2];
    $vatCategory = VatCategory::Taxable;
    $gross = Currency::fromFloat(119.00);
    $net = Currency::fromFloat(100.00);

    $translator = \Mockery::mock(TranslationInterface::class);
    $translator->shouldReceive('translate')->andReturn('translated');

    $view = VatLineView::lineItem($positions, $vatCategory, $gross, $net, true, $translator);

    expect($view->positions)->toBe('1,&nbsp;2')
        ->and($view->description)->toBe('translated')
        ->and($view->mainAmount)->toBe($gross)
        ->and($view->vatAmount->toFloat())->toBe(19.00);
});

it('can create a header using a translator', function () {
    $translator = \Mockery::mock(TranslationInterface::class);
    $translator->shouldReceive('translate')->andReturn('translated');

    $view = VatLineView::header('any', $translator);

    expect($view->positions)->toBe('translated')
        ->and($view->description)->toBe('translated')
        ->and($view->mainAmount)->toBe('translated');
});

it('returns correct columns', function () {
    $translator = \Mockery::mock(TranslationInterface::class);
    $translator->shouldReceive('translate')->andReturn('translated');

    $view = VatLineView::header('any', $translator);
    $columns = $view->columns();

    expect($columns)->toBeArray()
        ->and($columns)->toHaveKey('positions')
        ->and($columns['positions'])->toBeFalse()
        ->and($columns['mainAmount'])->toBeTrue();
});
