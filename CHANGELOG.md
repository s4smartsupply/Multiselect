# Changelog

## 1.1.4

- Add all shown now adds every filtered available item, not only the first `pageSize` rendered rows

## 1.1.2

- Add `limitTo()` so a field can follow another field's selected values in the browser (e.g. Free Reward ⊆ Products to Buy) without a Livewire re-render

## 1.1.1

- Build normalized options once per field render (PHP cache + single Blade `@js` payload)
- Memoize Alpine available/selected lists in one pass so search, counts, and clicks do not refilter the catalog each tick

## 1.1.0

- Rename public field class to `XyloMultiselectTwo` (`MultiselectTwo` kept as deprecated alias)
- Scroll windowing for large catalogs (`pageSize`, default 80) on both panels — load more on scroll
- Search inputs use 200ms debounce to avoid refiltering on every keystroke
- Prebuild `optionList` once in Alpine `init()` instead of remapping `Object.entries` on every render
- Replace `wire:ignore` with `wire:key` remount when option keys change — dependent fields (e.g. Free Reward ⊆ Products to Buy) refresh correctly
- Explicit PHP **8.2 / 8.3 / 8.4** support in `composer.json`
- Document Punch Card-style dual-field usage in the README

## 1.0.0

- Initial release for Filament 4.x and 5.x
- Two-sided multiselect with search, barcode scan, descriptions, SKUs
- Natural green accent, count badges, bulk actions
- English and Arabic translations
