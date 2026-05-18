@php use App\Support\Game\HardpointCategory; @endphp
@props(['vehicle'])

@php
    $ports = data_get($vehicle, 'ports', []);
    $powerPools = data_get($vehicle, 'power_pools', []);
    $vehicleName = data_get($vehicle, 'name');
    $vehiclePortTags = data_get($vehicle, 'port_tags', []);

    if (! is_array($ports) || $ports === []) {
        return;
    }

    $grouped = collect($ports)->groupBy(fn ($port) => data_get($port, 'category_label', 'Other'));

    $primaryCategories = HardpointCategory::primary();
    $collapsedCategories = HardpointCategory::collapsed();
    $knownCategories = array_merge($primaryCategories, $collapsedCategories);
    $columnDefinitions = HardpointCategory::columns();

    // Build column data: only categories that have ports
    $columns = collect();
    foreach ($columnDefinitions as $colNumber => $colCategories) {
        $colPorts = collect();
        foreach ($colCategories as $cat) {
            if ($grouped->has($cat)) {
                $colPorts[$cat] = $grouped[$cat];
            }
        }
        if ($colPorts->isNotEmpty()) {
            $columns[$colNumber] = $colPorts;
        }
    }

    // Categories assigned to columns
    $assignedCategories = collect($columnDefinitions)->flatten()->all();

    // Remaining collapsed/unassigned categories
    $collapsedPorts = collect();
    foreach ($collapsedCategories as $cat) {
        if ($grouped->has($cat) && ! in_array($cat, $assignedCategories, true)) {
            $collapsedPorts[$cat] = $grouped[$cat];
        }
    }
    foreach ($grouped as $cat => $catPorts) {
        if (! in_array($cat, $knownCategories, true)) {
            $collapsedPorts['Other'] = collect($collapsedPorts->get('Other', []))->merge($catPorts);
        }
    }

    $collapsedCount = $collapsedPorts->sum(fn ($p) => count($p));
    $hasColumns = $columns->isNotEmpty();
@endphp

<div class="col-span-full space-y-4">
    @if($hasColumns)
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($columns as $colNumber => $colPorts)
                <div class="space-y-4">
                    @foreach($colPorts as $categoryLabel => $categoryPorts)
                        <div class="card card-border bg-base-100 shadow">
                            <div class="card-body gap-2 p-3">
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-muted">
                                    {{ $categoryLabel }}
                                    <span class="badge badge-ghost badge-sm ml-1">{{ count($categoryPorts) }}</span>
                                </h4>
                                <div class="space-y-2 max-h-96 overflow-auto">
                                    @foreach($categoryPorts as $port)
                                        <x-port-display :port="$port" :editable="data_get($port, 'editable', false)"
                                                        :power-pools="$powerPools" :category-index="$loop->index"
                                                        :vehicle-name="$vehicleName" :vehicle-port-tags="$vehiclePortTags"/>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
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
            <div class="collapse-content space-y-4 max-h-96 overflow-y-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
                @foreach($collapsedPorts as $categoryLabel => $categoryPorts)
                    <div class="card card-border bg-base-100 shadow">
                        <div class="card-body gap-2 p-3">
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-muted">
                                {{ $categoryLabel }}
                                <span class="badge badge-ghost badge-sm ml-1">{{ count($categoryPorts) }}</span>
                            </h4>
                            <div class="space-y-2 max-h-96 overflow-auto">
                                @foreach($categoryPorts as $port)
                                    <x-port-display :port="$port" :editable="data_get($port, 'editable', false)"
                                                    :power-pools="$powerPools" :category-index="$loop->index"
                                                    :vehicle-name="$vehicleName" :vehicle-port-tags="$vehiclePortTags"/>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </details>
    @endif
</div>
