<?php

namespace SchenkeIo\Invoice\Invoicing\Views;

use SchenkeIo\Invoice\Contracts\LineViewInterface;
use SchenkeIo\Invoice\Invoicing\LineData;
use SchenkeIo\Invoice\Invoicing\LineViewBase;
use SchenkeIo\Invoice\Money\Currency;

/**
 * View class for rendering a single line of an invoice table.
 *
 * This class handles the display of standard line items and footer
 * totals, providing methods to create header, body, and footer rows
 * with appropriate formatting and translations. It implements the
 * LineViewInterface and defines columns for:
 * - Position ID (`lineId`)
 * - Description (`name`)
 * - Total Price (`totalPrice`)
 *
 * Each instance of this class represents a specific row in the main
 * invoice table, carrying the formatted data and row-level styling
 * information.
 */
final readonly class InvoiceLineView extends LineViewBase implements LineViewInterface
{
    private function __construct(
        // financial elements
        public int|string $lineId,
        public string $name,
        public string $totalPrice,
        public bool $isEmpty,
        bool $isBold
    ) {

        parent::__construct($isBold);
    }

    public static function lineItem(int $lineId, LineData $lineItem, bool $isGross = true, ?\SchenkeIo\Invoice\Contracts\TranslationInterface $translator = null): self
    {
        if ($isGross) {
            $totalPrice = $lineItem->lineTotalGrossPrice->str();
        } else {
            $totalPrice = $lineItem->lineTotalNetPrice->str();
        }

        return new self($lineId, $lineItem->name, $totalPrice, $lineItem->lineTotalGrossPrice->isEmpty(), false);
    }

    public static function footerTotal(Currency $amount, string $text, bool $isBold, ?\SchenkeIo\Invoice\Contracts\TranslationInterface $translator = null): self
    {
        return new self('', $text, $amount->str(), $amount->isEmpty(), $isBold);
    }

    public static function header(string $pricePrefix, ?\SchenkeIo\Invoice\Contracts\TranslationInterface $translator = null): self
    {
        if ($translator) {
            $pos = $translator->translate('invoice::invoice.pos');
            $description = $translator->translate('invoice::invoice.description');
            $total = $translator->translate('invoice::invoice.total');
        } else {
            $pos = __('invoice::invoice.pos');
            $description = __('invoice::invoice.description');
            $total = __('invoice::invoice.total');
        }

        return new self(
            is_string($pos) ? $pos : 'Pos.',
            is_string($description) ? $description : 'Description',
            $pricePrefix.' '.(is_string($total) ? $total : 'Total'),
            false,
            true
        );
    }

    /**
     * key and custom definitions per column
     *
     * @return array<string,mixed>
     */
    public function columns(): array
    {
        return [
            // key => align right
            'lineId' => false,
            'name' => false,
            'totalPrice' => true,
        ];
    }
}
