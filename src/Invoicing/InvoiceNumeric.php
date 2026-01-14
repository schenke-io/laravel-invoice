<?php

namespace SchenkeIo\Invoice\Invoicing;

use Carbon\Carbon;
use SchenkeIo\Invoice\Enum\VatCategory;
use SchenkeIo\Invoice\Invoicing\Views\InvoiceLineView;
use SchenkeIo\Invoice\Invoicing\Views\InvoiceTableView;
use SchenkeIo\Invoice\Invoicing\Views\VatLineView;
use SchenkeIo\Invoice\Invoicing\Views\VatTableView;
use SchenkeIo\Invoice\Money\Currency;
use SchenkeIo\Invoice\Money\Vat;

/**
 * Main class for managing invoice data and calculations.
 *
 * This class acts as a facade for accumulating invoice lines, calculating
 * totals (gross, net, weight), and preparing data for view rendering. It
 * uses InvoiceCalculator for the heavy lifting of financial calculations.
 */
class InvoiceNumeric
{
    protected InvoiceCalculator $calculator;

    public function __construct(
        public readonly string $invoiceId,
        public readonly Carbon $invoiceDate,
        public readonly Customer $customer)
    {
        $this->calculator = new InvoiceCalculator;
    }

    public function getTotalGrossPrice(): Currency
    {
        return $this->calculator->getTotalGrossPrice();
    }

    public function getTotalNetPrice(): Currency
    {
        return $this->calculator->getTotalNetPrice();
    }

    /**
     * take the weight in grams and add it to the total weight
     */
    public function addWeight(int $grams): void
    {
        $this->calculator->addWeight($grams);
    }

    /**
     * add the lines with automatic positions
     */
    public function addLine(LineData $lineData): void
    {
        $this->calculator->addLine($lineData);
    }

    /**
     * show pay me information
     */
    public function payMe(): bool
    {
        return $this->calculator->getTotalGrossPrice()->centValue > 0;
    }

    /**
     * the total is zero
     */
    public function isEmpty(): bool
    {
        return $this->calculator->getTotalGrossPrice()->isEmpty();
    }

    /**
     * Prepare data for a Blade template or raw HTML rendering.
     *
     * This method converts the internal state of the invoice and its
     * calculator into a DTO (InvoiceTableView) that can be easily
     * rendered. It handles the grouping of lines into header,
     * body, and footer sections.
     *
     * @param bool $isGrossInvoice Whether the invoice should display gross or net prices.
     */
    public function invoiceTableView(bool $isGrossInvoice): InvoiceTableView
    {
        $view = new InvoiceTableView;
        $view->invoiceId = $this->invoiceId;
        $view->invoiceDate = $this->invoiceDate;
        $view->totalWeightGrams = $this->calculator->getTotalWeightGrams();
        $view->totalWeightText = $this->formatWeight($this->calculator->getTotalWeightGrams());
        $view->customer = $this->customer;
        $view->totalGrossPrice = $this->calculator->getTotalGrossPrice();

        $this->buildHeader($view, $isGrossInvoice);
        $this->buildBody($view, $isGrossInvoice);
        $this->buildFooter($view, $isGrossInvoice);

        return $view;
    }

    private function formatWeight(int $grams): string
    {
        return $grams > 1000 ? number_format($grams / 1000, 1).' kg' : $grams.' g';
    }

    private function buildHeader(InvoiceTableView $view, bool $isGrossInvoice): void
    {
        $pricePrefix = $isGrossInvoice ? 'Preis' : 'Nettopreis';
        $view->header = InvoiceLineView::header($pricePrefix);
    }

    private function buildBody(InvoiceTableView $view, bool $isGrossInvoice): void
    {
        foreach ($this->calculator->getLineItems() as $position => $lineItem) {
            $view->body[] = InvoiceLineView::lineItem($position, $lineItem, $isGrossInvoice);
        }
    }

    private function buildFooter(InvoiceTableView $view, bool $isGrossInvoice): void
    {
        $vatPrefix = $isGrossInvoice ? 'darin enthalten ' : 'zzgl. ';
        $bruttoLine = InvoiceLineView::footerTotal(
            $this->calculator->getTotalGrossPrice(), 'Gesamtbetrag (Brutto)', true
        );

        if ($isGrossInvoice) {
            $view->footer[] = $bruttoLine;
        } else {
            $view->footer[] = InvoiceLineView::footerTotal(
                $this->calculator->getTotalNetPrice(), 'Summe Netto', true
            );
        }

        $vatText = $this->calculateVatSection($view, $vatPrefix, $isGrossInvoice);

        $vat = $this->calculator->getTotalGrossPrice()->minus($this->calculator->getTotalNetPrice());
        $view->footer[] = InvoiceLineView::footerTotal($vat, $vatText, false);

        if (! $isGrossInvoice) {
            $view->footer[] = $bruttoLine;
        }
    }

    /**
     * Determine how the VAT section should be displayed.
     *
     * If there's only one VAT category, it returns a simple descriptive string.
     * If there are multiple VAT categories, it prepares a VatTableView for
     * detailed breakdown and returns the prefix with the table's HTML.
     *
     * @param InvoiceTableView $view The DTO being populated.
     * @param string $vatPrefix The prefix text for the VAT section (e.g., "incl. " or "plus ").
     * @param bool $isGrossInvoice Whether we are rendering a gross or net invoice.
     */
    private function calculateVatSection(InvoiceTableView $view, string $vatPrefix, bool $isGrossInvoice): string
    {
        $vatCategories = $this->calculator->getVatCategories();
        if (count($vatCategories) === 1) {
            $vatCategory = VatCategory::from(array_key_first($vatCategories));

            $vatLabel = __('invoice::invoice.vat');

            return sprintf('%s - %s%% %s %s',
                $vatCategory->description(),
                Vat::country($this->customer->countryCode)->getVat($vatCategory->vatRate())->rate * 100,
                is_string($vatLabel) ? $vatLabel : 'VAT',
                $vatPrefix
            );
        }

        $view->vatTableView = new VatTableView;
        $view->vatTableView->header = VatLineView::header('');
        $categoryGrossSum = $this->calculator->getCategoryGrossSum();
        $categoryNetSum = $this->calculator->getCategoryNetSum();

        foreach ($vatCategories as $categoryId => $lineItems) {
            $vatCategory = VatCategory::from($categoryId);
            $view->vatTableView->body[] = VatLineView::lineItem(
                array_keys($lineItems), $vatCategory,
                $categoryGrossSum[$categoryId],
                $categoryNetSum[$categoryId],
                $isGrossInvoice
            );
        }

        return $vatPrefix.'<br>'.$view->vatTableView->html();
    }
}
