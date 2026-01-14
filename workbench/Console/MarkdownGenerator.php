<?php

namespace Workbench\Console;

use SchenkeIo\Invoice\Banking\SepaCode;
use SchenkeIo\Invoice\Enum\InvoiceLineType;
use SchenkeIo\Invoice\Invoicing\Customer;
use SchenkeIo\Invoice\Invoicing\InvoiceNumeric;
use SchenkeIo\Invoice\Invoicing\LineData;
use SchenkeIo\Invoice\Money\Currency;
use SchenkeIo\Invoice\Money\Vat;
use SchenkeIo\PackagingTools\Badges\BadgeStyle;
use SchenkeIo\PackagingTools\Badges\MakeBadge;
use SchenkeIo\PackagingTools\Markdown\MarkdownAssembler;

class MarkdownGenerator
{
    public function execute(): void
    {
        MakeBadge::makePhpStanBadge('phpstan.neon')->store('.github/phpstan.svg', BadgeStyle::Flat);
        MakeBadge::makeCoverageBadge('build/logs/clover.xml')->store('.github/coverage.svg', BadgeStyle::Flat);

        $mda = new MarkdownAssembler('workbench/resources/md');
        $mda->storeLocalBadge('', '.github/phpstan.svg');
        $mda->storeLocalBadge('', '.github/coverage.svg');
        $mda->addBadges();
        $mda->addMarkdown('introduction.md');
        $mda->addMarkdown('examples.md');
        $mda->addClassMarkdown(Currency::class);
        $mda->addClassMarkdown(Vat::class);
        $mda->addClassMarkdown(InvoiceNumeric::class);
        $mda->addClassMarkdown(Customer::class);
        $mda->addClassMarkdown(LineData::class);
        $mda->addClassMarkdown(InvoiceLineType::class);
        $mda->addClassMarkdown(SepaCode::class);
        $mda->addMarkdown('custom-invoice.md');
        $mda->writeMarkdown('README.md');
    }

    public static function run(): void
    {
        $command = new self;
        $command->execute();
    }
}
