<?php

namespace SchenkeIo\Invoice\Banking;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use SchenkeIo\Invoice\Invoicing\InvoiceNumeric;
use SepaQr\SepaQrData;

/**
 * Generator for SEPA QR codes (BezahlCode).
 *
 * This class facilitates the creation of SEPA-compliant QR codes for
 * bank transfers. It takes payment details like name, IBAN, and amount,
 * and generates a data URI for the QR code image.
 */
class SepaCode
{
    protected SepaQrData $paymentData;

    public function __construct(string $name, string $iban, float $amountEuro, protected string $information)
    {
        $amountEuro = max(0.01, $amountEuro);  // the minimum amount is 0.01 €
        $this->paymentData = (new SepaQrData)
            ->setName($name)
            ->setIban($iban)
            ->setAmount($amountEuro)
            ->setRemittanceText($information);
    }

    public static function fromInvoice(
        InvoiceNumeric $invoice,
        string $name,
        string $iban,
        string $infoPrefix): self
    {
        return new self($name, $iban, $invoice->getTotalGrossPrice()->toFloat(), $infoPrefix.' '.$invoice->invoiceId);
    }

    public function dataUri(int $red = 0, int $green = 0, int $blue = 0): string
    {
        $writer = new PngWriter;
        // Create generic label
        $label = new Label(
            text: $this->information,
            textColor: new Color($red, $green, $blue)
        );
        $qrCode = new QrCode(
            data: $this->paymentData,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium
        );

        return $writer->write($qrCode, null, $label)->getDataUri();
    }
}
