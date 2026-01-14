<?php

namespace SchenkeIo\Invoice;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * Service provider for the Invoice package.
 *
 * This class handles the registration and bootstrapping of the package's
 * services, including translations and other Laravel-specific integrations.
 */
class InvoiceServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('invoice')
            ->hasTranslations();
    }
}
