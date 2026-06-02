@php
    $wrapperClass ??= 'relative flex-1 min-w-0';
    $disabled ??= 'loading';
    $active ??= false;
@endphp

<div class="{{ $wrapperClass }}">
    <input
        type="text"
        x-model="{{ $slotExpression }}.query"
        @input="locationInput({{ $slotExpression }})"
        @focus="locationFocus({{ $slotExpression }})"
        @blur="locationBlur({{ $slotExpression }})"
        placeholder="{{ $placeholder }}"
        class="input input-sm w-full pr-7"
        autocomplete="off"
        @if ($disabled !== null)
            :disabled="{{ $disabled }}"
        @endif
    >

    <template x-if="{{ $slotExpression }}.uuid">
        <button
            type="button"
            class="btn btn-xs btn-circle btn-ghost absolute right-1 top-1/2 -translate-y-1/2"
            @mousedown.prevent="locationClear({{ $slotExpression }})"
        >&times;</button>
    </template>

    <div
        x-show="{{ $slotExpression }}.showDropdown"
        x-transition
        class="absolute z-50 left-0 right-0 top-full mt-1 bg-base-100 rounded-box border border-base-300 shadow-lg max-h-60 overflow-y-auto"
    >
        <ul class="menu menu-xs w-full">
            <template x-for="e in {{ $slotExpression }}.results" :key="e.uuid">
                <li class="w-full" @if ($active) :class="{ 'active': {{ $slotExpression }}.uuid === e.uuid }" @endif>
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-3 text-left"
                        @mousedown.prevent="locationSelect({{ $slotExpression }}, e)"
                    >
                        <span class="truncate" x-text="e.name"></span>
                        <span class="text-xs opacity-50 shrink-0" x-text="e.meta"></span>
                    </button>
                </li>
            </template>
        </ul>
    </div>
</div>
