<?php

use SchenkeIo\Invoice\Support\LaravelTranslation;

it('translates using the Laravel helper', function () {
    $translator = new LaravelTranslation;
    expect($translator->translate('invoice::invoice.pos'))->toBe('Pos.');
});

it('translates with replacement', function () {
    // assuming there is a translation with replacement, or we just test it doesn't crash
    $translator = new LaravelTranslation;
    expect($translator->translate('invoice::invoice.pos', ['name' => 'John']))->toBe('Pos.');
});

it('returns the key if translation is not a string', function () {
    $translator = new LaravelTranslation;
    // __() returns the key if not found, usually.
    // If it returns an array (e.g. if the key points to a group), LaravelTranslation returns the key.
    expect($translator->translate('invoice::invoice'))->toBe('invoice::invoice');
});
