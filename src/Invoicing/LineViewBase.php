<?php

namespace SchenkeIo\Invoice\Invoicing;

use SchenkeIo\Invoice\Contracts\InvoiceLineView;
use SchenkeIo\Invoice\Enum\LineDisplayType;

abstract readonly class LineViewBase implements InvoiceLineView
{
    public function __construct(public bool $isBold) {}

    /**
     * @param  array<string,string>  $config
     */
    public function html(array $config, LineDisplayType $type): string
    {
        $return = '    <tr class="';
        $return .= $config['invoice-row-'.$type->name];
        $return .= "\">\n";
        $cellType = $type == LineDisplayType::thead ? 'th' : 'td';
        foreach ($this->columns() as $key => $alignRight) {
            $return .= "      <$cellType";
            $return .= ' class="';
            $return .= $config[$alignRight ? 'invoice-cell-right' : 'invoice-cell-left'];
            if ($this->isBold) {
                $return .= ' '.$config['invoice-cell-bold'];
            }
            $return .= '">'.$this->{$key}."</$cellType>\n";
        }
        $return .= "    </tr>\n";

        return $return;
    }
}
