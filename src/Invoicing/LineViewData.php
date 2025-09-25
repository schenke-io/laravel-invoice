<?php

namespace SchenkeIo\Invoice\Invoicing;

use SchenkeIo\Invoice\Enum\LineDisplayType;
use SchenkeIo\Invoice\Money\Currency;

final readonly class LineViewData
{
    public const array COLUMNS = [
        // key => align right
        'quantity' => false,
        'name' => false,
        'singlePrice' => true,
        'totalPrice' => true,
    ];

    private function __construct(
        // financial elements
        public null|int|string $quantity,
        public string $name,
        public ?string $singlePrice,
        public string $totalPrice,
        // design elements
        public bool $isBold = false,
        public bool $isEmpty = false
    ) {}

    public static function lineItem(LineData $lineItem, bool $isGross = true): self
    {
        if ($isGross) {
            $singlePrice = $lineItem->itemGrossPrice->str();
            $totalPrice = $lineItem->lineTotalGrossPrice->str();
        } else {
            $singlePrice = $lineItem->itemNetPrice->str();
            $totalPrice = $lineItem->lineTotalNetPrice->str();
        }

        return new self(
            $lineItem->quantity,
            $lineItem->name,
            $singlePrice,
            $totalPrice
        );
    }

    public static function footerTotal(Currency $amount, string $text, bool $isBold): self
    {
        return new self(null, $text, null, $amount->str(), $isBold);
    }

    public static function header(string $pricePrefix): self
    {
        return new self(
            'Menge',
            'Position',
            $pricePrefix.' pro Stk.',
            $pricePrefix.' Gesamt',
            true
        );
    }

    public static function emptyLine(): self
    {
        return new self(null, '', null, '', false, true);
    }

    /**
     * @param  array<string,string>  $config
     */
    public function html(array $config, LineDisplayType $type): string
    {
        $return = '    <tr class="';
        $return .= $config['invoice-row-'.$type->name];
        if ($this->isEmpty) {
            $return .= ' '.$config['invoice-row-empty'];
        }
        $return .= "\">\n";
        $cellType = $type == LineDisplayType::thead ? 'th' : 'td';
        foreach (self::COLUMNS as $key => $alignRight) {
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
