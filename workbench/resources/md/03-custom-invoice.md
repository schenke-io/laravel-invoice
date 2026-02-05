# Custom invoice 

To build a custom invoice you first generate a class which 
extends `LineViewBase` and implements `LineViewInterface`. 

This class should define the column-alignment in the `columns()` method.

Then you start a new instance of `InvoiceTableView` and fill its public data.
The `columns()` method must return keys that correspond to the public properties of your custom line view class.

### CSS Styling

The rendering engine uses a configuration-based approach for CSS classes. You can customize the look of your tables by providing a config array to the `html()` method or by using the default configuration in `TableView`.

#### Row Classes
The following keys in the configuration control row-level styling:
- `invoice-row-thead`: Classes for the `<thead>` row.
- `invoice-row-tbody`: Classes for rows within `<tbody>`.
- `invoice-row-tfoot`: Classes for rows within `<tfoot>`.
- `invoice-row-empty`: Classes for empty/spacer rows.
- `invoice-row-{LineType}`: Classes specifically for rows of a certain `InvoiceLineType` (e.g., `invoice-row-SalesDE`).

#### Cell Classes
The following keys control cell-level alignment and emphasis:
- `invoice-cell-left`: Classes for left-aligned cells (default: `cell-left`).
- `invoice-cell-right`: Classes for right-aligned cells (default: `cell-right`).
- `invoice-cell-bold`: Classes for cells that should be bold (default: `cell-bold`).