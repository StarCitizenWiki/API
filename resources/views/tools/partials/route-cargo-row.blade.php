@php
    $badgeExpression ??= null;
    $removeExpression ??= null;
    $amountExpression ??= null;
    $class ??= '';
    $primaryOptional ??= false;
    $secondaryOptional ??= false;
    $disabled ??= 'loading';
@endphp

<div class="card card-border bg-base-100 sm:border-0 sm:bg-transparent {{ $class }}">
    <div class="card-body gap-2 p-3 sm:flex sm:flex-row sm:items-end sm:p-0">
        @if ($badgeExpression || $removeExpression)
            <div class="flex items-center justify-between sm:pb-2">
                @if ($badgeExpression)
                    <span class="badge badge-ghost badge-sm tabular-nums shrink-0 w-5 justify-center" x-text="{{ $badgeExpression }}"></span>
                @else
                    <span class="hidden sm:block shrink-0 w-5"></span>
                @endif

                @if ($removeExpression)
                    <button
                        type="button"
                        class="btn btn-xs btn-ghost btn-circle text-error/50 hover:text-error sm:hidden"
                        :disabled="cargoMissions.length <= 1"
                        @click="{{ $removeExpression }}"
                    >
                        <i data-lucide="trash-2" class="size-3.5"></i>
                    </button>
                @endif
            </div>
        @else
            <div class="hidden sm:block shrink-0 w-5"></div>
        @endif

        <fieldset class="fieldset flex-1 min-w-0">
            <legend class="fieldset-legend">
                {{ $primaryLegend }}
                @if ($primaryOptional)
                    <span class="badge badge-ghost badge-xs">Optional</span>
                @endif
            </legend>
            @include('tools.partials.route-location-input', [
                'slotExpression' => $primarySlotExpression,
                'placeholder' => $primaryPlaceholder,
                'disabled' => $disabled,
            ])
        </fieldset>

        <span class="hidden sm:block text-muted text-xs shrink-0 pb-2">&rarr;</span>

        <fieldset class="fieldset flex-1 min-w-0">
            <legend class="fieldset-legend">
                {{ $secondaryLegend }}
                @if ($secondaryOptional)
                    <span class="badge badge-ghost badge-xs">Optional</span>
                @endif
            </legend>
            @include('tools.partials.route-location-input', [
                'slotExpression' => $secondarySlotExpression,
                'placeholder' => $secondaryPlaceholder,
                'disabled' => $disabled,
            ])
        </fieldset>

        @if ($amountExpression)
            <fieldset class="fieldset sm:w-20">
                <legend class="fieldset-legend">SCU</legend>
                <input
                    type="number"
                    min="0"
                    step="0.01"
                    placeholder="SCU"
                    x-model="{{ $amountExpression }}"
                    class="input input-sm w-full tabular-nums"
                >
            </fieldset>
        @else
            <div class="hidden sm:block sm:w-20"></div>
        @endif

        @if ($removeExpression)
            <button
                type="button"
                class="hidden sm:inline-flex btn btn-xs btn-ghost btn-circle shrink-0 text-error/50 hover:text-error mb-1"
                :disabled="cargoMissions.length <= 1"
                @click="{{ $removeExpression }}"
            >
                <i data-lucide="trash-2" class="size-3.5"></i>
            </button>
        @else
            <div class="hidden sm:block shrink-0 w-6"></div>
        @endif
    </div>
</div>
