<?php


require "vendor/autoload.php";

use SchenkeIo\PackagingTools\Markdown\MarkdownAssembler;
use SchenkeIo\PackagingTools\Badges\BadgeStyle;
use SchenkeIo\PackagingTools\Badges\MakeBadge;

MakeBadge::makePhpStanBadge('phpstan.neon')->store('.github/phpstan.svg', BadgeStyle::Flat);
MakeBadge::makeCoverageBadge('build/logs/clover.xml')->store('.github/coverage.svg', BadgeStyle::Flat);


$mda = new MarkdownAssembler("resources/md");
$mda->storeLocalBadge('', '.github/phpstan.svg');
$mda->storeLocalBadge('', '.github/coverage.svg');
$mda->addBadges();
$mda->addMarkdown('introduction.md');
$mda->writeMarkdown('README.md');