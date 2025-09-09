<?php

namespace SchenkeIo\Invoice;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class InvoiceServiceProvider extends PackageServiceProvider
{
    public function register(): void {}

    public function boot(): void {}

    public function configurePackage(Package $package): void
    {
        $package->name('invoice');
    }
}
