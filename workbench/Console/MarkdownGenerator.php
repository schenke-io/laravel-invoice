<?php

namespace Workbench\Console;

use SchenkeIo\Invoice\Banking\SepaCode;
use SchenkeIo\Invoice\Enum\InvoiceLineType;
use SchenkeIo\Invoice\Invoicing\Customer;
use SchenkeIo\Invoice\Invoicing\InvoiceNumeric;
use SchenkeIo\Invoice\Invoicing\LineData;
use SchenkeIo\Invoice\Money\Currency;
use SchenkeIo\Invoice\Money\Vat;
use SchenkeIo\PackagingTools\Badges\MakeBadge;
use SchenkeIo\PackagingTools\Markdown\MarkdownAssembler;

class MarkdownGenerator
{
    public function execute(): void
    {
        MakeBadge::auto();

        $mda = new MarkdownAssembler('workbench/resources/md');
        $mda->autoHeader('Laravel Invoice');

        $mda->addMarkdown('introduction.md');
        $mda->addMarkdown('examples.md');

        $mda->classes()
            ->add(Currency::class)
            ->add(Vat::class)
            ->add(InvoiceNumeric::class)
            ->add(Customer::class)
            ->add(LineData::class)
            ->add(InvoiceLineType::class)
            ->add(SepaCode::class);

        $mda->addMarkdown('custom-invoice.md');
        $mda->writeMarkdown('README.md');
    }

    public static function run(): void
    {
        $command = new self;
        $command->execute();
    }
}
