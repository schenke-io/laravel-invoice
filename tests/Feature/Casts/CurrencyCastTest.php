<?php

use Illuminate\Database\Eloquent\Model;
use SchenkeIo\Invoice\Casts\CurrencyCast;
use SchenkeIo\Invoice\Data\Currency;

it('converts database value to Currency object', function ($value, $result) {
    // Anonymous Dummy model to mimic a database-backed Eloquent model with a casted attribute.
    $dummy = new class extends Model
    {
        protected $table = 'dummies';

        protected $guarded = [];

        protected $casts = [
            'amount' => CurrencyCast::class,
        ];
    };

    // Simulate a raw value coming from the database (no cast applied yet)
    // setRawAttributes mimics the state after fetching from DB.
    $dummy->setRawAttributes(['amount' => $value], true);

    $casted = $dummy->getAttribute('amount');

    expect($casted)
        ->toBeInstanceOf(Currency::class)
        ->and($casted->toFloat())->toBe($result);
})->with([
    'null' => [null, 0.0],
    '0' => [0, 0.0],
    '1.23' => [1.23, 1.23],
    '123.45' => [123.45, 123.45],
    '-123.45' => [-123.45, -123.45],
    '123.456789' => [123.456789, 123.46],
]);

it('converts Currency object to float', function ($value, $result) {
    // Anonymous Dummy model to test the setter (cast to primitive for DB storage)
    $dummy = new class extends Model
    {
        protected $table = 'dummies';

        protected $guarded = [];

        protected $casts = [
            'amount' => CurrencyCast::class,
        ];
    };

    // Assigning the attribute triggers the cast's set() method, storing the DB value in attributes
    $dummy->setAttribute('amount', $value);

    // Inspect the underlying raw attribute as it would be saved to the database
    $stored = $dummy->getAttributes()['amount'] ?? null;

    expect($stored)->toBe($result);
})->with([
    'null' => [null, 0.0],
    '0' => [0, 0.0],
    '12,34 Euro' => ['12,34 Euro', 12.34],
]);
