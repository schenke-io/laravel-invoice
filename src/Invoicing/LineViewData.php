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
        public null|int|string $quantity,
        public string $name,
        public ?string $singlePrice,
        public string $totalPrice,
        public bool $isBold,
        public bool $isEmpty
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
            $singlePrice, $totalPrice,
            false,
            $lineItem->itemGrossPrice->isEmpty()
        );
    }

    public static function footerTotal(Currency $amount, string $text, bool $isBold): self
    {
        return new self(null, $text, null, $amount->str(), $isBold, $amount->isEmpty());
    }

    public static function header(string $pricePrefix): self
    {
        return new self(
            'Menge',
            'Position',
            $pricePrefix.' pro Stk.',
            $pricePrefix.' Gesamt',
            true, true
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
        $cellType = $type == LineDisplayType::thead ? 'th' : 'td';
        $return .= $config['invoice-row-'.($this->isEmpty ? 'empty-' : '').$type->name];
        $return .= "\">\n";
        foreach (self::COLUMNS as $key => $alignRight) {
            $return .= "      <$cellType";
            $return .= ' class="';
            $return .= $config[$alignRight ? 'invoice-cell-right' : 'invoice-cell-left'];
            $return .= '">'.$this->{$key}."</$cellType>\n";
        }
        $return .= "    </tr>\n";

        return $return;
    }
}
