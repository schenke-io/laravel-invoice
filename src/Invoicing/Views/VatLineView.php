<?php

namespace SchenkeIo\Invoice\Invoicing\Views;

use SchenkeIo\Invoice\Contracts\LineViewInterface;
use SchenkeIo\Invoice\Enum\VatCategory;
use SchenkeIo\Invoice\Invoicing\LineViewBase;
use SchenkeIo\Invoice\Money\Currency;
use SchenkeIo\Invoice\Money\Vat;

/**
 * View class for rendering VAT-specific information as a table row.
 *
 * This class handles the display of VAT categories, including the
 * associated positions, description, amounts, and rates, ensuring
 * that tax breakdowns are clearly presented on the invoice.
 * It implements LineViewInterface and provides columns for:
 * - List of positions (`positions`)
 * - Tax category description (`description`)
 * - Base amount (Net or Gross) (`mainAmount`)
 * - VAT rate percentage (`vatRate`)
 * - Calculated VAT amount (`vatAmount`)
 *
 * It is typically used within a `VatTableView` to show a summary
 * of taxes applied to the invoice.
 */
readonly class VatLineView extends LineViewBase implements LineViewInterface
{
    private function __construct(
        // financial elements
        public string $positions,
        public string $description,
        public string|Currency $mainAmount,
        public string|Vat $vatRate,
        public string|Currency $vatAmount
    ) {
        parent::__construct(false);
    }

    /**
     * @param  array<int,int>  $positions
     */
    public static function lineItem(array $positions, VatCategory $vatCategory, Currency $gross, Currency $net, bool $isGross, string $countryCode = 'DE', ?\SchenkeIo\Invoice\Contracts\TranslationInterface $translator = null): self
    {
        return new self(
            positions: implode(',&nbsp;', $positions),
            description: $vatCategory->description($translator),
            mainAmount: $isGross ? $gross : $net,
            vatRate: Vat::country($countryCode)->getVat($vatCategory->vatRate()),
            vatAmount: $gross->minus($net)
        );
    }

    /**
     * key and right-aligned yes/no definitions per column
     *
     * @return array<string,bool>
     */
    public function columns(): array
    {
        return [
            'positions' => false,
            'description' => false,
            'mainAmount' => true,
            'vatRate' => true,
            'vatAmount' => true,
        ];
    }

    /**
     * get the table header information
     */
    public static function header(string $pricePrefix, ?\SchenkeIo\Invoice\Contracts\TranslationInterface $translator = null): self
    {
        if ($translator) {
            $pos = $translator->translate('invoice::invoice.pos');
            $description = $translator->translate('invoice::invoice.description');
            $amount = $translator->translate('invoice::invoice.amount');
            $vatRate = $translator->translate('invoice::invoice.vat_rate');
            $vatAmount = $translator->translate('invoice::invoice.vat_amount');
        } else {
            $pos = __('invoice::invoice.pos');
            $description = __('invoice::invoice.description');
            $amount = __('invoice::invoice.amount');
            $vatRate = __('invoice::invoice.vat_rate');
            $vatAmount = __('invoice::invoice.vat_amount');
        }

        return new self(
            positions: is_string($pos) ? $pos : 'Pos.',
            description: is_string($description) ? $description : 'Description',
            mainAmount: is_string($amount) ? $amount : 'Amount',
            vatRate: is_string($vatRate) ? $vatRate : 'VAT Rate',
            vatAmount: is_string($vatAmount) ? $vatAmount : 'VAT Amount'
        );
    }
}
