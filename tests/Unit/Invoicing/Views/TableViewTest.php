<?php

use SchenkeIo\Invoice\Contracts\LineViewInterface;
use SchenkeIo\Invoice\Enum\LineDisplayType;
use SchenkeIo\Invoice\Invoicing\Views\TableView;

it('can generate HTML without blade view', function () {
    $header = Mockery::mock(LineViewInterface::class);
    $header->shouldReceive('html')->andReturn('<tr><th>Header</th></tr>');
    $header->shouldReceive('columns')->andReturn(['col1' => false]);

    $line = Mockery::mock(LineViewInterface::class);
    $line->shouldReceive('html')->with(Mockery::any(), LineDisplayType::tbody)->andReturn('<tr><td>Body</td></tr>');
    $line->shouldReceive('html')->with(Mockery::any(), LineDisplayType::tfoot)->andReturn('<tr><td>Footer</td></tr>');

    $view = new class extends TableView {};
    $view->header = $header;
    $view->body = [$line];
    $view->footer = [$line];

    $html = $view->html();

    expect($html)->toContain('<table class="table-invoice">')
        ->and($html)->toContain('<tr><th>Header</th></tr>')
        ->and($html)->toContain('<tr><td>Body</td></tr>')
        ->and($html)->toContain('<tr><td>Footer</td></tr>')
        ->and($html)->toContain('</table>');
});

it('merges config correctly', function () {
    $view = new class extends TableView
    {
        public function getFinalConfig(array $config): array
        {
            return $this->buildConfig($config);
        }
    };

    $config = $view->getFinalConfig(['table-class' => 'custom-table', 'invalid-key' => 'value']);

    expect($config['table-class'])->toBe('custom-table')
        ->and($config)->not->toHaveKey('invalid-key')
        ->and($config['invoice-row-thead'])->toBe('');
});
