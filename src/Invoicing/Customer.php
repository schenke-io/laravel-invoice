<?php

namespace SchenkeIo\Invoice\Invoicing;

/**
 * Data transfer object for customer information.
 *
 * This class holds the basic address details of a customer, including
 * name, street address, postal code, city, and country code, which are
 * required for invoice generation and tax determination.
 */
readonly class Customer
{
    public function __construct(
        public string $name,
        public string $address,
        public string $zip,
        public string $city,
        public string $countryCode
    ) {}
}
