<?php

namespace SchenkeIo\Invoice\Invoicing\Views;

use SchenkeIo\Invoice\Contracts\LineViewInterface;
use SchenkeIo\Invoice\Enum\LineDisplayType;

/**
 * Base class for generating HTML table representations of invoice data.
 *
 * This abstract class provides the structure and configuration for
 * rendering invoice tables, including headers, bodies, and footers.
 * It supports both raw HTML generation and Laravel Blade-based rendering.
 *
 * Key responsibilities:
 * - Storing header, body, and footer line objects.
 * - Managing default CSS classes and configuration for rendering.
 * - Providing a unified `html()` method that handles configuration merging
 *   and rendering delegation (to raw HTML or Blade).
 * - Defining the structure of the table (thead, tbody, tfoot).
 */
abstract class TableView
{
    public LineViewInterface $header;

    /**
     * @var LineViewInterface[]
     */
    public array $body = [];

    /**
     * @var LineViewInterface[]
     */
    public array $footer = [];

    /**
     * key - class pairs for various situations
     *
     * @var array<string,string|null>
     */
    protected array $defaults = [
        'table-class' => 'table-invoice',
        'invoice-row-thead' => '',
        'invoice-row-tbody' => '',
        'invoice-row-tfoot' => '',
        'invoice-row-empty' => 'empty-line',
        'invoice-cell-right' => 'cell-right',
        'invoice-cell-left' => 'cell-left',
        'invoice-cell-bold' => 'cell-bold',
        'blade-view' => null,
    ];

    /**
     * @param  array<string,string|null>  $finalConfig
     */
    protected function preLinesHtml(array $finalConfig, string $header): string
    {
        $html = "\n<table class=\"{$finalConfig['table-class']}\">\n  <thead>\n";
        $html .= $header;
        $html .= "  </thead>\n  <tbody>\n";

        return $html;
    }

    /**
     * @param  array<string,string|null>  $finalConfig
     */
    public function postLinesHtml(array $finalConfig, int $columnCount): string
    {
        $html = "  </tbody>\n  <tfoot>\n";
        // this can be styled a line separating all items from the summary
        $html .= sprintf("    <tr class='%s'><td colspan='%d'></td></tr>\n",
            $finalConfig['invoice-row-empty'] ?? '',
            $columnCount,
        );

        return $html;
    }

    /**
     * returns the closing tags for the table
     */
    public function postFooterHtml(): string
    {
        return "  </tfoot>\n</table>\n\n";
    }

    /**
     * Generate the HTML for the table.
     *
     * This method orchestrates the rendering process. It first builds the
     * final configuration by merging defaults with the provided config.
     * If a Blade view is specified in the config and the Laravel view()
     * helper is available, it delegates rendering to Blade.
     * Otherwise, it builds the HTML string by iterating through header,
     * body, and footer lines.
     *
     * @param  array<string,string|null>  $config  Optional configuration overrides.
     */
    public function html(array $config = []): string
    {
        $finalConfig = $this->buildConfig($config);

        if (isset($finalConfig['blade-view']) && function_exists('view')) {
            return view($finalConfig['blade-view'], ['view' => $this, 'config' => $finalConfig])->render();
        }

        $html = $this->preLinesHtml($finalConfig, $this->header->html($finalConfig, LineDisplayType::thead));
        foreach ($this->body as $line) {
            $html .= $line->html($finalConfig, LineDisplayType::tbody);
        }
        $html .= $this->postLinesHtml($finalConfig, count($this->header->columns()));
        foreach ($this->footer as $line) {
            $html .= $line->html($finalConfig, LineDisplayType::tfoot);
        }
        $html .= $this->postFooterHtml();

        return $html;
    }

    /**
     * Merge the default configuration with the provided configuration.
     *
     * To ensure stability, this method filters the user-provided config to
     * only include keys that exist in the defaults. Values from the
     * valid user config will then overwrite the defaults.
     *
     * @param  array<string,string|null>  $config  User-provided configuration.
     * @return array<string,string|null> The merged and filtered configuration.
     */
    protected function buildConfig(array $config): array
    {
        // First, filter the user input to only include keys that exist in the defaults.
        $allowedKeys = array_keys($this->defaults);
        $validConfig = array_intersect_key($config, array_flip($allowedKeys));

        // Second, merge the defaults with the now-safe and filtered user input.
        // The values from $validConfig will overwrite the values in $defaults.
        return array_merge($this->defaults, $validConfig);
    }
}
