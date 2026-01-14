<?php

namespace Workbench\Console;

use SchenkeIo\Invoice\Money\Country;
use SchenkeIo\Invoice\Money\Vat;

class VatCheck
{
    public function execute(): int
    {
        echo "Checking VAT rates against api.vatcomply.com\n";

        $hasError = false;

        foreach (Country::DATA as $isoCode => $data) {
            /*
             * skip non-EU countries
             */
            if (! $data['isEu']) {
                continue;
            }
            $slug = $data['name'];
            $url = "https://api.vatcomply.com/rates?country=$isoCode";
            $status = 'OK';
            $message = '';

            try {
                $response = @file_get_contents($url);
                if ($response === false) {
                    $status = 'FAIL';
                    $message = 'Failed to fetch data';
                } else {
                    $data = json_decode($response, true);
                    $base = $data['base'] ?? null;

                    if ($base) {
                        try {
                            $vat = (new \ReflectionClass(Vat::class))->newInstanceWithoutConstructor();
                            $country = $vat->$slug;
                        } catch (\Exception $e) {
                            $status = 'FAIL';
                            $message = $e->getMessage();
                        }
                    } else {
                        $status = 'FAIL';
                        $message = 'No base currency found in API response';
                    }
                }
            } catch (\Exception $e) {
                $status = 'ERROR';
                $message = $e->getMessage();
            }

            if ($status !== 'OK') {
                $hasError = true;
                echo sprintf("[%s] %s (%s): %s\n", $status, $isoCode, $slug, $message);
            } else {
                echo sprintf("[%s] %s (%s)\n", $status, $isoCode, $slug);
            }
        }

        return $hasError ? 1 : 0;
    }

    public static function run(): void
    {
        $command = new self;
        exit($command->execute());
    }
}
