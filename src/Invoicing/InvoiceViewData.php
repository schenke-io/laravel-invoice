<?php

namespace SchenkeIo\Invoice\Invoicing;

use Carbon\Carbon;
use SchenkeIo\Invoice\Enum\LineDisplayType;
use SchenkeIo\Invoice\Money\Currency;

class InvoiceViewData
{
    /**
     * key - class pairs for various situations
     *
     * @var array<string,string>
     */
    private array $defaults = [
        'table-class' => 'table-invoice',
        'invoice-row-thead' => '',
        'invoice-row-tbody' => '',
        'invoice-row-tfoot' => '',
        'invoice-row-empty' => 'empty-line',
        'invoice-cell-right' => 'cell-right',
        'invoice-cell-left' => 'cell-left',
        'invoice-cell-bold' => 'cell-bold',
    ];

    public string $invoiceId = '';

    public Carbon $invoiceDate;

    public int $totalGramm = 0;

    public string $totalWeightText = '';

    public Customer $customer;

    public Currency $totalGrossPrice;

    public LineViewData $header;

    /**
     * @var LineViewData[]
     */
    public array $body = [];

    /**
     * @var LineViewData[]
     */
    public array $footer = [];

    /**
     * @param  array<string,string>  $config
     */
    public function html(array $config = []): string
    {
        // First, filter the user input to only include keys that exist in the defaults.
        $validConfig = array_intersect_key($config, $this->defaults);

        // Second, merge the defaults with the now-safe and filtered user input.
        // The values from $validConfig will overwrite the values in $defaults.
        $finalConfig = array_merge($this->defaults, $validConfig);

        $html = "\n<table class=\"{$finalConfig['table-class']}\">\n  <thead>\n";
        $html .= $this->header->html($finalConfig, LineDisplayType::thead);
        $html .= "  </thead>\n  <tbody>\n";
        foreach ($this->body as $line) {
            $html .= $line->html($finalConfig, LineDisplayType::tbody);
        }
        $html .= "  </tbody>\n  <tfoot>\n";
        // this can be styled a line separating all items from the summary
        $html .= sprintf("    <tr class='%s'><td colspan='%d'></td></tr>\n",
            $config['invoice-row-empty'],
            count(LineViewData::COLUMNS),
        );
        foreach ($this->footer as $line) {
            $html .= $line->html($finalConfig, LineDisplayType::tfoot);
        }
        $html .= "  </tfoot>\n</table>\n\n";

        return $html;
    }
}
