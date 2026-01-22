<?php

namespace Tests\Unit\Enum;

use SchenkeIo\Invoice\Contracts\TranslationInterface;
use SchenkeIo\Invoice\Enum\VatCategory;
use SchenkeIo\Invoice\Enum\VatRate;

it('has descriptions for all cases using a translator', function () {
    $translator = \Mockery::mock(TranslationInterface::class);
    $translator->shouldReceive('translate')->andReturn('translated');

    foreach (VatCategory::cases() as $case) {
        expect($case->description($translator))->toBe('translated');
    }
});

it('has vat rates for all cases', function () {
    foreach (VatCategory::cases() as $case) {
        expect($case->vatRate())->toBeInstanceOf(VatRate::class);
    }
});

it('correctly identifies if it has VAT', function () {
    expect(VatCategory::Taxable->hasVat())->toBeTrue()
        ->and(VatCategory::NonTaxableDamages->hasVat())->toBeFalse()
        ->and(VatCategory::Deposits->hasVat())->toBeFalse()
        ->and(VatCategory::ReverseChargingStandard->hasVat())->toBeFalse();
});
