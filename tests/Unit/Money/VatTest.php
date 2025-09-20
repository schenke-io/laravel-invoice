<?php

use SchenkeIo\Invoice\Exceptions\VatException;
use SchenkeIo\Invoice\Money\Vat;

it('can convert rates to id and back', function ($rate) {
    $vat = Vat::fromRate($rate);
    $vatId = $vat->id;
    $vat2 = Vat::fromId($vatId);
    expect($vat->id)->toBe($vat2->id);
})->with([
    0.19,
    0.065,
]);

it('fails when incorrect VAT values are given', function ($rate) {
    Vat::fromRate($rate);
})->with([
    -1,
    -0.1,
    1.1,
    0.6,
    19.0,
])->throws(VatException::class);

it('can create DE Standard VAT', function () {
    $vat = Vat::deStandard();
    expect($vat->rate)->toBe(0.19)
        ->and($vat->id)->toBe('190')
        ->and($vat->name)->toBe('19%');
});

it('can create 0% VAT', function () {
    $vat = Vat::fromRate(0.00);
    expect($vat->rate)->toBe(0.00)
        ->and($vat->id)->toBe('000')
        ->and($vat->name)->toBe('0%');
});
