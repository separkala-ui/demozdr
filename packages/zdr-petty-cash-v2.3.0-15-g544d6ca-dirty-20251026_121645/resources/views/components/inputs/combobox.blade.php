@props([
    'name',
    'label' => '',
    'placeholder' => __('Search...'),
    'options' => [],
    'selected' => [],
    'multiple' => false,
    'searchable' => false,
    'required' => false,
    'class' => '',
    'disabled' => false,
    'queryParam' => null,
    'refreshPage' => false,
    'onchange' => null,
])

@php
    // if collection, convert to array.
    if ($options instanceof \Illuminate\Support\Collection) {
        $options = $options->toArray();
    }

    $selectedValues = is_array($selected) ? $selected : [$selected];
    $selectedValues = array_filter($selectedValues, fn($val) => !empty($val));

    // Normalize options to array of objects with 'value', 'label', and optional 'description'.
    $normalizedOptions = [];
    if (!empty($options)) {
        if (array_is_list($options)) {
            foreach ($options as $opt) {
                if (is_array($opt) && isset($opt['value']) && isset($opt['label'])) {
                    $normalizedOptions[] = [
                        'value' => $opt['value'],
                        'label' => $opt['label'],
                        'description' => $opt['description'] ?? null
                    ];
                } else {
                    $normalizedOptions[] = ['value' => $opt, 'label' => $opt, 'description' => null];
                }
            }
        } else {
            foreach ($options as $key => $lbl) {
                $normalizedOptions[] = ['value' => $key, 'label' => $lbl, 'description' => null];
            }
        }
    }
@endphp

<div x-data="comboboxData({
    allOptions: {{ json_encode($normalizedOptions) }} ?? [],
    options: {{ json_encode($normalizedOptions) }} ?? [],
    selectedOptions: {{ json_encode($selectedValues) }},
    selectedOption: {{ $multiple ? 'null' : json_encode($selectedValues[0] ?? null) }},
    multiple: {{ $multiple ? 'true' : 'false' }},
    searchable: {{ $searchable ? 'true' : 'false' }},
    queryParam: '{{ $queryParam }}',
    refreshPage: {{ $refreshPage ? 'true' : 'false' }},
    placeholder: '{{ __($placeholder) }}',
    name: '{{ $name }}',
    onchange: @json($onchange)
})"
    class="w-full flex flex-col gap-1 {{ $class }}"
    x-on:keydown.esc.window="isOpen = false, openedWithKeyboard = false"
    {{ $attributes->whereStartsWith('x-on:') }}>

    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ __($label) }}

            @if($required)
                <span class="crm:text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <!-- Trigger button -->
        <button type="button"
            role="combobox"
            class="form-control-combobox"
            x-on:click="isOpen = !isOpen; if (searchable && (isOpen || openedWithKeyboard)) { $nextTick(() => $refs.searchField && $refs.searchField.focus()); }"
            x-on:keydown.down.prevent="openedWithKeyboard = true; if (searchable && (isOpen || openedWithKeyboard)) { $nextTick(() => $refs.searchField && $refs.searchField.focus()); }"
            x-on:keydown.enter.prevent="openedWithKeyboard = true; if (searchable && (isOpen || openedWithKeyboard)) { $nextTick(() => $refs.searchField && $refs.searchField.focus()); }"
            x-on:keydown.space.prevent="openedWithKeyboard = true; if (searchable && (isOpen || openedWithKeyboard)) { $nextTick(() => $refs.searchField && $refs.searchField.focus()); }"
            x-bind:aria-expanded="isOpen || openedWithKeyboard"
            @if($disabled) disabled @endif>
            <span class="text-sm font-normal text-left truncate" x-text="setLabelText()"></span>
            <iconify-icon
                icon="mdi:chevron-down"
                class="text-2xl"
                :class="(isOpen || openedWithKeyboard) ? 'text-gray-400 dark:text-gray-300 rotate-180 transition-transform duration-200' : 'text-gray-400 dark:text-gray-300 transition-transform duration-200'"
            ></iconify-icon>
        </button>

        <!-- Hidden input -->
        <template x-if="multiple">
            <div>
                <template x-for="(value, index) in selectedOptions" x-bind:key="index">
                    <input type="hidden" x-bind:name="'{{ str_replace('[]', '', $name) }}[' + index + ']'" x-bind:value="value" />
                </template>
            </div>
        </template>

        <input x-show="!multiple"
            name="{{ $name }}"
            type="hidden"
            x-ref="hiddenTextField"
            x-bind:value="selectedOption"
            @if($required) required @endif />

        <div
            x-cloak
            x-show="isOpen || openedWithKeyboard"
            class="absolute z-50 left-0 top-full mt-1 w-full overflow-hidden rounded-md border border-gray-300 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900"
            @click.outside="isOpen = false; openedWithKeyboard = false;"
            x-on:keydown.down.prevent="$focus.wrap().next()"
            x-on:keydown.up.prevent="$focus.wrap().previous()"
            x-transition
            x-trap="openedWithKeyboard"
        >

            @if($searchable)
            <div class="border-b border-gray-200 dark:border-gray-700 p-2">
                <input type="text"
                    autofocus
                    class="form-control"
                    placeholder="{{ __('Search...') }}"
                    x-model="searchQuery"
                    x-on:input="getFilteredOptions(searchQuery)"
                    x-ref="searchField" />
            </div>
            @endif

            <!-- Options list -->
            <ul class="max-h-60 overflow-y-auto py-1">
                <template x-for="(item, index) in options" x-bind:key="item.value">
                    @if($multiple)
                    <li role="option">
                        <label class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-white/90 dark:hover:bg-gray-800 cursor-pointer"
                            x-bind:for="'option_' + index"
                            x-on:click.prevent="handleOptionToggle(item.value, !selectedOptions.includes(item.value))">
                            <input type="checkbox"
                                class="form-checkbox combobox-option h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary dark:border-gray-700 dark:bg-gray-900"
                                x-bind:value="item.value"
                                x-bind:id="'option_' + index"
                                x-bind:checked="selectedOptions.includes(item.value)"
                                x-on:change="handleOptionToggle(item.value, $el.checked)"
                                tabindex="0" />
                            <div class="flex flex-col">
                                <span x-bind:class="selectedOption == item.value ? 'font-medium' : ''" x-text="item.label"></span>
                                <span x-show="item.description" class="block text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="item.description"></span>
                            </div>
                        </label>
                    </li>
                    @else
                    <li class="combobox-option px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-white/90 dark:hover:bg-gray-800 cursor-pointer flex items-center justify-between"
                        role="option"
                        x-on:click="setSelectedOption(item)"
                        x-on:keydown.enter="setSelectedOption(item)"
                        x-bind:id="'option_' + index"
                        tabindex="0">
                        <div class="flex flex-col">
                            <span x-bind:class="selectedOption == item.value ? 'font-medium' : ''" x-text="item.label"></span>
                            <span x-show="item.description" class="block text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="item.description"></span>
                        </div>
                        <svg x-cloak x-show="selectedOption == item.value" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2" class="size-4 text-primary">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                        </svg>
                    </li>
                    @endif
                </template>

                <li x-show="options.length === 0" class="px-4 py-2 text-sm text-gray-500 dark:text-gray-300">
                    {{ __('No options found') }}
                </li>
            </ul>
        </div>
    </div>
</div>
<script>
function comboboxData({
    allOptions = [],
    options = [],
    selectedOptions = [],
    selectedOption = null,
    multiple = false,
    searchable = false,
    queryParam = null,
    refreshPage = false,
    placeholder = 'Please Select',
    name = '',
    onchange = null
}) {
    return {
        allOptions,
        options,
        isOpen: false,
        openedWithKeyboard: false,
        selectedOptions,
        selectedOption,
        multiple,
        searchable,
        queryParam,
        refreshPage,
        searchQuery: '',
        setLabelText() {
            const findOption = (value) => {
                if (!this.allOptions) return null;
                if (Array.isArray(this.allOptions)) {
                    return this.allOptions.find(opt => opt.value == value);
                } else {
                    const optionsArray = Object.keys(this.allOptions)
                        .map(key => ({
                            value: key + '',
                            label: this.allOptions[key]?.label ?? placeholder
                        }));
                    return optionsArray.find(opt => opt.value == value);
                }
            };
            if (this.multiple) {
                const count = this.selectedOptions.length;
                if (count === 0) return placeholder;
                if (count === 1) {
                    const option = findOption(this.selectedOptions[0]);
                    return option ? option.label : this.selectedOptions[0];
                }
                return count + ' items selected';
            } else {
                if (!this.selectedOption) return placeholder;
                const option = findOption(this.selectedOption);
                return option?.label ?? this.selectedOption ?? placeholder;
            }
        },
        setSelectedOption(option) {
            if (this.multiple) {
                return;
            } else {
                this.selectedOption = option.value + '';
                this.isOpen = false;
                this.openedWithKeyboard = false;
                this.$refs.hiddenTextField.value = option.value;
                if (this.queryParam) {
                    this.updateUrlParam(this.queryParam, option.value);
                }
                if (onchange && typeof window[onchange] === 'function') {
                    window[onchange]();
                }
                const event = new CustomEvent('combobox-change', {
                    detail: {
                        name: name,
                        value: option.value,
                        option: option
                    },
                    bubbles: true
                });
                this.$el.dispatchEvent(event);
            }
        },
        handleOptionToggle(optionValue, checked) {
            if (checked) {
                if (!this.selectedOptions.includes(optionValue)) {
                    this.selectedOptions.push(optionValue);
                }
            } else {
                this.selectedOptions = this.selectedOptions.filter(val => val !== optionValue);
            }
            if (this.queryParam) {
                this.updateUrlParam(this.queryParam, this.selectedOptions.join(','));
            }
            if (onchange && typeof window[onchange] === 'function') {
                window[onchange]();
            }
            const option = this.allOptions.find(opt => opt.value == optionValue);
            if (option) {
                const event = new CustomEvent('combobox-change', {
                    detail: {
                        name: name,
                        value: this.selectedOptions,
                        option: option,
                        allSelected: this.selectedOptions
                    },
                    bubbles: true
                });
                this.$el.dispatchEvent(event);
            }
        },
        getFilteredOptions(query) {
            console.log('query', query);
            if (!this.searchable || !query) {
                this.options = this.allOptions;
            } else {
                this.options = this.allOptions.filter(option =>
                    option.label.toLowerCase().includes(query.toLowerCase())
                );
            }
        },
        updateUrlParam(param, value) {
            if (!param) return;
            const url = new URL(window.location.href);
            if (value && value !== '') {
                url.searchParams.set(param, value);
            } else {
                url.searchParams.delete(param);
            }
            if (this.refreshPage) {
                window.location.href = url.toString();
            } else {
                window.history.pushState({}, '', url.toString());
            }
        },
        init() {
            if (this.queryParam) {
                const url = new URL(window.location.href);
                const paramValue = url.searchParams.get(this.queryParam);
                if (paramValue) {
                    if (this.multiple) {
                        this.selectedOptions = paramValue.split(',');
                    } else {
                        this.selectedOption = paramValue;
                        this.$refs.hiddenTextField.value = paramValue;
                    }
                }
            }
        }
    };
}
</script>
