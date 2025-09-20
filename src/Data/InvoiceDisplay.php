<?php

namespace SchenkeIo\Invoice\Data;

use Carbon\Carbon;

class InvoiceDisplay
{
    public string $invoiceId = '';

    public Carbon $invoiceDate;

    public int $totalGramm = 0;

    public string $totalWeightText = '';

    public Customer $customer;

    public Currency $totalGrossPrice;

    public LineDisplay $header;

    /**
     * @var LineDisplay[]
     */
    public array $body = [];

    /**
     * @var LineDisplay[]
     */
    public array $footer = [];
}
