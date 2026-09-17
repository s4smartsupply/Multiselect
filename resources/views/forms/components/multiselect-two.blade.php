@php
    $statePath = $getStatePath();
    $isDisabled = $isDisabled();
    $listHeight = $getListHeight();
    $pageSize = $getPageSize();
    $accentData = $getAccentDataAttribute();
    $themeStyle = $getThemeStyle();
    $normalizedOptions = $getNormalizedOptions();
    $optionKeys = implode(',', array_keys($normalizedOptions));
    $limitToStatePath = $getLimitToStatePath();
    $hasBarcodeScanner = $hasBarcodeScanner();
    $showScanFeedback = $hasBarcodeScanner && $shouldShowScanFeedback();
@endphp

<div
        wire:key="xms-{{ md5($statePath.'|'.$optionKeys) }}"
        class="xms-two"
        data-accent="{{ $accentData }}"
        @if ($themeStyle) style="{{ $themeStyle }}" @endif
        x-data="{
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
            options: @js($normalizedOptions),
            optionList: [],
            availableSearch: '',
            selectedSearch: '',
            barcode: '',
            disabled: @js($isDisabled),
            pageSize: @js($pageSize),
            visibleAvailableCount: @js($pageSize),
            visibleSelectedCount: @js($pageSize),
            limitPath: @js($limitToStatePath),
            limitState: @js($limitToStatePath) ? $wire.$entangle(@js($limitToStatePath), true) : [],
            barcodeStrict: @js($isBarcodeStrict()),
            searchOnScanMiss: @js($shouldSearchOnScanMiss()),
            scanFeedbackDuration: @js($showScanFeedback ? $getScanFeedbackDuration() : 0),
            scanMessages: @js($showScanFeedback ? $getScanMessages() : []),
            scanStatus: { type: '', message: '' },
            _scanTimeout: null,
            _searchCameFromScan: false,
            _optionsByValue: null,
            _scanExact: null,
            _scanLoose: null,
            _scanGtin: null,
            _listsKey: '',
            _selectedSet: null,
            _filteredAvailable: [],
            _filteredSelected: [],
            _availableCount: 0,

            init() {
                if (! Array.isArray(this.state)) {
                    this.state = []
                }

                this.state = this.state.map((value) => String(value))
                this.optionList = Object.entries(this.options).map(([value, option]) => ({
                    value: String(value),
                    ...option,
                }))
                this.buildScanIndex()
                this.$watch('availableSearch', () => {
                    this.visibleAvailableCount = this.pageSize
                })
                this.$watch('selectedSearch', () => {
                    this.visibleSelectedCount = this.pageSize
                })
                if (this.limitPath) {
                    this.$watch('limitState', () => {
                        this._listsKey = ''
                        const allowed = new Set((this.limitState || []).map((value) => String(value)))
                        this.state = (this.state || []).filter((value) => allowed.has(String(value)))
                    })
                }
            },

            destroy() {
                this.clearScanTimeout()
            },

            /* Strip case and every separator a label or a keyboard-wedge scanner may add. */
            normalizeCode(value) {
                return String(value ?? '').toLowerCase().replace(/[^a-z0-9]+/g, '')
            },

            /*
             * GTIN-14 form of a numeric code. UPC-E/UPC-A/EAN-13/GTIN-14 all denote the
             * same article once left-padded to 14 digits, so this is what makes a 12-digit
             * scan match a 13-digit stored barcode. Non-numeric codes have no GTIN form.
             */
            gtinKey(code) {
                return /^[0-9]{6,14}$/.test(code) ? code.padStart(14, '0') : ''
            },

            /*
             * Three lookup tiers, each mapping a key to every option that claims it.
             * Buckets (not single values) are what lets scanBarcode() refuse to guess
             * when a code is ambiguous.
             */
            buildScanIndex() {
                const exact = new Map()
                const loose = new Map()
                const gtin = new Map()
                const byValue = new Map()

                const add = (map, key, value) => {
                    if (! key) {
                        return
                    }

                    const bucket = map.get(key)

                    if (! bucket) {
                        map.set(key, [value])
                    } else if (! bucket.includes(value)) {
                        bucket.push(value)
                    }
                }

                for (const item of this.optionList) {
                    byValue.set(item.value, item)

                    // An option value (record id) is only ever matched exactly, never through
                    // a relaxed tier, so an id can never shadow a real barcode.
                    add(exact, item.value.trim().toLowerCase(), item.value)

                    const codes = []

                    for (const candidate of [item.barcode, item.sku]) {
                        if (candidate === null || candidate === undefined || candidate === '') {
                            continue
                        }

                        add(exact, String(candidate).trim().toLowerCase(), item.value)

                        const normal = this.normalizeCode(candidate)

                        if (! normal) {
                            continue
                        }

                        const padded = this.gtinKey(normal)
                        codes.push(normal)

                        if (padded) {
                            codes.push(padded)
                        }

                        if (this.barcodeStrict) {
                            continue
                        }

                        add(loose, normal, item.value)
                        add(gtin, padded, item.value)
                    }

                    item.codeSearch = codes.join(' ')
                }

                this._optionsByValue = byValue
                this._scanExact = exact
                this._scanLoose = loose
                this._scanGtin = gtin
            },

            findScanMatches(raw) {
                const exact = this._scanExact.get(raw.trim().toLowerCase())

                if (exact) {
                    return exact
                }

                if (this.barcodeStrict) {
                    return []
                }

                const normal = this.normalizeCode(raw)

                if (! normal) {
                    return []
                }

                return this._scanLoose.get(normal)
                    ?? this._scanGtin.get(this.gtinKey(normal))
                    ?? []
            },

            searchNeedle(query) {
                const raw = (query || '').trim().toLowerCase()

                if (! raw) {
                    return null
                }

                const code = this.normalizeCode(raw)

                return { raw: raw, code: code.length >= 3 ? code : '' }
            },

            matchesSearch(option, needle) {
                if (! needle) {
                    return true
                }

                if (option.search.includes(needle.raw)) {
                    return true
                }

                return needle.code !== '' && option.codeSearch !== '' && option.codeSearch.includes(needle.code)
            },

            allowedSet() {
                if (! this.limitPath) {
                    return null
                }

                return new Set((this.limitState || []).map((value) => String(value)))
            },

            ensureLists() {
                const limitKey = (this.limitState || []).join('\0')
                const key = (this.state || []).join('\0') + '\n' + this.availableSearch + '\n' + this.selectedSearch + '\n' + limitKey

                if (this._listsKey === key) {
                    return
                }

                this._listsKey = key
                this._selectedSet = new Set((this.state || []).map((value) => String(value)))

                const allowed = this.allowedSet()
                const availableQuery = this.searchNeedle(this.availableSearch)
                const selectedQuery = this.searchNeedle(this.selectedSearch)
                const available = []
                const selected = []
                let availableCount = 0

                for (const item of this.optionList) {
                    if (allowed && ! allowed.has(item.value)) {
                        continue
                    }

                    if (this._selectedSet.has(item.value)) {
                        if (this.matchesSearch(item, selectedQuery)) {
                            selected.push(item)
                        }

                        continue
                    }

                    availableCount++

                    if (this.matchesSearch(item, availableQuery)) {
                        available.push(item)
                    }
                }

                this._filteredAvailable = available
                this._filteredSelected = selected
                this._availableCount = availableCount
            },

            selectedSet() {
                this.ensureLists()

                return this._selectedSet
            },

            filteredAvailable() {
                this.ensureLists()

                return this._filteredAvailable
            },

            filteredSelected() {
                this.ensureLists()

                return this._filteredSelected
            },

            availableEntries() {
                return this.filteredAvailable().slice(0, this.visibleAvailableCount)
            },

            selectedEntries() {
                return this.filteredSelected().slice(0, this.visibleSelectedCount)
            },

            loadMoreAvailable(event) {
                const el = event.target

                if (el.scrollTop + el.clientHeight < el.scrollHeight - 64) {
                    return
                }

                if (this.visibleAvailableCount < this.filteredAvailable().length) {
                    this.visibleAvailableCount += this.pageSize
                }
            },

            loadMoreSelected(event) {
                const el = event.target

                if (el.scrollTop + el.clientHeight < el.scrollHeight - 64) {
                    return
                }

                if (this.visibleSelectedCount < this.filteredSelected().length) {
                    this.visibleSelectedCount += this.pageSize
                }
            },

            availableCount() {
                this.ensureLists()

                return this._availableCount
            },

            selectedCount() {
                return this.selectedSet().size
            },

            select(value) {
                if (this.disabled) {
                    return
                }

                value = String(value)
                const allowed = this.allowedSet()

                if (allowed && ! allowed.has(value)) {
                    return
                }

                const next = [...this.selectedSet()]

                if (next.includes(value)) {
                    return
                }

                next.push(value)
                this.state = next
            },

            unselect(value) {
                if (this.disabled) {
                    return
                }

                value = String(value)
                this.state = [...this.selectedSet()].filter((item) => item !== value)
            },

            selectAllShown() {
                if (this.disabled) {
                    return
                }

                const next = this.selectedSet()

                this.filteredAvailable().forEach((item) => {
                    next.add(item.value)
                })

                this.state = [...next]
            },

            removeAll() {
                if (this.disabled) {
                    return
                }

                this.state = []
            },

            clearScanTimeout() {
                if (this._scanTimeout) {
                    clearTimeout(this._scanTimeout)
                    this._scanTimeout = null
                }
            },

            setScanStatus(type, replacements) {
                this.clearScanTimeout()

                const template = this.scanMessages[type]

                if (! template) {
                    this.scanStatus = { type: '', message: '' }

                    return
                }

                this.scanStatus = {
                    type: type,
                    message: Object.entries(replacements).reduce(
                        (carry, [token, replacement]) => carry.replaceAll(':' + token, replacement),
                        template
                    ),
                }

                if (this.scanFeedbackDuration > 0) {
                    this._scanTimeout = setTimeout(() => {
                        this.scanStatus = { type: '', message: '' }
                        this._scanTimeout = null
                    }, this.scanFeedbackDuration)
                }
            },

            /* Hand an unresolved code over to the search box so the operator can finish by eye. */
            handOverToSearch(code) {
                if (! this.searchOnScanMiss) {
                    return
                }

                this.availableSearch = code
                this._searchCameFromScan = true
            },

            clearHandedOverSearch() {
                if (! this._searchCameFromScan) {
                    return
                }

                this.availableSearch = ''
                this._searchCameFromScan = false
            },

            /*
             * Resolves a scanned code entirely in the browser — no request is made and the
             * code is never sent anywhere. Adds an option only when exactly one matches;
             * every other outcome reports back instead of guessing.
             */
            scanBarcode() {
                if (this.disabled) {
                    return
                }

                const code = this.barcode.trim()

                if (! code) {
                    return
                }

                this.barcode = ''
                this.$refs.barcodeInput?.focus()

                const matches = this.findScanMatches(code)

                if (matches.length === 0) {
                    this.setScanStatus('not_found', { code: code })
                    this.handOverToSearch(code)

                    return
                }

                if (matches.length > 1) {
                    this.setScanStatus('ambiguous', { code: code })
                    this.handOverToSearch(code)

                    return
                }

                const value = matches[0]
                const label = this._optionsByValue.get(value)?.label ?? value
                const allowed = this.allowedSet()

                if (allowed && ! allowed.has(value)) {
                    this.setScanStatus('not_allowed', { label: label, code: code })

                    return
                }

                if (this.selectedSet().has(value)) {
                    this.setScanStatus('already_added', { label: label, code: code })
                    this.clearHandedOverSearch()

                    return
                }

                this.select(value)
                this.setScanStatus('added', { label: label, code: code })
                this.clearHandedOverSearch()
            },
        }"
    >
        <div class="xms-two__grid">
            {{-- Available panel --}}
            <section class="xms-two__panel xms-two__panel--available">
                <header class="xms-two__header">
                    <h3 class="xms-two__title">{{ $getSelectableLabel() }}</h3>
                    @if ($shouldShowCounts())
                        <span class="xms-two__badge" x-text="availableCount()"></span>
                    @endif
                </header>

                <div class="xms-two__controls">
                    @if ($hasBarcodeScanner)
                        <label class="xms-two__input-wrap xms-two__input-wrap--barcode">
                            <span class="xms-two__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2"/>
                                    <path stroke-linecap="round" d="M7 8v8M10 8v8M13 8v5M16 8v8"/>
                                </svg>
                            </span>
                            <input
                                type="text"
                                class="xms-two__input"
                                x-ref="barcodeInput"
                                x-model="barcode"
                                @keydown.enter.prevent.stop="scanBarcode()"
                                placeholder="{{ $getBarcodePlaceholder() }}"
                                @disabled($isDisabled)
                                autocomplete="off"
                                autocorrect="off"
                                autocapitalize="off"
                                spellcheck="false"
                                inputmode="text"
                            />
                        </label>
                    @endif

                    @if ($isSearchable())
                        <label class="xms-two__input-wrap">
                            <span class="xms-two__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="11" cy="11" r="7"/>
                                    <path stroke-linecap="round" d="m20 20-3.5-3.5"/>
                                </svg>
                            </span>
                            <input
                                type="search"
                                class="xms-two__input"
                                x-model.debounce.200ms="availableSearch"
                                placeholder="{{ $getAvailableSearchPlaceholder() }}"
                                @disabled($isDisabled)
                                autocomplete="off"
                            />
                        </label>
                    @endif

                    @if ($showScanFeedback)
                        <p
                            class="xms-two__scan-status"
                            :class="scanStatus.type ? 'xms-two__scan-status--' + scanStatus.type : null"
                            x-show="scanStatus.message"
                            x-cloak
                            x-text="scanStatus.message"
                            role="status"
                            aria-live="polite"
                        ></p>
                    @endif
                </div>

                <ul
                    class="xms-two__list"
                    style="--xms-list-height: {{ $listHeight }}px"
                    role="listbox"
                    aria-label="{{ $getSelectableLabel() }}"
                    @scroll.passive="loadMoreAvailable($event)"
                >
                    <template x-for="item in availableEntries()" :key="'available-' + item.value">
                        <li class="xms-two__item">
                            <button
                                type="button"
                                class="xms-two__item-button"
                                @click="select(item.value)"
                                :disabled="disabled"
                            >
                                <span class="xms-two__item-body">
                                    <span class="xms-two__item-label" x-text="item.label"></span>
                                    <span
                                        class="xms-two__item-description"
                                        x-show="item.description"
                                        x-text="item.description"
                                    ></span>
                                </span>
                                <span class="xms-two__item-action" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6"/>
                                    </svg>
                                </span>
                            </button>
                        </li>
                    </template>

                    <li class="xms-two__empty" x-show="availableEntries().length === 0">
                        {{ __('xylo-multiselect-two::xylo-multiselect-two.empty.available') }}
                    </li>
                </ul>

                @if ($shouldShowBulkActions())
                    <div class="xms-two__footer">
                        <button
                            type="button"
                            class="xms-two__bulk xms-two__bulk--add"
                            @click="selectAllShown()"
                            :disabled="disabled || availableEntries().length === 0"
                        >
                            {{ $getAddAllLabel() }}
                        </button>
                    </div>
                @endif
            </section>

            {{-- Selected panel --}}
            <section class="xms-two__panel xms-two__panel--selected">
                <header class="xms-two__header">
                    <h3 class="xms-two__title">{{ $getSelectedLabel() }}</h3>
                    @if ($shouldShowCounts())
                        <span class="xms-two__badge" x-text="selectedCount()"></span>
                    @endif
                </header>

                <div class="xms-two__controls">
                    @if ($isSearchable())
                        <label class="xms-two__input-wrap">
                            <span class="xms-two__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="11" cy="11" r="7"/>
                                    <path stroke-linecap="round" d="m20 20-3.5-3.5"/>
                                </svg>
                            </span>
                            <input
                                type="search"
                                class="xms-two__input"
                                x-model.debounce.200ms="selectedSearch"
                                placeholder="{{ $getSelectedSearchPlaceholder() }}"
                                @disabled($isDisabled)
                                autocomplete="off"
                            />
                        </label>
                    @endif
                </div>

                <ul
                    class="xms-two__list"
                    style="--xms-list-height: {{ $listHeight }}px"
                    role="listbox"
                    aria-label="{{ $getSelectedLabel() }}"
                    @scroll.passive="loadMoreSelected($event)"
                >
                    <template x-for="item in selectedEntries()" :key="'selected-' + item.value">
                        <li class="xms-two__item">
                            <div class="xms-two__item-row">
                                <button
                                    type="button"
                                    class="xms-two__icon-button"
                                    @click="unselect(item.value)"
                                    :disabled="disabled"
                                    title="{{ __('xylo-multiselect-two::xylo-multiselect-two.actions.move_back') }}"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m15 6-6 6 6 6"/>
                                    </svg>
                                </button>

                                <button
                                    type="button"
                                    class="xms-two__item-button xms-two__item-button--grow"
                                    @click="unselect(item.value)"
                                    :disabled="disabled"
                                >
                                    <span class="xms-two__item-body">
                                        <span class="xms-two__item-label" x-text="item.label"></span>
                                        <span
                                            class="xms-two__item-description"
                                            x-show="item.description"
                                            x-text="item.description"
                                        ></span>
                                    </span>
                                </button>

                                <button
                                    type="button"
                                    class="xms-two__icon-button xms-two__icon-button--remove"
                                    @click="unselect(item.value)"
                                    :disabled="disabled"
                                    title="{{ __('xylo-multiselect-two::xylo-multiselect-two.actions.remove') }}"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/>
                                    </svg>
                                </button>
                            </div>
                        </li>
                    </template>

                    <li class="xms-two__empty" x-show="selectedEntries().length === 0">
                        {{ __('xylo-multiselect-two::xylo-multiselect-two.empty.selected') }}
                    </li>
                </ul>

                @if ($shouldShowBulkActions())
                    <div class="xms-two__footer">
                        <button
                            type="button"
                            class="xms-two__bulk xms-two__bulk--remove"
                            @click="removeAll()"
                            :disabled="disabled || selectedCount() === 0"
                        >
                            {{ $getRemoveAllLabel() }}
                        </button>
                    </div>
                @endif
            </section>
        </div>
</div>
