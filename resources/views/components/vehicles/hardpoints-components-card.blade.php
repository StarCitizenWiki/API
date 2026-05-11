@php use App\Support\Game\EquipmentCategoryOrder; @endphp
@props(['vehicle'])

@php
    $ports = data_get($vehicle, 'ports', []);
    $hardpoints = data_get($vehicle, 'hardpoints', []);
    $powerPools = data_get($vehicle, 'power_pools', []);

    $portEntries = is_array($ports) && $ports !== [] ? $ports : (is_array($hardpoints) ? $hardpoints : []);
    $portsCount = count($portEntries);

    $portEntries = collect($portEntries)->groupBy('category_label');
    $order = EquipmentCategoryOrder::all();
@endphp

<details class="collapse collapse-arrow border border-base-300 bg-base-100 shadow col-span-full">
    <summary class="collapse-title min-h-11 py-3 font-semibold">
        <span class="flex items-center gap-2">
            <span>Hardpoints</span>
            @if ($portsCount > 0)
                <span class="badge badge-soft text-xs">{{ $portsCount }}</span>
            @endif
        </span>
    </summary>

    <div class="collapse-content max-h-96 overflow-y-auto">
        <!-- Ports & Hardpoints -->
        @if ($portEntries->isNotEmpty())
            <div class="space-y-4">
                @foreach ($order as $categoryLabel)
                    @if(empty($portEntries[$categoryLabel]))
                        @continue
                    @endif
                    <div class="space-y-2">
                        <h4>{{$categoryLabel}}</h4>
                        @php $categoryIndex = 0; @endphp
                        <div @if(count($portEntries[$categoryLabel]) > 1) class="grid grid-cols-1 lg:grid-cols-2 gap-2" @endif>
                        @foreach ($portEntries[$categoryLabel] ?? [] as $port)
                            <x-port-display :port="$port" :editable="data_get($port, 'editable', false)"
                                            :power-pools="$powerPools" :category-index="$categoryIndex"
                                            :vehicle-name="data_get($vehicle, 'name')"/>
                            @php $categoryIndex++; @endphp
                        @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</details>
