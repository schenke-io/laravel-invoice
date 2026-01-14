<?php

namespace SchenkeIo\Invoice\Invoicing;

use SchenkeIo\Invoice\Contracts\LineViewInterface;
use SchenkeIo\Invoice\Enum\LineDisplayType;

/**
 * Base class for rendering invoice lines as HTML.
 *
 * This abstract class provides the common logic for generating HTML table
 * rows for different sections of an invoice (header, body, footer),
 * using configuration for CSS classes and alignment.
 */
abstract readonly class LineViewBase implements LineViewInterface
{
    public function __construct(public bool $isBold) {}

    /**
     * @param  array<string,string|null>  $config
     */
    public function html(array $config, LineDisplayType $type): string
    {
        $rowClass = $config['invoice-row-'.$type->name] ?? '';
        $cellType = $type == LineDisplayType::thead ? 'th' : 'td';
        $boldClass = $this->isBold ? ($config['invoice-cell-bold'] ?? '') : '';

        $cellsHtml = '';
        foreach ($this->columns() as $key => $alignRight) {
            $alignClass = $config[$alignRight ? 'invoice-cell-right' : 'invoice-cell-left'] ?? '';
            $classAttr = trim("$alignClass $boldClass");
            $classAttr = $classAttr ? " class=\"$classAttr\"" : '';

            $cellsHtml .= sprintf(
                "      <%s%s>%s</%s>\n",
                $cellType,
                $classAttr,
                $this->{$key},
                $cellType
            );
        }

        return sprintf(
            "    <tr class=\"%s\">\n%s    </tr>\n",
            (string) $rowClass,
            $cellsHtml
        );
    }
}
