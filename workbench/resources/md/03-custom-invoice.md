# Custom invoice 

To build a custom invoice you first generate a class which 
extends `LineViewBase` and implements `LineViewInterface`. 

This class should define the column-alignment in the `columns()` method.

Then you start a new instance of `InvoiceTableView` and fill its public data.
The `columns()` method must return keys that correspond to the public properties of your custom line view class.