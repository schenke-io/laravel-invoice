<!--

This file was written by 'make-markdown.php' line 14 using
SchenkeIo\PackagingTools\Markdown\MarkdownAssembler

Do not edit manually as it will be overwritten.

-->

[![](.github/phpstan.svg)]()
[![](.github/coverage.svg)]()


# Laravel Invoice

Handling of:
- Currency
- Invoice




### Currency



#### Public methods of Currency

| method         | summary                                                        |
|----------------|----------------------------------------------------------------|
| fromAny        | static constructor from any value                              |
| fromFloat      | static constructor from a float value                          |
| fromCents      | static constructor from cents                                  |
| vatFromGross   | VAT amount from the gross price, given a VAT rate.             |
| vatFromNet     | Calculate the VAT amount from the net price, given a VAT rate. |
| fromGrossToNet | convert a gross value to a net value using VAT                 |
| fromNetToGross | Convert a net value to a gross value using VAT                 |
| toFloat        | exports to float                                               |
| str            | exports to formatted currency string                           |
| plus           | adds two objects                                               |
| minus          | subtracts two objects                                          |
| times          | multiplies the object by a factor                              |
| toLivewire     | exports to Livewire format                                     |
| fromLivewire   | static constructor from Livewire format                        |
| isEmpty        | Check if the object is empty (zero)                            |



### InvoiceNumericData



#### Public methods of InvoiceNumericData

| method          | summary                                                 |
|-----------------|---------------------------------------------------------|
| addWeight       | take the weight in grams and add it to the total weight |
| addLine         | cent based calculation to avoid numeric glitches        |
| vats            | formats the VAT values in a readable format             |
| payMe           | show pay me information                                 |
| isEmpty         | the total is zero                                       |
| invoiceViewData | data for blade templates                                |



# Custom invoice 

To build a custom invoice you first generate a class which 
extends `LineViewBase` and implements `InvoiceLineView`. 

This class should define the column-alignment.

Then you start a new instance of `InvoiceViewData` and fill its public data.
The `__construct()` method must declare public variables same name as the keys in `columns()`.


