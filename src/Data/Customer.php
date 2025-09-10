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

    /**
     * @return array<string,string>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'address' => $this->address,
            'zip' => $this->zip,
            'city' => $this->city,
            'countryCode' => $this->countryCode,
        ];
    }
}
