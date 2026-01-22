<?php

namespace Tests\Unit\Invoicing;

use SchenkeIo\Invoice\Enum\InvoiceLineType;
use SchenkeIo\Invoice\Enum\VatCategory;
use SchenkeIo\Invoice\Invoicing\LineData;
use SchenkeIo\Invoice\Money\Vat;

it('uses country specific VAT in LineData', function (string $countryCode, float $expectedVatRate) {
    $line = LineData::fromTotalNetPrice('Item', 100.00, InvoiceLineType::SalesDE, $countryCode);

    // In this package, SalesDE maps to VatCategory::Taxable which is always Standard Rate
    // Even if it's named SalesDE, the countryCode passed should override the rate lookup if it uses Vat::country($countryCode)

    $vat = Vat::country($countryCode)->standard();
    expect($vat->rate)->toBe($expectedVatRate);

    // LineData::fromTotalNetPrice calculates Gross from Net using the VAT rate
    // Gross = 100 * (1 + rate)
    expect($line->lineTotalGrossPrice->toFloat())->toBe(round(100 * (1 + $expectedVatRate), 2))
        ->and($line->lineVatAmount->toFloat())->toBe(round(100 * $expectedVatRate, 2));
})->with([
    ['DE', 0.19],
    ['AT', 0.20],
    ['FR', 0.20],
    ['CH', 0.081],
]);

it('handles reverse charge correctly in LineData', function () {
    // SalesEU maps to ReverseChargingStandard
    $line = LineData::fromTotalNetPrice('Item', 100.00, InvoiceLineType::SalesEU, 'FR');

    expect($line->invoiceLineType->vatCategory())->toBe(VatCategory::ReverseChargingStandard);
    expect($line->invoiceLineType->vatCategory()->isReverseCharge())->toBeTrue();

    // For reverse charge: Gross = Net, VAT = 0
    expect($line->lineTotalNetPrice->toFloat())->toBe(100.00)
        ->and($line->lineTotalGrossPrice->toFloat())->toBe(100.00)
        ->and($line->lineVatAmount->toFloat())->toBe(0.00);
});

it('handles reverse charge fromTotalGrossPrice correctly', function () {
    // SalesEU maps to ReverseChargingStandard
    // If a user provides a "Gross" price for a reverse charge item, it should be treated as Net
    $line = LineData::fromTotalGrossPrice('Item', 100.00, InvoiceLineType::SalesEU, 'FR');

    expect($line->lineTotalNetPrice->toFloat())->toBe(100.00)
        ->and($line->lineTotalGrossPrice->toFloat())->toBe(100.00)
        ->and($line->lineVatAmount->toFloat())->toBe(0.00);
});
