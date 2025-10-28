# Custom invoice 

To build a custom invoice you first generate a class which 
extends `LineViewBase` and implements `InvoiceLineView`. 

This class should define the column-alignment.

Then you start a new instance of `InvoiceViewData` and fill its public data.
The `__construct()` method must declare public variables same name as the keys in `columns()`.