<?php

use SchenkeIo\Invoice\Enum\VatRate;
use SchenkeIo\Invoice\Exceptions\VatException;
use SchenkeIo\Invoice\Money\Country;
use SchenkeIo\Invoice\Money\Vat;

it('can convert rates to id and back', function ($rate) {
    $vat = Vat::fromRate($rate);
    $vatId = $vat->id;
    $vat2 = Vat::fromId($vatId);
    expect($vat->id)->toBe($vat2->id)
        ->and((string) $vat)->toBe($vat->name);
})->with([
    0.19,
    0.065,
    0.0,
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

it('can create VAT for various countries and types', function ($countryCode, $type, $expectedRate, $expectedId, $expectedName) {
    $country = Vat::country($countryCode);
    $vat = $country->getVat($type);
    expect($vat->rate)->toBe($expectedRate)
        ->and($vat->id)->toBe($expectedId)
        ->and($vat->name)->toBe($expectedName);
})->with([
    ['DE', VatRate::Standard, 0.19, '190', '19%'],
    ['FR', VatRate::Standard, 0.20, '200', '20%'],
    ['FR', VatRate::Reduced, 0.10, '100', '10%'],
    ['DE', VatRate::Reduced, 0.07, '070', '7%'],
    ['DE', VatRate::None, 0.0, '000', '0%'],
]);

it('can access VAT via country() and static methods', function ($method, $isoCode) {
    $country = Vat::$method();
    expect($country)->toBeInstanceOf(Country::class)
        ->and($country->isoCode)->toBe($isoCode);

    expect(Vat::country($method)->isoCode)->toBe($isoCode);
    expect(Vat::country($isoCode)->isoCode)->toBe($isoCode);
})->with([
    ['de', 'DE'],
    ['fr', 'FR'],
    ['ch', 'CH'],
]);

it('handles country related exceptions', function ($input) {
    Vat::country($input);
})->with([
    'xx',
    'de-de',
    'germany',
    'france!',
])->throws(VatException::class);

it('handles invalid country code in constructor', function () {
    new Country('XX');
})->throws(VatException::class);

it('can access interface methods on country classes', function ($countryCode, $expectedUrlPart) {
    $country = Vat::country($countryCode);
    expect($country->referenceUrl())->toContain($expectedUrlPart)
        ->and(filter_var($country->referenceUrl(), FILTER_VALIDATE_URL))->not->toBeFalse()
        ->and($country->getStatus())->toBeString()
        ->and($country->standard())->toBeInstanceOf(Vat::class)
        ->and($country->reduced())->toBeInstanceOf(Vat::class)
        ->and($country->none())->toBeInstanceOf(Vat::class);
})->with([
    ['DE', 'vat-rules-rates/germany_en'],
    ['CH', 'admin.ch'],
]);

it('returns correct status for countries', function () {
    // We create a mock-like class to test the case where standard and reduced rates are equal
    $country = new class('DE') extends Country
    {
        public function standard(): Vat
        {
            return Vat::fromId('190');
        }

        public function reduced(): Vat
        {
            return Vat::fromId('190');
        }
    };
    expect($country->getStatus())->toBe('Standard rate 19%, No reduced rates');

    $country2 = new Country('DK');
    expect($country2->getStatus())->toBe('Standard rate 25%, Reduced rate 0%');
});

it('verifies all countries have valid data and reference URLs', function () {
    foreach (Country::DATA as $isoCode => $data) {
        $country = new Country($isoCode);
        expect(filter_var($country->referenceUrl(), FILTER_VALIDATE_URL))->not->toBeFalse();
        expect(Vat::$isoCode())->toBeInstanceOf(Country::class);
    }
});
