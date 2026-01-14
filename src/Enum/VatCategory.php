<?php

namespace SchenkeIo\Invoice\Enum;

/**
 * Defines the high-level tax categories
 * based on the analysis from section I.C of the legal opinion.
 */
enum VatCategory: int
{
    /**
     * Taxable main or secondary service (max VAT).
     * Remuneration for own service.
     */
    case Taxable = 1;

    /**
     * Non-taxable real damages.
     * Payment *for* a damage, *without* counter-service.
     */
    case NonTaxableDamages = 2;

    /**
     * Non-taxable transitory item.
     * Disbursement in the name and for the account of another.
     */
    case NonTaxableTransitory = 3;

    /**
     * Deposit.
     */
    case Deposits = 4;

    /**
     * Reverse charge VAT for remuneration.
     */
    case ReverseChargingStandard = 5;
    case ReverseChargingReduced = 6;

    /**
     * Reduced VAT rate for this category.
     */
    case TaxableReducedVatRate = 9;

    /**
     * German description of the category.
     */
    public function description(?\SchenkeIo\Invoice\Contracts\TranslationInterface $translator = null): string
    {
        $key = match ($this) {
            self::Taxable => 'invoice::invoice.vat_categories.taxable',
            self::NonTaxableDamages => 'invoice::invoice.vat_categories.non_taxable_damages',
            self::NonTaxableTransitory => 'invoice::invoice.vat_categories.non_taxable_transitory',
            self::Deposits => 'invoice::invoice.vat_categories.deposits',
            self::ReverseChargingStandard => 'invoice::invoice.vat_categories.reverse_charging_standard',
            self::TaxableReducedVatRate => 'invoice::invoice.vat_categories.taxable_reduced_vat_rate',
            self::ReverseChargingReduced => 'invoice::invoice.vat_categories.reverse_charging_reduced',
        };

        if ($translator) {
            return $translator->translate($key);
        }

        $description = __($key);

        return is_string($description) ? $description : '';
    }

    public function vatRate(): VatRate
    {
        return match ($this) {
            self::Taxable , self::ReverseChargingStandard => VatRate::Standard,
            self::NonTaxableDamages, self::NonTaxableTransitory, self::Deposits => VatRate::None,
            self::TaxableReducedVatRate, self::ReverseChargingReduced => VatRate::Reduced
        };
    }

    /**
     * Checks if this transaction case involves VAT.
     *
     * @return bool True if the case has VAT, false otherwise.
     */
    public function hasVat(): bool
    {
        return $this->vatRate() != VatRate::None;
    }
}
