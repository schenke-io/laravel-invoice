<?php

use SchenkeIo\Invoice\InvoiceServiceProvider;
use Spatie\LaravelPackageTools\Package;

it('verifies the service provider', function () {
    $package = new Package;
    $provider = new InvoiceServiceProvider(null);
    $provider->boot();
    $provider->register();
    $provider->configurePackage($package);
    expect($package->name)->toBe('invoice');
});
