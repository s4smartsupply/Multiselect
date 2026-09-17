<?php

use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Component;
use Xylo\MultiselectTwo\Forms\Components\XyloMultiselectTwo;

class SchemaHost extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public array $data = ['items' => []];

    public function render()
    {
        return '<div></div>';
    }
}

function renderField(XyloMultiselectTwo $field): string
{
    $schema = Schema::make(new SchemaHost)->statePath('data')->components([$field]);

    $field->container($schema);

    return $field->toHtml();
}

it('renders the scan controls and keeps the code client side', function () {
    $html = renderField(
        XyloMultiselectTwo::make('items')->options([
            '1' => ['label' => 'Cheese Burger', 'barcode' => '0012546011112', 'sku' => 'CH-06'],
        ])
    );

    expect($html)
        ->toContain('x-ref="barcodeInput"')
        ->toContain('scanBarcode()')
        ->toContain('xms-two__scan-status')
        ->toContain('barcodeStrict')
        ->toContain('gtinKey')
        // Nothing may call home with a scanned code.
        ->not->toContain('fetch(')
        ->not->toContain('console.')
        ->not->toContain('$wire.call');
});

it('omits scan feedback wiring when the scanner or feedback is off', function () {
    $noScanner = renderField(XyloMultiselectTwo::make('items')->options(['1' => 'A'])->barcodeScanner(false));
    $noFeedback = renderField(XyloMultiselectTwo::make('items')->options(['1' => 'A'])->scanFeedback(false));

    expect($noScanner)
        ->not->toContain('x-ref="barcodeInput"')
        ->not->toContain('xms-two__scan-status')
        ->and($noFeedback)
        ->toContain('x-ref="barcodeInput"')
        ->not->toContain('xms-two__scan-status');
});

it('does not render option barcodes beyond the existing search payload', function () {
    $html = renderField(
        XyloMultiselectTwo::make('items')->options([
            '1' => ['label' => 'Cheese Burger', 'barcode' => '  0012546011112  '],
        ])
    );

    // Trimmed once on the server; the GTIN variants are derived in the browser.
    expect(substr_count($html, '0012546011112'))->toBe(2)
        ->and($html)->not->toContain('00012546011112');
});
