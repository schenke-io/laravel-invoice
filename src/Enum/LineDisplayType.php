<?php

namespace SchenkeIo\Invoice\Enum;

/**
 * Enum defining the display sections for invoice lines in a table.
 *
 * It corresponds to the HTML table sections: header (thead), body (tbody),
 * and footer (tfoot), helping to organize the rendering of invoice data.
 * These constants are used to determine which configuration keys for
 * row and cell styling should be applied during the rendering process,
 * ensuring consistent visual structure across different table types.
 */
enum LineDisplayType
{
    case thead;
    case tbody;
    case tfoot;

}
