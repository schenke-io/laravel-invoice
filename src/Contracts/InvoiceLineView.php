<?php

namespace SchenkeIo\Invoice\Contracts;

use SchenkeIo\Invoice\Enum\LineDisplayType;

interface InvoiceLineView
{
    /**
     * key and right-aligned yes/no definitions per column
     *
     * @return array<string,bool>
     */
    public function columns(): array;

    /**
     * @param  array<string,string>  $config
     */
    public function html(array $config, LineDisplayType $type): string;
}
