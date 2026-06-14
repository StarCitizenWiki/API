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

    $grouped = collect($ports)->groupBy(fn ($port) => data_get($port, 'category_label') ?: 'Other');
    $layoutCategories = collect(HardpointCategory::columns())->flatten();

    $columns = collect(HardpointCategory::columns())
        ->map(fn (array $categories) => collect($categories)
            ->mapWithKeys(fn (string $category): array => $grouped->has($category) ? [$category => $grouped[$category]] : [])
            ->filter(fn ($categoryPorts) => count($categoryPorts) > 0))
        ->filter(fn ($column) => $column->isNotEmpty());

    $otherGroups = $grouped
        ->reject(fn ($categoryPorts, string $category): bool => $layoutCategories->contains($category))
        ->filter(fn ($categoryPorts) => count($categoryPorts) > 0);
    $otherCount = $otherGroups->sum(fn ($categoryPorts): int => count($categoryPorts));
@endphp

<div class="col-span-full space-y-4">
    @if($columns->isNotEmpty())
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 xl:grid-cols-3">
            @foreach($columns as $column)
                <div class="space-y-4">
                    @foreach($column as $categoryLabel => $categoryPorts)
                        <div class="card card-border bg-base-100 shadow">
                            <div class="card-body gap-2 p-3">
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-subtle">
                                    {{ $categoryLabel }}
                                    <span class="badge badge-ghost badge-sm ml-1">{{ count($categoryPorts) }}</span>
                                </h4>

                                <div class="space-y-2 max-h-96 overflow-auto">
                                    @foreach($categoryPorts as $port)
                                        <x-port-display :port="$port" :editable="data_get($port, 'editable', false)" :power-pools="$powerPools" :category-index="$loop->index" :vehicle-name="$vehicleName" :vehicle-port-tags="$vehiclePortTags"/>
                                    @endforeach

                                    @if($categoryLabel === 'Quantum Drives')
                                        @php
                                            $qdPort = $categoryPorts->first(fn ($p) => data_get($p, 'type') === 'QuantumDrive');
                                            $qdItem = data_get($qdPort, 'equipped_item') ?? data_get($qdPort, 'equipped_port_item');
                                            $qdSpec = data_get($qdItem, 'quantum_drive');
                                            $qdFuelCapacity = data_get($vehicle, 'quantum.quantum_fuel_capacity');
                                        @endphp
                                        @if($qdSpec)
                                            <x-quantum-drive-travel-calc :quantum-drive="$qdSpec" :fuel-capacity="$qdFuelCapacity"/>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endif

    @if($otherGroups->isNotEmpty())
        <details class="collapse collapse-arrow border border-base-300 bg-base-100 shadow-sm" @if($columns->isEmpty()) open @endif>
            <summary class="collapse-title min-h-10 py-2 text-sm">
                <span class="flex items-center gap-2">
                    <span class="text-subtle">Other hardpoints</span>
                    <span class="badge badge-ghost badge-sm">{{ $otherCount }}</span>
                </span>
            </summary>

            <div class="collapse-content">
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 xl:grid-cols-3">
                    @foreach($otherGroups as $categoryLabel => $categoryPorts)
                        <div class="card card-border bg-base-100 shadow">
                            <div class="card-body gap-2 p-3">
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-subtle">
                                    {{ $categoryLabel }}
                                    <span class="badge badge-ghost badge-sm ml-1">{{ count($categoryPorts) }}</span>
                                </h4>

                                <div class="space-y-2 max-h-96 overflow-auto">
                                    @foreach($categoryPorts as $port)
                                        <x-port-display :port="$port" :editable="data_get($port, 'editable', false)" :power-pools="$powerPools" :category-index="$loop->index" :vehicle-name="$vehicleName" :vehicle-port-tags="$vehiclePortTags"/>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </details>
    @endif
</div>
