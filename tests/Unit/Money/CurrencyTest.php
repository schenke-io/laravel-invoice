<?php

use SchenkeIo\Invoice\Money\Currency;
use SchenkeIo\Invoice\Money\MismatchedCurrencyException;
use SchenkeIo\Invoice\Money\Vat;

it('stores and receives data to Livewire based on Wireable', function () {
    // Test the Wireable interface implementation
    // toLivewire() serializes the Currency object to a float
    // fromLivewire() deserializes the float back to a Currency object

    // Create a test Currency object
    $originalCurrency = Currency::fromFloat(123.45);

    // Serialize to Livewire format
    $livewireData = $originalCurrency->toLivewire();

    // Verify the serialized data structure
    expect($livewireData)->toBeArray()
        ->and($livewireData)->toBe(['cents' => 12345, 'code' => 'EUR']);

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

it('tests fromLivewire fallback cases', function ($value, $expectedCents) {
    $currency = Currency::fromLivewire($value);
    expect($currency->centValue)->toBe($expectedCents);
})->with([
    'null value' => [null, 0],
    'int value' => [10, 1000],
    'float value' => [12.34, 1234],
    'string value' => ['12,34 €', 1234],
    'array without cents' => [['foo' => 'bar'], 0],
    'empty array' => [[], 0],
    'cents array' => [['cents' => 1234], 1234],
]);

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

it('returns the same instance when fromAny() is called with a Currency object', function () {
    $currency = Currency::fromFloat(10.0);
    expect(Currency::fromAny($currency))->toBe($currency);
});

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

it('defaults currency code to EUR', function () {
    expect(Currency::fromCents(500)->code())->toBe('EUR')
        ->and(Currency::fromFloat(10.5)->code())->toBe('EUR')
        ->and(Currency::fromAny('12,34')->code())->toBe('EUR');
});

it('accepts currency code in factory methods', function () {
    expect(Currency::fromCents(500, 'USD')->code())->toBe('USD')
        ->and(Currency::fromFloat(10.5, 'GBP')->code())->toBe('GBP')
        ->and(Currency::fromAny('12,34', 'CHF')->code())->toBe('CHF');
});

it('returns same instance when fromAny receives a Currency', function () {
    $currency = Currency::fromCents(100, 'USD');
    expect(Currency::fromAny($currency))->toBe($currency);
});

it('str() adapts symbol to currency code', function ($code, $expectedSymbol) {
    $currency = Currency::fromCents(100, $code);
    expect($currency->str())->toEndWith($expectedSymbol);
})->with([
    ['EUR', '€'],
    ['USD', '$'],
    ['GBP', '£'],
    ['CHF', 'CHF'],
    ['JPY', '¥'],
    ['XYZ', 'XYZ'],
]);

it('withCode() creates new instance with different code', function () {
    $eur = Currency::fromCents(100, 'EUR');
    $usd = $eur->withCode('USD');
    expect($usd->code())->toBe('USD')
        ->and($usd->centValue)->toBe(100)
        ->and($eur->code())->toBe('EUR');
});

it('plus() throws MismatchedCurrencyException for different codes', function () {
    Currency::fromCents(100, 'USD')->plus(Currency::fromCents(100, 'EUR'));
})->throws(MismatchedCurrencyException::class);

it('minus() throws MismatchedCurrencyException for different codes', function () {
    Currency::fromCents(100, 'USD')->minus(Currency::fromCents(100, 'EUR'));
})->throws(MismatchedCurrencyException::class);

it('plus() works for same currency code', function () {
    $result = Currency::fromCents(100, 'USD')->plus(Currency::fromCents(50, 'USD'));
    expect($result->centValue)->toBe(150)
        ->and($result->code())->toBe('USD');
});

it('formatted() returns German format with thousands separator', function ($cents, $expected) {
    expect(Currency::fromCents($cents)->formatted())->toBe($expected);
})->with([
    [123450, '1.234,50'],
    [100000000, '1.000.000,00'],
    [-123450, '-1.234,50'],
    [0, '0,00'],
    [5, '0,05'],
]);

it('formattedStr() returns formatted value with currency symbol', function ($cents, $code, $expected) {
    expect(Currency::fromCents($cents, $code)->formattedStr())->toBe($expected);
})->with([
    [123450, 'EUR', '1.234,50 €'],
    [100000000, 'EUR', '1.000.000,00 €'],
    [-123450, 'EUR', '-1.234,50 €'],
    [123450, 'USD', '1.234,50 $'],
    [123450, 'GBP', '1.234,50 £'],
    [123450, 'XYZ', '1.234,50 XYZ'],
]);

it('dividedBy() divides with half-up rounding', function ($cents, $divisor, $expectedCents) {
    expect(Currency::fromCents($cents)->dividedBy($divisor)->centValue)->toBe($expectedCents);
})->with([
    [1000, 3, 333],
    [1001, 3, 334],
    [100, 2, 50],
    [100, -2, -50],
    [-1000, 3, -333],
]);

it('dividedBy() preserves currency code', function () {
    $result = Currency::fromCents(1000, 'USD')->dividedBy(3);
    expect($result->centValue)->toBe(333)
        ->and($result->code())->toBe('USD');
});

it('dividedBy() throws DivisionByZeroError for zero divisor', function () {
    Currency::fromCents(100)->dividedBy(0);
})->throws(DivisionByZeroError::class);

it('Livewire round-trip preserves currency code', function () {
    $original = Currency::fromCents(12345, 'USD');
    $data = $original->toLivewire();
    $restored = Currency::fromLivewire($data);
    expect($restored->centValue)->toBe(12345)
        ->and($restored->code())->toBe('USD');
});

it('Legacy Livewire payload defaults to EUR', function () {
    $restored = Currency::fromLivewire(['cents' => 123]);
    expect($restored->centValue)->toBe(123)
        ->and($restored->code())->toBe('EUR');
});

it('VAT methods preserve currency code', function () {
    $vat = Vat::fromRate(0.19);
    $usd = Currency::fromCents(11900, 'USD');
    expect($usd->vatFromGross($vat)->code())->toBe('USD')
        ->and($usd->fromGrossToNet($vat)->code())->toBe('USD')
        ->and($usd->vatFromNet($vat)->code())->toBe('USD')
        ->and($usd->fromNetToGross($vat)->code())->toBe('USD');
});

it('times() preserves currency code', function () {
    $result = Currency::fromCents(100, 'GBP')->times(1.5);
    expect($result->centValue)->toBe(150)
        ->and($result->code())->toBe('GBP');
});
