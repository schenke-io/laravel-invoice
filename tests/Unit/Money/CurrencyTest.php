<?php

use SchenkeIo\Invoice\Money\Currency;
use SchenkeIo\Invoice\Money\Vat;

it('stores and receives data to Livewire based on Wireable', function () {
    // Test the Wireable interface implementation
    // toLivewire() serializes the Currency object to an array
    // fromLivewire() deserializes the array back to a Currency object

    // Create a test Currency object
    $originalCurrency = Currency::fromFloat(123.45);

    // Serialize to Livewire format
    $livewireData = $originalCurrency->toLivewire();

    // Verify the serialized data structure
    expect($livewireData)->toBeArray()
        ->and($livewireData)->toHaveKey('centValue')
        ->and($livewireData['centValue'])->toBe(12345);

    // Deserialize from Livewire format
    $deserializedCurrency = Currency::fromLivewire($livewireData);

    // Verify the deserialized object matches the original
    expect($deserializedCurrency)->toBeInstanceOf(Currency::class)
        ->and($deserializedCurrency->toFloat())->toBe(123.45)
        ->and($deserializedCurrency->centValue)->toBe(12345)
        ->and((string) $deserializedCurrency)->toBe('123,45');

    // Test with zero value
    $zeroCurrency = Currency::fromFloat(0);
    $zeroLivewireData = $zeroCurrency->toLivewire();
    $zeroDeserialized = Currency::fromLivewire($zeroLivewireData);
    expect($zeroDeserialized->toFloat())->toBe(0.0);

    // Test with negative value
    $negativeCurrency = Currency::fromFloat(-99.99);
    $negativeLivewireData = $negativeCurrency->toLivewire();
    $negativeDeserialized = Currency::fromLivewire($negativeLivewireData);
    expect($negativeDeserialized->toFloat())->toBe(-99.99);
});

it('can calculate VAT net and gross', function () {
    $net = 100.0;
    $tax = 19.0;
    $gross = 119.0;
    $vat = Vat::fromRate($tax / 100);
    expect(Currency::fromFloat($net)->vatFromNet($vat)->toFloat())->toBe($tax)
        ->and(Currency::fromFloat($gross)->vatFromGross($vat)->toFloat())->toBe($tax);
});

it('can create and use Currency', function () {
    expect(''.Currency::fromAny(0))->toBe('0,00');
});

it('can make currency from any value', function ($value, $result) {
    expect(Currency::fromAny($value)->toFloat())->toBe($result)
        ->and(Currency::fromAny($value)->str())->toEndWith('€');

})->with([
    '1.23' => [1.23, 1.23],
    '123.45' => [123.45, 123.45],
    '-123.45' => [-123.45, -123.45],
    '123.456789' => [123.456789, 123.46],
    'text Euro 1' => ['12,34 €', 12.34],
    'text Euro 2' => ['12,34Euro', 12.34],
    'text Euro 3' => ['12.345,67 E', 12345.67],
    'text Euro 4' => ['- 12.345,67 Euro', -12345.67],

    'text USD 1' => ['12.34 USD', 12.34],

    'just text 1' => ['nothing', 0.0],
    'just text 2' => ['a,b and c', 0.0],
    'just text 3' => ['a-b. C+', 0.0],

    'x1' => ['1,12', 1.12],
]);

it('can create Currency from cents', function ($cents, $expected) {
    // Create a Currency object from cents
    // We need to be careful with floating-point precision
    // Using string conversion to ensure exact decimal representation
    $value = $cents / 100;
    $valueStr = number_format($value, 2, '.', '');
    $currency = Currency::fromFloat((float) $valueStr);
    $centsBack = ''.$currency;

    expect($centsBack)->toBe($expected);
})->with([
    'case 111' => [111, '1,11'],
    'case 112' => [112, '1,12'],
    'case 113' => [113, '1,13'],
    'case 114' => [114, '1,14'],
    'case 115' => [115, '1,15'],
    'case 116' => [116, '1,16'],
    'case 117' => [117, '1,17'],
    'case 123' => [123, '1,23'],
    'case -123' => [-123, '-1,23'],
]);

it('can subtract currencies', function ($cents1, $cents2, $expected) {
    expect(Currency::fromCents($cents1)->minus(Currency::fromCents($cents2))->centValue)
        ->toBe($expected);
})
    ->with([
        [100, 50, 50],
    ]);

it('can see if it is empty', function ($cents, bool $isEmpty) {
    $c = Currency::fromCents($cents);
    expect($c->isEmpty())->toBe($isEmpty);
})->with([
    [0, true],
    [-1, false],
    [1, false],
    [1000000000, false],
]);
