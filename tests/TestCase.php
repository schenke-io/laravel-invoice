<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use SchenkeIo\Invoice\InvoiceServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            InvoiceServiceProvider::class,
        ];
    }
}
