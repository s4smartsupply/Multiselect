@php
    $statePath = $getStatePath();
    $isDisabled = $isDisabled();
    $listHeight = $getListHeight();
    $pageSize = $getPageSize();
    $accentData = $getAccentDataAttribute();
    $themeStyle = $getThemeStyle();
    $normalizedOptions = $getNormalizedOptions();
    $optionKeys = implode(',', array_keys($normalizedOptions));
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
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
                this.$watch('availableSearch', () => {
                    this.visibleAvailableCount = this.pageSize
                })
                this.$watch('selectedSearch', () => {
                    this.visibleSelectedCount = this.pageSize
                })
            },

            matchesSearch(option, query) {
                if (! query) {
                    return true
                }

                return option.search.includes(query.trim().toLowerCase())
            },

            ensureLists() {
                const key = (this.state || []).join('\0') + '\n' + this.availableSearch + '\n' + this.selectedSearch

                if (this._listsKey === key) {
                    return
                }

                this._listsKey = key
                this._selectedSet = new Set((this.state || []).map((value) => String(value)))

                const availableQuery = this.availableSearch
                const selectedQuery = this.selectedSearch
                const available = []
                const selected = []
                let availableCount = 0

                for (const item of this.optionList) {
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

                this.availableEntries().forEach((item) => {
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

            scanBarcode() {
                if (this.disabled) {
                    return
                }

                const code = this.barcode.trim().toLowerCase()

                if (! code) {
                    return
                }

                const match = this.optionList.find((option) => {
                    const barcode = (option.barcode || '').toLowerCase()
                    const sku = (option.sku || '').toLowerCase()
                    const key = String(option.value).toLowerCase()

                    return barcode === code || sku === code || key === code
                })

                if (! match) {
                    this.barcode = ''

                    return
                }

                this.select(match.value)
                this.barcode = ''
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
                    @if ($hasBarcodeScanner())
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
                                x-model="barcode"
                                @keydown.enter.prevent="scanBarcode()"
                                placeholder="{{ $getBarcodePlaceholder() }}"
                                @disabled($isDisabled)
                                autocomplete="off"
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
</x-dynamic-component>
