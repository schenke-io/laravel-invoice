<?php

it('can load translations from the package via Laravel', function () {
    expect(__('invoice::invoice.pos'))->toBe('Pos.');
});

it('can load German translations via Laravel', function () {
    App::setLocale('de');
    expect(__('invoice::invoice.description'))->toBe('Beschreibung');
    App::setLocale('en');
});
