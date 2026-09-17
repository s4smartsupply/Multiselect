<?php

return [
    'selectable' => [
        'label' => 'Available items',
        'search_placeholder' => 'Search by name, SKU or barcode',
        'barcode_placeholder' => 'Scan barcode & press Enter',
    ],

    'selected' => [
        'label' => 'In this program',
        'search_placeholder' => 'Filter included items',
    ],

    'actions' => [
        'add_all' => 'Add all shown',
        'remove_all' => 'Remove all',
        'remove' => 'Remove',
        'move_back' => 'Move back',
    ],

    'empty' => [
        'available' => 'No available items',
        'selected' => 'No items selected',
    ],

    'scan' => [
        'added' => 'Added :label',
        'already_added' => ':label is already selected',
        'not_allowed' => ':label is not available in this list',
        'not_found' => 'No item matches :code',
        'ambiguous' => 'Several items match :code — pick the right one from the list',
    ],
];
