<?php

namespace SchenkeIo\Invoice\Invoicing;

use Spatie\LaravelData\Data;

/*

$voucherData = [
  // WICHTIG: Der Typ ist 'invoice' für eine Ausgangsrechnung.
  'voucherType' => 'invoice',

  // WICHTIG: Hier übergeben Sie die Rechnungsnummer aus Ihrem PDF!
  // Das verhindert, dass Lexware eine neue Nummer generiert.
  'voucherNumber' => 'RE-2025-0815', // Beispiel: Ihre Original-Rechnungsnummer

  // Die Adressdaten des Kunden übergeben Sie weiterhin direkt.
  'contact' => [
    'name' => 'Kundenfirma GmbH & Co. KG',
    'address' => [
      'street' => 'Hauptstraße 123',
      'zip' => '12345',
      'city' => 'Berlin',
      'countryCode' => 'DE'
    ]
  ],

  'voucherDate' => '2025-09-09T00:00:00.000+02:00', // Datum Ihrer Original-Rechnung

  // Belegpositionen
  'voucherItems' => [
    [
      'name' => 'Beratungsleistung Projekt X',
      'netAmount' => 1000.00,
      'grossAmount' => 1190.00,

      // WICHTIG: Anstelle der 'taxRateId' geben Sie den Steuersatz in Prozent an.
      'taxRatePercentage' => 19.00
    ],
    [
      'name' => 'Fahrtkostenpauschale',
      'netAmount' => 50.00,
      'grossAmount' => 59.50,
      'taxRatePercentage' => 19.00
    ]
  ],

  // Die Gesamtbeträge müssen der Summe der Positionen entsprechen.
  'totalPrice' => [
    'totalNetAmount' => 1050.00,
    'totalGrossAmount' => 1249.50
  ],

  // Die Verknüpfung zur hochgeladenen PDF-Datei bleibt gleich.
  'files' => [
    [
      'id' => 'g345678c-901d-e23f-g456-7890abcdef2g' // ID aus dem PDF-Upload
    ]
  ]
];

*/

/**
 * takes the Payment object and the related Trip and User to fill out all necessary data for Lexware
 */
class LexwareInvoiceData {}
