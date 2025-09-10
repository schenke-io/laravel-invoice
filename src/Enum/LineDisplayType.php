<?php

namespace SchenkeIo\Invoice\Enum;

enum LineDisplayType
{
    case InvoiceItem;
    case FooterTotal;
    case FooterVat;

}
