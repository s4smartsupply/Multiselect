<?php

use Xylo\MultiselectTwo\Forms\Components\XyloMultiselectTwo;

it('can enable search', function () {
    $field = XyloMultiselectTwo::make('items')->enableSearch();

    expect($field->isSearchable())->toBeTrue();
});

it('can disable barcode scanner', function () {
    $field = XyloMultiselectTwo::make('items')->barcodeScanner(false);

    expect($field->hasBarcodeScanner())->toBeFalse();
});

it('can set selectable and selected labels', function () {
    $field = XyloMultiselectTwo::make('items')
        ->selectableLabel('Available items')
        ->selectedLabel('In this program');

    expect($field->getSelectableLabel())->toBe('Available items')
        ->and($field->getSelectedLabel())->toBe('In this program');
});

it('normalizes rich options with descriptions barcodes and skus', function () {
    $field = XyloMultiselectTwo::make('items')
        ->options([
            '1' => 'Cheese Burger 6"',
            '2' => [
                'label' => 'Beef Burger',
                'description' => '$8.99',
                'barcode' => '100200',
                'sku' => 'BF-01',
            ],
        ])
        ->descriptions([
            '1' => '$7.99',
        ])
        ->barcodes([
            '1' => '100100',
        ])
        ->skus([
            '1' => 'CH-06',
        ]);

    $normalized = $field->getNormalizedOptions();

    expect($normalized)->toBe($field->getNormalizedOptions())
        ->and($normalized['1']['label'])->toBe('Cheese Burger 6"')
        ->and($normalized['1']['description'])->toBe('$7.99')
        ->and($normalized['1']['barcode'])->toBe('100100')
        ->and($normalized['1']['sku'])->toBe('CH-06')
        ->and($normalized['2']['label'])->toBe('Beef Burger')
        ->and($normalized['2']['description'])->toBe('$8.99')
        ->and($normalized['2']['barcode'])->toBe('100200');
});

it('trims stored codes so padded values still match a scan', function () {
    $normalized = XyloMultiselectTwo::make('items')
        ->options([
            '1' => [
                'label' => 'Cheese Burger 6"',
                'barcode' => "  0012546011112\t",
                'sku' => ' CH-06 ',
            ],
            '2' => ['label' => 'Beef Burger', 'barcode' => '   '],
        ])
        ->getNormalizedOptions();

    expect($normalized['1']['barcode'])->toBe('0012546011112')
        ->and($normalized['1']['sku'])->toBe('CH-06')
        ->and($normalized['1']['search'])->toContain('0012546011112')
        ->and($normalized['2']['barcode'])->toBeNull();
});

it('matches scans loosely by default and strictly on request', function () {
    $default = XyloMultiselectTwo::make('items');
    $strict = XyloMultiselectTwo::make('items')->barcodeStrict();

    expect($default->isBarcodeStrict())->toBeFalse()
        ->and($strict->isBarcodeStrict())->toBeTrue()
        ->and($strict->barcodeStrict(false)->isBarcodeStrict())->toBeFalse();
});

it('can toggle scan feedback and its duration', function () {
    $default = XyloMultiselectTwo::make('items');
    $custom = XyloMultiselectTwo::make('items')->scanFeedback(false)->scanFeedbackDuration(0);

    expect($default->shouldShowScanFeedback())->toBeTrue()
        ->and($default->getScanFeedbackDuration())->toBe(4000)
        ->and($custom->shouldShowScanFeedback())->toBeFalse()
        ->and($custom->getScanFeedbackDuration())->toBe(0)
        ->and(XyloMultiselectTwo::make('items')->scanFeedbackDuration(-50)->getScanFeedbackDuration())->toBe(0);
});

it('only hands a missed scan to the search box when search is enabled', function () {
    $default = XyloMultiselectTwo::make('items');
    $optedOut = XyloMultiselectTwo::make('items')->searchOnScanMiss(false);
    $withoutSearch = XyloMultiselectTwo::make('items')->searchable(false);

    expect($default->shouldSearchOnScanMiss())->toBeTrue()
        ->and($optedOut->shouldSearchOnScanMiss())->toBeFalse()
        ->and($withoutSearch->shouldSearchOnScanMiss())->toBeFalse();
});

it('exposes scan messages with label and code placeholders', function () {
    $messages = XyloMultiselectTwo::make('items')->getScanMessages();

    expect($messages)->toHaveKeys(['added', 'already_added', 'not_allowed', 'not_found', 'ambiguous'])
        ->and($messages['added'])->toContain(':label')
        ->and($messages['not_found'])->toContain(':code')
        ->and($messages['ambiguous'])->toContain(':code');
});

it('uses natural accent by default', function () {
    $field = XyloMultiselectTwo::make('items');

    expect($field->getAccentColor())->toBe('natural')
        ->and($field->isNamedAccentColor())->toBeTrue()
        ->and($field->getAccentStyle())->toBeNull();
});

it('accepts a custom accent color', function () {
    $field = XyloMultiselectTwo::make('items')->accentColor('#16a34a');

    expect($field->getAccentColor())->toBe('#16a34a')
        ->and($field->isNamedAccentColor())->toBeFalse()
        ->and($field->getAccentDataAttribute())->toBe('custom')
        ->and($field->getThemeStyle())->toContain('--xms-accent: #16a34a;');
});

it('resolves shared and separate hover colors', function () {
    $shared = XyloMultiselectTwo::make('items')
        ->hoverColor('#dbeafe');

    expect($shared->getThemeStyle())
        ->toContain('--xms-available-hover: #dbeafe;')
        ->toContain('--xms-selected-hover: #dbeafe;')
        ->toContain('--xms-add-hover: #dbeafe;')
        ->toContain('--xms-remove-hover: #dbeafe;');

    $separate = XyloMultiselectTwo::make('items')
        ->itemsHoverColor('#ecfdf5')
        ->buttonsHoverColor('#fef3c7')
        ->availableHoverColor('#d1fae5')
        ->addAllHoverColor('#fde68a')
        ->addAllBackgroundColor('#f3f4f6')
        ->removeAllBackgroundColor('#ffffff');

    expect($separate->getThemeStyle())
        ->toContain('--xms-available-hover: #d1fae5;')
        ->toContain('--xms-selected-hover: #ecfdf5;')
        ->toContain('--xms-add-hover: #fde68a;')
        ->toContain('--xms-remove-hover: #fef3c7;')
        ->toContain('--xms-add-bg: #f3f4f6;')
        ->toContain('--xms-remove-bg: #ffffff;');
});

it('keeps default button backgrounds when unset', function () {
    $field = XyloMultiselectTwo::make('items')->accentColor('natural');

    expect($field->getThemeStyle())->toBeNull();
});

it('can customize list height and bulk action labels', function () {
    $field = XyloMultiselectTwo::make('items')
        ->listHeight(280)
        ->addAllLabel('Add all shown')
        ->removeAllLabel('Remove all');

    expect($field->getListHeight())->toBe(280)
        ->and($field->getAddAllLabel())->toBe('Add all shown')
        ->and($field->getRemoveAllLabel())->toBe('Remove all');
});

it('uses a default page size of 80 and accepts custom values', function () {
    $default = XyloMultiselectTwo::make('items');
    $custom = XyloMultiselectTwo::make('items')->pageSize(40);
    $invalid = XyloMultiselectTwo::make('items')->pageSize(0);

    expect($default->getPageSize())->toBe(80)
        ->and($custom->getPageSize())->toBe(40)
        ->and($invalid->getPageSize())->toBe(1);
});

it('can set a limitTo state path', function () {
    $field = XyloMultiselectTwo::make('items')->limitTo('qualifying_ids');

    expect($field)->toBeInstanceOf(XyloMultiselectTwo::class);
});

it('keeps MultiselectTwo as a deprecated alias', function () {
    $field = \Xylo\MultiselectTwo\Forms\Components\MultiselectTwo::make('items');

    expect($field)->toBeInstanceOf(XyloMultiselectTwo::class);
});
