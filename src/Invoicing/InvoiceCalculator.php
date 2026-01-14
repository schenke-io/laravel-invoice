<?php

namespace SchenkeIo\Invoice\Invoicing;

use SchenkeIo\Invoice\Money\Currency;

/**
 * Calculator for invoice totals and tax categorization.
 *
 * This class handles the financial logic of an invoice, including summing
 * up line items, grouping them by VAT category, and calculating total
 * gross and net prices as well as total weight.
 */
class InvoiceCalculator
{
    /**
     * Grouped line items by their vat category ID.
     *
     * @var array<int, array<int, LineData>>
     */
    protected array $vatCategories = [];

    /**
     * Total weight of all items in grams.
     */
    protected int $totalWeightGrams = 0;

    /**
     * Sum of all gross prices.
     */
    protected Currency $totalGrossPrice;

    /**
     * Sum of all net prices.
     */
    protected Currency $totalNetPrice;

    /**
     * Gross totals indexed by vat category ID.
     *
     * @var array<int, Currency>
     */
    protected array $categoryGrossSum = [];

    /**
     * Net totals indexed by vat category ID.
     *
     * @var array<int, Currency>
     */
    protected array $categoryNetSum = [];

    /**
     * All line items added to the invoice.
     *
     * @var LineData[]
     */
    protected array $lineItems = [];

    /**
     * The current line item position.
     */
    private int $position = 0;

    /**
     * InvoiceCalculator constructor.
     */
    public function __construct()
    {
        $this->totalGrossPrice = Currency::fromCents(0);
        $this->totalNetPrice = Currency::fromCents(0);
    }

    /**
     * Adds a line item and updates all totals and vat categories.
     */
    public function addLine(LineData $lineData): void
    {
        $this->position++;
        $this->lineItems[$this->position] = $lineData;

        $vatCategoryId = $lineData->invoiceLineType->vatCategory()->value;

        $net = $lineData->lineTotalNetPrice;
        $gross = $lineData->lineTotalGrossPrice;

        $this->vatCategories[$vatCategoryId][$this->position] = $lineData;

        $this->categoryGrossSum[$vatCategoryId] = ($this->categoryGrossSum[$vatCategoryId] ?? Currency::fromCents(0))->plus($gross);
        $this->categoryNetSum[$vatCategoryId] = ($this->categoryNetSum[$vatCategoryId] ?? Currency::fromCents(0))->plus($net);

        $this->totalGrossPrice = $this->totalGrossPrice->plus($gross);
        $this->totalNetPrice = $this->totalNetPrice->plus($net);
    }

    /**
     * Adds weight to the total weight of the invoice.
     */
    public function addWeight(int $grams): void
    {
        $this->totalWeightGrams += $grams;
    }

    public function getTotalGrossPrice(): Currency
    {
        return $this->totalGrossPrice;
    }

    public function getTotalNetPrice(): Currency
    {
        return $this->totalNetPrice;
    }

    public function getTotalWeightGrams(): int
    {
        return $this->totalWeightGrams;
    }

    /**
     * @return LineData[]
     */
    public function getLineItems(): array
    {
        return $this->lineItems;
    }

    /**
     * @return array<int, array<int, LineData>>
     */
    public function getVatCategories(): array
    {
        return $this->vatCategories;
    }

    /**
     * @return array<int, Currency>
     */
    public function getCategoryGrossSum(): array
    {
        return $this->categoryGrossSum;
    }

    /**
     * @return array<int, Currency>
     */
    public function getCategoryNetSum(): array
    {
        return $this->categoryNetSum;
    }
}
