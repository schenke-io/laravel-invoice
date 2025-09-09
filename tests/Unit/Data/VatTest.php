<?php

use SchenkeIo\Invoice\Data\Vat;
use SchenkeIo\Invoice\Exceptions\VatException;

it('can convert rates to id and back', function ($rate) {
    $vat = new Vat($rate);
    $vatId = $vat->id;
    $vat2 = Vat::fromId($vatId);
    expect($vat->id)->toBe($vat2->id);
})->with([
    0.19,
    0.065,
]);

it('fails when incorrect VAT values are given', function ($rate) {
    new Vat($rate);
})->with([
    -1,
    -0.1,
    1.1,
    0.6,
    19.0,
])->throws(VatException::class);
