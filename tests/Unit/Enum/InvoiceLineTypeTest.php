<?php

use SchenkeIo\Invoice\Enum\InvoiceLineType;
use SchenkeIo\Invoice\Enum\VatCategory;
use SchenkeIo\Invoice\Enum\VatRate;

it('maps to correct tax category and vat rate for each case', function () {
    foreach (InvoiceLineType::cases() as $case) {
        expect($case->vatCategory())->toBeInstanceOf(VatCategory::class)
            ->and($case->vatRate())->toBeInstanceOf(VatRate::class);
    }
});

it('specifically tests some key mappings', function () {
    expect(InvoiceLineType::SalesDE->vatCategory())->toBe(VatCategory::Taxable)
        ->and(InvoiceLineType::SaleBooksDE->vatCategory())->toBe(VatCategory::TaxableReducedVatRate)
        ->and(InvoiceLineType::Deposit->vatCategory())->toBe(VatCategory::Deposits)
        ->and(InvoiceLineType::Damage->vatCategory())->toBe(VatCategory::NonTaxableDamages)
        ->and(InvoiceLineType::Fine->vatCategory())->toBe(VatCategory::NonTaxableTransitory)
        ->and(InvoiceLineType::SalesEU->vatCategory())->toBe(VatCategory::ReverseChargingStandard);
});
