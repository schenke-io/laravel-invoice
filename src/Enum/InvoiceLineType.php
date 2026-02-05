<?php

namespace SchenkeIo\Invoice\Enum;

/**
 * Enum defining the various types of line items that can appear on an invoice.
 *
 * Each type represents a specific business scenario (e.g., sales, rentals,
 * deposits, damages) and maps to a corresponding VAT category and rate,
 * ensuring correct tax calculation and accounting. This categorization
 * allows the system to automate tax handling based on the nature of the
 * transaction, providing both financial accuracy and clear reporting.
 */
enum InvoiceLineType: int
{
    /*
     * --------------------------------------------------------------------------
     * GROUP 1: OWN SERVICES (Sales & Rental)
     * --------------------------------------------------------------------------
     */

    /**
     * Sale of goods and services or accessories (Standard VAT rate).
     * Accounting: Revenue (Taxable).
     */
    case SalesDE = 10;
    case SalesEU = 11;

    /**
     * Sale of books (Reduced VAT rate).
     * Accounting: Revenue reduced (Taxable).
     */
    case SaleBooksDE = 12;
    case SaleBooksEU = 13;

    /**
     * Rental of motorhomes (Daily rates, service fees).
     * Accounting: Rental income (Taxable).
     */
    case RentalFee = 20;

    /*
     * --------------------------------------------------------------------------
     * GROUP 2: REVERSALS & CANCELLATIONS (Credits)
     * --------------------------------------------------------------------------
     */

    /**
     * Customer returns electronics.
     * Accounting: Return of goods / revenue reduction (Taxable).
     */
    case ReturnElectronicsDE = 30;
    case ReturnElectronicsEU = 31;

    /**
     * Customer returns books.
     * Accounting: Return of goods reduced / revenue reduction (Taxable).
     */
    case ReturnBooksDE = 32;
    case ReturnBooksEU = 33;

    /*
     * --------------------------------------------------------------------------
     * GROUP 3: SPECIAL CASES & FINANCES
     * --------------------------------------------------------------------------
     */

    /**
     * Deposit or withdrawal of security deposit (Kaution).
     * Accounting: Deposits received (Non-taxable, as only parked).
     */
    case Deposit = 40;

    /*
     * --------------------------------------------------------------------------
     * GROUP 4: INCIDENTS (Events from travel logic)
     * These names correspond exactly to the Incident enum.
     * --------------------------------------------------------------------------
     */

    /**
     * Damage to vehicle or inventory that is invoiced.
     * Accounting: Real damage compensation (Non-taxable).
     */
    case Damage = 50;

    /**
     * Price reduction due to defects (e.g. heating did not work).
     * Accounting: Revenue reduction / price reduction (Taxable, corrects the rent).
     */
    case Refund = 51;

    /**
     * Passing on a traffic ticket/fine to the customer.
     * Accounting: Transitory item or cost reimbursement (Non-taxable).
     */
    case Fine = 52;

    /**
     * We reimburse the customer for expenses (e.g. they bought oil or paid for a workshop).
     * Accounting: Transitory item (Non-taxable, pure money return).
     */
    case Reimbursement = 53;

    /**
     * Returns the tax category.
     */
    public function vatCategory(): VatCategory
    {
        return match ($this) {
            // Normal services (electronics, rent) -> Taxable Standard
            self::SalesDE,
            self::RentalFee,
            self::ReturnElectronicsDE => VatCategory::Taxable,

            // Books -> Taxable Reduced
            self::SaleBooksDE,
            self::ReturnBooksDE => VatCategory::TaxableReducedVatRate,

            // Price reduction on rent (Refund) corrects the taxable service
            self::Refund => VatCategory::Taxable,

            // Real damages (Damage, deductible) -> Non-taxable
            self::Damage => VatCategory::NonTaxableDamages,

            // Transitory items (traffic tickets, cost reimbursement)
            self::Fine,
            self::Reimbursement => VatCategory::NonTaxableTransitory,

            // Deposit
            self::Deposit => VatCategory::Deposits,

            self::SalesEU,
            self::ReturnElectronicsEU => VatCategory::ReverseChargingStandard,

            self::SaleBooksEU,
            self::ReturnBooksEU => VatCategory::ReverseChargingReduced
        };
    }

    /**
     * Returns the applicable VAT rate type.
     */
    public function vatRate(): VatRate
    {
        return $this->vatCategory()->vatRate();
    }
}
