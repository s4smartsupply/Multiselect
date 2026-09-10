# Xylo Multiselect Two

A Filament form field for transferring items between two lists — available and selected — with search, barcode scanning, descriptions, count badges, and a natural green accent.

Compatible with **Filament 4.x and 5.x**.

## Installation

```bash
composer require xylo/multiselect-two
```

If you install from a local path:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../xylo-multiselect-two"
    }
  ]
}
```

```bash
composer require xylo/multiselect-two:@dev
```

Publish assets (recommended after install / update):

```bash
php artisan filament:assets
```

Optional translations:

```bash
php artisan vendor:publish --tag="xylo-multiselect-two-translations"
```

## Usage

```php
use Xylo\MultiselectTwo\Forms\Components\XyloMultiselectTwo;

XyloMultiselectTwo::make('menu_item_ids')
    // Optional — omit both (and use hiddenLabel) if you want no title/subtitle
    ->label('STEP 2 · Choose the items in this program')
    ->helperText('A punch is earned when a customer buys any item on the right.')
    ->selectableLabel('Available items')
    ->selectedLabel('In this program')
    ->options([
        '1' => 'Cheese Burger 6"',
        '2' => 'Beef Burger',
        '3' => 'Chicken Burger',
        '4' => 'Veggie Burger',
        '5' => 'Fish Burger',
        '6' => 'Double Cheese',
        '7' => 'Classic Fries',
        '8' => 'Onion Rings',
    ])
    ->descriptions([
        '1' => '$7.99',
        '2' => '$8.99',
        '3' => '$7.49',
        '4' => '$6.99',
        '5' => '$8.49',
        '6' => '$9.99',
        '7' => '$3.49',
        '8' => '$4.29',
    ])
    ->barcodes([
        '1' => '100100',
        '2' => '100200',
        '3' => '100300',
    ])
    ->skus([
        '1' => 'CH-06',
        '2' => 'BF-01',
    ])
    ->accentColor('#2f9e7a') // or 'natural' / 'primary' / any CSS color
    ->default(['2', '7']);
```

Without title / subtitle:

```php
XyloMultiselectTwo::make('menu_item_ids')
    ->hiddenLabel()
    ->options($items)
    ->accentColor('#0ea5e9');
```

### Rich options (label + description + barcode + sku in one array)

```php
XyloMultiselectTwo::make('items')
    ->options([
        '1' => [
            'label' => 'Cheese Burger 6"',
            'description' => '$7.99',
            'barcode' => '100100',
            'sku' => 'CH-06',
        ],
        '2' => [
            'label' => 'Beef Burger',
            'description' => '$8.99',
            'barcode' => '100200',
        ],
    ]);
```

## Features

- Two-sided transfer UI (available ↔ selected)
- Count badges on both panels
- Barcode scan input (Enter to add)
- Search / filter on both sides (name, SKU, barcode) with **200ms debounce**
- **Scroll windowing** — renders `pageSize` rows at a time (default 80), loads more on scroll
- Prebuilt option list + `Set` membership for fast filtering on large catalogs
- **Dynamic options remount** via `wire:key` (safe for dependent fields like Free Reward ⊆ Products to Buy)
- Item title + subtitle (e.g. price)
- Chevron move actions + remove (X) on selected rows
- **Add all shown** / **Remove all** (Add all only adds currently visible rows)
- Soft accent color only on hover (items + bulk buttons)
- Theme-aware colors: text/borders/surfaces follow Filament gray tokens + light/dark automatically
- Accent: `natural`, Filament colors (`primary`, `success`, …), or any custom CSS color
- Optional Filament `label()` / `helperText()` — nothing forced by the package
- Works with Filament `live()`, `disabled()`, validation, defaults
- English + Arabic translations
- Multiple independent instances on the same form (e.g. Products to Buy + Free Reward)

## Configuration API

| Method                                                                                  | Description                                                                   |
| --------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------- |
| `options()`                                                                             | Available choices                                                             |
| `descriptions()`                                                                        | Subtitle under each label                                                     |
| `barcodes()`                                                                            | Values matched by the scanner                                                 |
| `skus()`                                                                                | Included in search + scan                                                     |
| `selectableLabel()` / `selectedLabel()`                                                 | Panel titles                                                                  |
| `enableSearch()` / `searchable()`                                                       | Toggle search inputs                                                          |
| `enableBarcode()` / `barcodeScanner()`                                                  | Toggle barcode input                                                          |
| `showCounts()`                                                                          | Toggle count badges                                                           |
| `showBulkActions()`                                                                     | Toggle footer buttons                                                         |
| `listHeight(320)`                                                                       | Scroll area height in px                                                      |
| `pageSize(80)`                                                                          | Rows rendered per chunk (scroll loads more); keep low for huge catalogs       |
| `limitTo('other_field')`                                                                | Client-side: only show options selected on another field (no Livewire wait)   |
| `accentColor(...)`                                                                      | `natural`, `primary`/`success`/`danger`/`warning`/`info`/`gray`, or CSS color |
| `hoverColor(...)`                                                                       | Shared hover for items + both buttons                                         |
| `itemsHoverColor(...)`                                                                  | Shared hover for both panels' rows                                            |
| `availableHoverColor(...)` / `selectedHoverColor(...)`                                  | Hover per panel                                                               |
| `buttonsHoverColor(...)`                                                                | Shared hover for Add all + Remove all                                         |
| `addAllHoverColor(...)` / `removeAllHoverColor(...)`                                    | Hover per button                                                              |
| `buttonsBackgroundColor(...)`                                                           | Shared default background for both buttons                                    |
| `addAllBackgroundColor(...)` / `removeAllBackgroundColor(...)`                          | Background per button (defaults kept if unset)                                |
| `addAllLabel()` / `removeAllLabel()`                                                    | Footer button labels                                                          |
| `availableSearchPlaceholder()` / `selectedSearchPlaceholder()` / `barcodePlaceholder()` | Input placeholders                                                            |
| `label()` / `helperText()` / `hiddenLabel()`                                            | Optional title & subtitle (Filament)                                          |

Color priority (most specific wins):

1. Per-part (`availableHoverColor`, `addAllHoverColor`, …)
2. Group (`itemsHoverColor`, `buttonsHoverColor`)
3. Shared (`hoverColor`)
4. Default from `accentColor`

```php
// One color for everything on hover
->hoverColor('#dcfce7')

// Cards together, buttons together
->itemsHoverColor('#ecfdf5')
->buttonsHoverColor('#fef3c7')

// Fully separate
->availableHoverColor('#d1fae5')
->selectedHoverColor('#e0f2fe')
->addAllHoverColor('#bbf7d0')
->removeAllHoverColor('#fecaca')
->addAllBackgroundColor('#f3f4f6')   // optional — default gray if omitted
->removeAllBackgroundColor('#ffffff') // optional — default card white if omitted
```

## Dependent fields (Products to Buy + Free Reward)

Use two separate fields. Keep Free Reward options as a subset of Products to Buy, and prune invalid rewards when qualifying changes:

```php
XyloMultiselectTwo::make('punch_card.qualifying_product_ids')
    ->label('Products to Buy')
    ->live(debounce: 400)
    ->showBulkActions(true)
    ->listHeight(260)
    ->pageSize(80)
    ->accentColor('success')
    ->barcodeScanner(false)
    ->options(fn (): array => $this->productTransferOptions())
    ->afterStateUpdated(function ($state, Set $set, Get $get): void {
        $allowed = array_map('strval', $state ?? []);
        $rewards = array_map('strval', $get('punch_card.reward_product_ids') ?? []);
        $set('punch_card.reward_product_ids', array_values(array_intersect($rewards, $allowed)));
    }),

XyloMultiselectTwo::make('punch_card.reward_product_ids')
    ->label('Free Reward Products')
    ->live(debounce: 400)
    ->showBulkActions(false)
    ->listHeight(220)
    ->pageSize(80)
    ->barcodeScanner(false)
    ->disabled(fn (Get $get): bool => blank($get('punch_card.qualifying_product_ids')))
    ->options(fn (Get $get): array => $this->productTransferOptions(
        array_map('strval', $get('punch_card.qualifying_product_ids') ?? [])
    )),
```

The package remounts Alpine when option keys change (`wire:key`), so the Free Reward list refreshes when Products to Buy changes.

## Example with labels from the screenshots

```php
XyloMultiselectTwo::make('program_items')
    ->selectableLabel('Available items')
    ->selectedLabel('In this program')
    ->availableSearchPlaceholder('Search by name, SKU or barcode')
    ->selectedSearchPlaceholder('Filter included items')
    ->barcodePlaceholder('Scan barcode & press Enter')
    ->addAllLabel('Add all shown')
    ->removeAllLabel('Remove all')
    ->accentColor('#16a34a')
    ->options($items)
    ->descriptions($prices)
    ->barcodes($barcodes);
```

Accent examples:

```php
->accentColor('natural')   // package soft green
->accentColor('primary')   // follows Filament panel primary theme
->accentColor('success')   // Filament success palette
->accentColor('#0ea5e9')   // any hex / CSS color
```

Structural colors (titles, prices, borders, cards, inputs) always use Filament `--gray-*` tokens and switch with light/dark mode — no extra config.

## Version compatibility

| Package | Filament  | PHP        |
| ------- | --------- | ---------- |
| 1.x     | 4.x / 5.x | 8.2 / 8.3 / 8.4 |

Filament 5 keeps the Forms API from 4 and mainly upgrades Livewire; this package targets both.

## Credits

- **Developer:** Qusai Homadi
- **Package:** Xylo (`xylo/multiselect-two`)

## License

MIT
