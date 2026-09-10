<?php

namespace Xylo\MultiselectTwo\Forms\Components;

use Closure;
use Filament\Forms\Components\Concerns\HasOptions;
use Filament\Forms\Components\Field;
use Illuminate\Contracts\Support\Arrayable;

class XyloMultiselectTwo extends Field
{
    use HasOptions;

    protected string $view = 'xylo-multiselect-two::forms.components.multiselect-two';

    protected string | Closure | null $selectableLabel = null;

    protected string | Closure | null $selectedLabel = null;

    protected string | Closure | null $availableSearchPlaceholder = null;

    protected string | Closure | null $selectedSearchPlaceholder = null;

    protected string | Closure | null $barcodePlaceholder = null;

    protected string | Closure | null $addAllLabel = null;

    protected string | Closure | null $removeAllLabel = null;

    /**
     * @var array<string, string> | Arrayable | Closure | null
     */
    protected array | Arrayable | Closure | null $descriptions = null;

    /**
     * @var array<string, string> | Arrayable | Closure | null
     */
    protected array | Arrayable | Closure | null $barcodes = null;

    /**
     * @var array<string, string> | Arrayable | Closure | null
     */
    protected array | Arrayable | Closure | null $skus = null;

    protected bool | Closure $isSearchable = true;

    protected bool | Closure $hasBarcodeScanner = true;

    protected bool | Closure $showCounts = true;

    protected bool | Closure $showBulkActions = true;

    protected int | Closure | null $listHeight = 320;

    /**
     * How many list rows to render initially / per scroll chunk.
     * Keeps the DOM light when catalogs are large.
     */
    protected int | Closure $pageSize = 80;

    protected string | Closure | null $limitToStatePath = null;

    protected string | Closure $accentColor = 'natural';

    protected string | Closure | null $hoverColor = null;

    protected string | Closure | null $itemsHoverColor = null;

    protected string | Closure | null $availableHoverColor = null;

    protected string | Closure | null $selectedHoverColor = null;

    protected string | Closure | null $buttonsHoverColor = null;

    protected string | Closure | null $addAllHoverColor = null;

    protected string | Closure | null $removeAllHoverColor = null;

    protected string | Closure | null $addAllBackgroundColor = null;

    protected string | Closure | null $removeAllBackgroundColor = null;

    protected string | Closure | null $buttonsBackgroundColor = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);

        $this->selectableLabel(__('xylo-multiselect-two::xylo-multiselect-two.selectable.label'));
        $this->selectedLabel(__('xylo-multiselect-two::xylo-multiselect-two.selected.label'));
        $this->availableSearchPlaceholder(__('xylo-multiselect-two::xylo-multiselect-two.selectable.search_placeholder'));
        $this->selectedSearchPlaceholder(__('xylo-multiselect-two::xylo-multiselect-two.selected.search_placeholder'));
        $this->barcodePlaceholder(__('xylo-multiselect-two::xylo-multiselect-two.selectable.barcode_placeholder'));
        $this->addAllLabel(__('xylo-multiselect-two::xylo-multiselect-two.actions.add_all'));
        $this->removeAllLabel(__('xylo-multiselect-two::xylo-multiselect-two.actions.remove_all'));

        $this->afterStateHydrated(function (XyloMultiselectTwo $component, mixed $state): void {
            if (! is_array($state)) {
                $component->state([]);

                return;
            }

            $component->state(array_values(array_map(
                static fn (mixed $value): string => (string) $value,
                $state
            )));
        });

        $this->dehydrateStateUsing(function (mixed $state): array {
            if (! is_array($state)) {
                return [];
            }

            return array_values(array_unique(array_map(
                static fn (mixed $value): string => (string) $value,
                $state
            )));
        });

        $this->rule('array');
    }

    public function selectableLabel(string | Closure | null $label): static
    {
        $this->selectableLabel = $label;

        return $this;
    }

    public function getSelectableLabel(): string
    {
        return (string) $this->evaluate($this->selectableLabel);
    }

    public function selectedLabel(string | Closure | null $label): static
    {
        $this->selectedLabel = $label;

        return $this;
    }

    public function getSelectedLabel(): string
    {
        return (string) $this->evaluate($this->selectedLabel);
    }

    public function availableSearchPlaceholder(string | Closure | null $placeholder): static
    {
        $this->availableSearchPlaceholder = $placeholder;

        return $this;
    }

    public function getAvailableSearchPlaceholder(): string
    {
        return (string) $this->evaluate($this->availableSearchPlaceholder);
    }

    public function selectedSearchPlaceholder(string | Closure | null $placeholder): static
    {
        $this->selectedSearchPlaceholder = $placeholder;

        return $this;
    }

    public function getSelectedSearchPlaceholder(): string
    {
        return (string) $this->evaluate($this->selectedSearchPlaceholder);
    }

    public function barcodePlaceholder(string | Closure | null $placeholder): static
    {
        $this->barcodePlaceholder = $placeholder;

        return $this;
    }

    public function getBarcodePlaceholder(): string
    {
        return (string) $this->evaluate($this->barcodePlaceholder);
    }

    public function addAllLabel(string | Closure | null $label): static
    {
        $this->addAllLabel = $label;

        return $this;
    }

    public function getAddAllLabel(): string
    {
        return (string) $this->evaluate($this->addAllLabel);
    }

    public function removeAllLabel(string | Closure | null $label): static
    {
        $this->removeAllLabel = $label;

        return $this;
    }

    public function getRemoveAllLabel(): string
    {
        return (string) $this->evaluate($this->removeAllLabel);
    }

    /**
     * @param  array<string, string> | Arrayable | Closure | null  $descriptions
     */
    public function descriptions(array | Arrayable | Closure | null $descriptions): static
    {
        $this->descriptions = $descriptions;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getDescriptions(): array
    {
        $descriptions = $this->evaluate($this->descriptions) ?? [];

        if ($descriptions instanceof Arrayable) {
            $descriptions = $descriptions->toArray();
        }

        return collect($descriptions)
            ->mapWithKeys(static fn (mixed $label, mixed $value): array => [(string) $value => (string) $label])
            ->all();
    }

    /**
     * @param  array<string, string> | Arrayable | Closure | null  $barcodes
     */
    public function barcodes(array | Arrayable | Closure | null $barcodes): static
    {
        $this->barcodes = $barcodes;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getBarcodes(): array
    {
        $barcodes = $this->evaluate($this->barcodes) ?? [];

        if ($barcodes instanceof Arrayable) {
            $barcodes = $barcodes->toArray();
        }

        return collect($barcodes)
            ->mapWithKeys(static fn (mixed $label, mixed $value): array => [(string) $value => (string) $label])
            ->all();
    }

    /**
     * @param  array<string, string> | Arrayable | Closure | null  $skus
     */
    public function skus(array | Arrayable | Closure | null $skus): static
    {
        $this->skus = $skus;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getSkus(): array
    {
        $skus = $this->evaluate($this->skus) ?? [];

        if ($skus instanceof Arrayable) {
            $skus = $skus->toArray();
        }

        return collect($skus)
            ->mapWithKeys(static fn (mixed $label, mixed $value): array => [(string) $value => (string) $label])
            ->all();
    }

    public function searchable(bool | Closure $condition = true): static
    {
        $this->isSearchable = $condition;

        return $this;
    }

    public function enableSearch(bool | Closure $condition = true): static
    {
        return $this->searchable($condition);
    }

    public function isSearchable(): bool
    {
        return (bool) $this->evaluate($this->isSearchable);
    }

    public function barcodeScanner(bool | Closure $condition = true): static
    {
        $this->hasBarcodeScanner = $condition;

        return $this;
    }

    public function enableBarcode(bool | Closure $condition = true): static
    {
        return $this->barcodeScanner($condition);
    }

    public function hasBarcodeScanner(): bool
    {
        return (bool) $this->evaluate($this->hasBarcodeScanner);
    }

    public function showCounts(bool | Closure $condition = true): static
    {
        $this->showCounts = $condition;

        return $this;
    }

    public function shouldShowCounts(): bool
    {
        return (bool) $this->evaluate($this->showCounts);
    }

    public function showBulkActions(bool | Closure $condition = true): static
    {
        $this->showBulkActions = $condition;

        return $this;
    }

    public function shouldShowBulkActions(): bool
    {
        return (bool) $this->evaluate($this->showBulkActions);
    }

    public function listHeight(int | Closure | null $height): static
    {
        $this->listHeight = $height;

        return $this;
    }

    public function getListHeight(): int
    {
        return (int) ($this->evaluate($this->listHeight) ?? 320);
    }

    /**
     * Rows rendered at once in each panel (scroll loads the next chunk).
     * Default 80 — same windowing strategy as the merchant MultiselectTwo.
     */
    public function pageSize(int | Closure $size = 80): static
    {
        $this->pageSize = $size;

        return $this;
    }

    public function getPageSize(): int
    {
        $size = (int) $this->evaluate($this->pageSize);

        return max(1, $size);
    }

    /**
     * Show only options whose values are currently selected on another field.
     * Updates in the browser — no Livewire re-render required.
     */
    public function limitTo(string | Closure | null $statePath): static
    {
        $this->limitToStatePath = $statePath;

        return $this;
    }

    public function getLimitToStatePath(): ?string
    {
        $path = $this->evaluate($this->limitToStatePath);

        if (! filled($path)) {
            return null;
        }

        $path = (string) $path;
        $containerPath = $this->getContainer()->getStatePath();

        if (filled($containerPath) && $path !== $containerPath && ! str_starts_with($path, $containerPath.'.')) {
            return $containerPath.'.'.$path;
        }

        return $path;
    }

    /**
     * Accent theme:
     * - Filament tokens: `primary`, `success`, `danger`, `warning`, `info`, `gray`
     * - Package default: `natural`
     * - Or any CSS color (e.g. `#16a34a`)
     *
     * Structural colors (text, borders, surfaces) always follow the Filament theme
     * and light/dark mode automatically.
     */
    public function accentColor(string | Closure $color = 'natural'): static
    {
        $this->accentColor = $color;

        return $this;
    }

    public function getAccentColor(): string
    {
        return (string) $this->evaluate($this->accentColor);
    }

    /**
     * @return array<int, string>
     */
    public static function getNamedAccentColors(): array
    {
        return ['natural', 'primary', 'success', 'danger', 'warning', 'info', 'gray'];
    }

    public function isNamedAccentColor(): bool
    {
        return in_array($this->getAccentColor(), static::getNamedAccentColors(), true);
    }

    public function getAccentDataAttribute(): string
    {
        return $this->isNamedAccentColor() ? $this->getAccentColor() : 'custom';
    }

    /**
     * Shared hover color for all items + both bulk buttons.
     */
    public function hoverColor(string | Closure | null $color): static
    {
        $this->hoverColor = $color;

        return $this;
    }

    /**
     * Shared hover color for both panels' item rows.
     */
    public function itemsHoverColor(string | Closure | null $color): static
    {
        $this->itemsHoverColor = $color;

        return $this;
    }

    public function availableHoverColor(string | Closure | null $color): static
    {
        $this->availableHoverColor = $color;

        return $this;
    }

    public function selectedHoverColor(string | Closure | null $color): static
    {
        $this->selectedHoverColor = $color;

        return $this;
    }

    /**
     * Shared hover color for Add all + Remove all.
     */
    public function buttonsHoverColor(string | Closure | null $color): static
    {
        $this->buttonsHoverColor = $color;

        return $this;
    }

    public function addAllHoverColor(string | Closure | null $color): static
    {
        $this->addAllHoverColor = $color;

        return $this;
    }

    public function removeAllHoverColor(string | Closure | null $color): static
    {
        $this->removeAllHoverColor = $color;

        return $this;
    }

    /**
     * Shared default background for both bulk buttons.
     * If omitted, each button keeps its own default.
     */
    public function buttonsBackgroundColor(string | Closure | null $color): static
    {
        $this->buttonsBackgroundColor = $color;

        return $this;
    }

    public function addAllBackgroundColor(string | Closure | null $color): static
    {
        $this->addAllBackgroundColor = $color;

        return $this;
    }

    public function removeAllBackgroundColor(string | Closure | null $color): static
    {
        $this->removeAllBackgroundColor = $color;

        return $this;
    }

    protected function evaluateOptionalColor(string | Closure | null $color): ?string
    {
        if ($color === null) {
            return null;
        }

        $value = $this->evaluate($color);

        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    protected function resolveHoverColor(
        string | Closure | null $specific,
        string | Closure | null $group,
        string | Closure | null $global,
    ): ?string {
        return $this->evaluateOptionalColor($specific)
            ?? $this->evaluateOptionalColor($group)
            ?? $this->evaluateOptionalColor($global);
    }

    /**
     * Inline CSS variables for accent + optional per-part colors.
     */
    public function getThemeStyle(): ?string
    {
        $vars = [];

        if (! $this->isNamedAccentColor()) {
            $vars['--xms-accent'] = $this->getAccentColor();
        }

        $availableHover = $this->resolveHoverColor(
            $this->availableHoverColor,
            $this->itemsHoverColor,
            $this->hoverColor,
        );
        $selectedHover = $this->resolveHoverColor(
            $this->selectedHoverColor,
            $this->itemsHoverColor,
            $this->hoverColor,
        );
        $addHover = $this->resolveHoverColor(
            $this->addAllHoverColor,
            $this->buttonsHoverColor,
            $this->hoverColor,
        );
        $removeHover = $this->resolveHoverColor(
            $this->removeAllHoverColor,
            $this->buttonsHoverColor,
            $this->hoverColor,
        );

        if ($availableHover !== null) {
            $vars['--xms-available-hover'] = $availableHover;
        }

        if ($selectedHover !== null) {
            $vars['--xms-selected-hover'] = $selectedHover;
        }

        if ($addHover !== null) {
            $vars['--xms-add-hover'] = $addHover;
            $vars['--xms-add-hover-text'] = "color-mix(in srgb, {$addHover} 70%, black)";
        }

        if ($removeHover !== null) {
            $vars['--xms-remove-hover'] = $removeHover;
            $vars['--xms-remove-hover-text'] = "color-mix(in srgb, {$removeHover} 70%, black)";
        }

        $addBackground = $this->evaluateOptionalColor($this->addAllBackgroundColor)
            ?? $this->evaluateOptionalColor($this->buttonsBackgroundColor);
        $removeBackground = $this->evaluateOptionalColor($this->removeAllBackgroundColor)
            ?? $this->evaluateOptionalColor($this->buttonsBackgroundColor);

        if ($addBackground !== null) {
            $vars['--xms-add-bg'] = $addBackground;
        }

        if ($removeBackground !== null) {
            $vars['--xms-remove-bg'] = $removeBackground;
        }

        if ($vars === []) {
            return null;
        }

        return collect($vars)
            ->map(static fn (string $value, string $key): string => "{$key}: {$value};")
            ->implode(' ');
    }

    /**
     * @deprecated Use getThemeStyle()
     */
    public function getAccentStyle(): ?string
    {
        return $this->getThemeStyle();
    }

    /**
     * @var array<string, array{label: string, description: string|null, barcode: string|null, sku: string|null, search: string}>|null
     */
    protected ?array $normalizedOptionsCache = null;

    /**
     * Normalized options for Alpine: value => [label, description, barcode, sku, search].
     *
     * @return array<string, array{label: string, description: string|null, barcode: string|null, sku: string|null, search: string}>
     */
    public function getNormalizedOptions(): array
    {
        return $this->normalizedOptionsCache ??= $this->buildNormalizedOptions();
    }

    /**
     * @return array<string, array{label: string, description: string|null, barcode: string|null, sku: string|null, search: string}>
     */
    protected function buildNormalizedOptions(): array
    {
        $descriptions = $this->getDescriptions();
        $barcodes = $this->getBarcodes();
        $skus = $this->getSkus();
        $normalized = [];

        foreach ($this->getOptions() as $value => $option) {
            $key = (string) $value;

            if (is_array($option)) {
                $label = (string) ($option['label'] ?? $option['name'] ?? $key);
                $description = isset($option['description']) ? (string) $option['description'] : ($descriptions[$key] ?? null);
                $barcode = isset($option['barcode']) ? (string) $option['barcode'] : ($barcodes[$key] ?? null);
                $sku = isset($option['sku']) ? (string) $option['sku'] : ($skus[$key] ?? null);
            } else {
                $label = (string) $option;
                $description = $descriptions[$key] ?? null;
                $barcode = $barcodes[$key] ?? null;
                $sku = $skus[$key] ?? null;
            }

            $searchParts = array_filter([
                $label,
                $description,
                $barcode,
                $sku,
                $key,
            ]);

            $normalized[$key] = [
                'label' => $label,
                'description' => $description,
                'barcode' => $barcode,
                'sku' => $sku,
                'search' => mb_strtolower(implode(' ', $searchParts)),
            ];
        }

        return $normalized;
    }
}
