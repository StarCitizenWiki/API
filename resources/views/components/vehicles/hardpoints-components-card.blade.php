@php use App\Support\Game\HardpointCategory; @endphp
@props(['vehicle'])

@php
    $ports = data_get($vehicle, 'ports', []);
    $powerPools = data_get($vehicle, 'power_pools', []);
    $vehicleName = data_get($vehicle, 'name');

    if (! is_array($ports) || $ports === []) {
        return;
    }

    $grouped = collect($ports)->groupBy(fn ($port) => data_get($port, 'category_label', 'Other'));

    $primaryCategories = HardpointCategory::primary();
    $collapsedCategories = HardpointCategory::collapsed();
    $knownCategories = array_merge($primaryCategories, $collapsedCategories);

    $primaryPorts = collect();
    $collapsedPorts = collect();

    foreach ($primaryCategories as $cat) {
        if ($grouped->has($cat)) {
            $primaryPorts[$cat] = $grouped[$cat];
        }
    }

    foreach ($collapsedCategories as $cat) {
        if ($grouped->has($cat)) {
            $collapsedPorts[$cat] = $grouped[$cat];
        }
    }

    foreach ($grouped as $cat => $catPorts) {
        if (! in_array($cat, $knownCategories, true)) {
            $collapsedPorts['Other'] = collect($collapsedPorts->get('Other', []))->merge($catPorts);
        }
    }

    $collapsedCount = $collapsedPorts->sum(fn ($p) => count($p));
@endphp

<div class="col-span-full space-y-4">
    @if($primaryPorts->isNotEmpty())
        <div class="space-y-4">
            @foreach($primaryPorts as $categoryLabel => $categoryPorts)
                <div class="space-y-2">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-muted px-2">{{ $categoryLabel }}</h4>
                    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-2">
                        @foreach($categoryPorts as $port)
                            <x-port-display :port="$port" :editable="data_get($port, 'editable', false)"
                                            :power-pools="$powerPools" :category-index="$loop->index"
                                            :vehicle-name="$vehicleName"/>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if($collapsedPorts->isNotEmpty())
        <details class="collapse collapse-arrow border border-base-300 bg-base-100 rounded-lg shadow-sm">
            <summary class="collapse-title min-h-10 py-2 text-sm">
                <span class="flex items-center gap-2">
                    <span class="text-muted">Show All Hardpoints</span>
                    <span class="badge badge-ghost badge-sm">{{ $collapsedCount }} more</span>
                </span>
            </summary>
            <div class="collapse-content space-y-4">
                @foreach($collapsedPorts as $categoryLabel => $categoryPorts)
                    <div class="space-y-2">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-muted px-2">{{ $categoryLabel }}</h4>
                        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-2">
                            @foreach($categoryPorts as $port)
                                <x-port-display :port="$port" :editable="data_get($port, 'editable', false)"
                                                :power-pools="$powerPools" :category-index="$loop->index"
                                                :vehicle-name="$vehicleName"/>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </details>
    @endif
</div>
