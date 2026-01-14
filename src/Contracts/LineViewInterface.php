<?php

namespace SchenkeIo\Invoice\Contracts;

use SchenkeIo\Invoice\Enum\LineDisplayType;

/**
 * Interface for rendering invoice lines.
 *
 * This interface defines the contract for classes that handle the visual
 * representation of invoice lines, including column definitions and
 * HTML generation based on configuration and display type.
 */
interface LineViewInterface
{
    /**
     * key and right-aligned yes/no definitions per column
     *
     * @return array<string,bool>
     */
    public function columns(): array;

    /**
     * @param  array<string,string|null>  $config
     */
    public function html(array $config, LineDisplayType $type): string;
}
