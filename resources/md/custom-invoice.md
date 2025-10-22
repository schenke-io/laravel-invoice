# Custom invoice 

To build a custom invoice you first generate a class which extends
`SchenkeIo\Invoice\Contracts\InvoiceLineView`. This class should define the columns
and must be able to generate HTML for any `LineDisplayType` 
using the config values from `InvoiceViewData`.

Then you start a new instance of `InvoiceViewData` and fill its public data.
