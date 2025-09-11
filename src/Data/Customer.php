<?php

namespace SchenkeIo\Invoice\Data;

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
