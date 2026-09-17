# Changelog

## 1.3.0

- Add `instantScan()` — resolve the code while typing instead of waiting for Enter. Off by default. While typing, only an unambiguous match is added and misses stay silent, since a half-typed code is not a failed scan; Enter still resolves and reports every outcome. Tune with `instantScanDelay()` (default 120ms) and `instantScanMinLength()` (default 6). The delay is what stops a prefix of a long code from matching another product's shorter barcode mid-burst.
- **`searchOnScanMiss()` now defaults to off.** The result line already says what happened, and overwriting the search box costs the operator the filter they were using. Call `searchOnScanMiss()` to restore the 1.2 behaviour.

## 1.2.0

- **Barcode scanning now resolves real-world codes.** Matching runs in three tiers — exact, separator/case-insensitive, then GTIN-14 — so a 12-digit UPC-A scan finds a 13-digit stored barcode, and spaces or dashes in either value no longer break the lookup. Previously only a byte-for-byte match worked, which silently failed on the leading zeros most catalogs store.
- **A scan never guesses.** When a code matches more than one option nothing is selected; the code is reported and handed to the search box instead. Option values (record ids) are still matched exactly only, so an id can't shadow a barcode.
- **Scans report their outcome** inline under the input (added / already selected / not available / not found / ambiguous), replacing the previous silent failure. Configure with `scanFeedback()` and `scanFeedbackDuration()`, translate via the new `scan.*` keys.
- Unresolved scans populate the available search box so the operator can finish by eye — opt out with `searchOnScanMiss(false)`.
- Search also accepts formatted codes: `0125-4601 1440` now finds a product stored as `012546011440`.
- `barcodeStrict()` restores the pre-1.2 exact-only matching.
- Barcode input keeps focus after a scan and no longer autocorrects or autocapitalises.
- `barcodes()` / `skus()` values are trimmed when normalized; whitespace-only codes become `null`.

All matching happens in the browser against options already on the page — a scanned code is never sent anywhere.

## 1.1.5

- Fix Filament 5 field chrome: stop wrapping the Blade view with `$getFieldWrapperView()` / `field-wrapper.index` (that printed the view name as raw text on some deploys). Use `Field::wrapEmbeddedHtml()` instead.

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
