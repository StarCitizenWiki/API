@props([
    'deposit',
    'resourceKind',
    'withVersion',
])

@php
    $depLabel = data_get($deposit, 'label');
    $depKey = data_get($deposit, 'key');
    $clustering = data_get($deposit, 'clustering');
    $depQMin = data_get($deposit, 'quality_min');
    $depQMax = data_get($deposit, 'quality_max');
    $depQRange = ($depQMin !== null && $depQMax !== null && $depQMax > $depQMin) ? $depQMax - $depQMin : null;
    $materials = data_get($deposit, 'materials', []);
    $depSignature = data_get($deposit, 'signature');
    $relProbMin = data_get($deposit, 'relative_probability_min_percent');
    $relProbMax = data_get($deposit, 'relative_probability_max_percent');

    $clusterMin = data_get($clustering, 'min_size');
    $clusterMax = data_get($clustering, 'max_size');
    $clusterProb = data_get($clustering, 'probability_percent');
@endphp

<div class="border-t border-base-200 pt-3 mt-1 first:border-t-0 first:mt-0 first:pt-0">
    <div class="flex flex-wrap items-center gap-2 mb-3">
        <span class="text-sm font-semibold" title="{{ $depKey }}">
            {{ $depLabel }}
            @if ($resourceKind === 'mineable')
                Deposit
            @endif
        </span>

        @if ($depSignature !== null)
            <span class="badge badge-ghost badge-sm">Signature: {{ $depSignature }}</span>
        @endif

        @if ($clusterMin !== null && $clusterMax !== null)
            <span class="badge badge-ghost badge-sm" title="Clustering: number of deposits that spawn together">
                Cluster: {{ $clusterMin }}-{{ $clusterMax }}
                @if ($clusterProb !== null)
                    ({{ $clusterProb }}%)
                @endif
            </span>
        @endif

        @if ($relProbMin !== null)
            <span class="badge badge-outline badge-sm" title="Relative probability within group">{{ $relProbMin === $relProbMax ? "{$relProbMin}%" : "{$relProbMin}-{$relProbMax}%" }}</span>
        @endif

        @php
            $areaExceptions = data_get($deposit, 'area_exceptions', []);
        @endphp
        @if (is_array($areaExceptions) && $areaExceptions !== [])
            @foreach ($areaExceptions as $exception)
                @php
                    $exceptionName = data_get($exception, 'name');
                    $modifierValue = data_get($exception, 'modifier');

                    $badgeClass = 'badge-warning';
                    $suffix = ' (increased spawn rate)';
                    $badgeTitle = "{$exceptionName} — Increased spawn rate";

                    if ($modifierValue === 0) {
                        $badgeClass = 'badge-ghost opacity-50 line-through';
                        $suffix = ' (does not spawn)';
                        $badgeTitle = "{$exceptionName} — Does not spawn in this area";
                    }
                @endphp
                <span class="badge badge-sm {{ $badgeClass }}" title="{{ $badgeTitle }}">{{ $exceptionName }}{{ $suffix }}</span>
            @endforeach
        @endif
    </div>

    @if ($materials !== [] && ! in_array($resourceKind, ['harvestable', 'salvage']))
        <div class="overflow-x-auto">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Range</th>
                        <th>Quality</th>
                        <th>Mean</th>
                        <th>Std Dev</th>
                        <th>Instability</th>
                        <th>Resistance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($materials as $material)
                        @php
                            $matName = data_get($material, 'name');
                            $matUuid = data_get($material, 'uuid');
                            $isCurrent = data_get($material, 'is_current', false);
                            $matMinPct = data_get($material, 'min_percentage');
                            $matMaxPct = data_get($material, 'max_percentage');
                            $qMin = data_get($material, 'quality_min');
                            $qMax = data_get($material, 'quality_max');
                            $qMean = data_get($material, 'quality_mean');
                            $qStddev = data_get($material, 'quality_stddev');

                            $qualityPercent = null;
                            $qualityLeftPercent = null;
                            if ($qMin !== null && $qMax !== null && $depQRange !== null && $depQRange > 0) {
                                $qualityPercent = round((($qMax - $qMin) / $depQRange) * 100, 1);
                                $qualityLeftPercent = round((($qMin - $depQMin) / $depQRange) * 100, 1);
                            } elseif ($qMin !== null && $qMax !== null) {
                                $qualityPercent = 100;
                                $qualityLeftPercent = 0;
                            }
                        @endphp

                        <tr class="hover{{ $isCurrent ? ' bg-primary/5' : '' }}">
                            <td class="text-xs font-medium whitespace-nowrap">
                                @if ($isCurrent)
                                    <span class="font-bold">{{ $matName }}</span>
                                @elseif ($matUuid)
                                    <a href="{{ $withVersion(route('web.commodities.show', ['identifier' => $matUuid])) }}" class="link link-hover">{{ $matName }}</a>
                                @else
                                    {{ $matName }}
                                @endif
                            </td>
                            <td class="text-xs tabular-nums whitespace-nowrap">
                                @if ($matMinPct !== null)
                                    {{ round((float) $matMinPct, 1) }}-{{ round((float) $matMaxPct, 1) }}%
                                @else
                                    -
                                @endif
                            </td>
                            <td class="whitespace-nowrap">
                                @if ($qMin !== null && $qMax !== null)
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-medium">{{ $qMin }}</span>
                                        <div class="relative h-1.5 w-16 rounded-full bg-base-200">
                                            <div class="absolute h-1.5 rounded-full bg-primary" style="left: {{ $qualityLeftPercent ?? 0 }}%; width: {{ $qualityPercent ?? 100 }}%"></div>
                                        </div>
                                        <span class="text-xs font-medium">{{ $qMax }}</span>
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-xs tabular-nums">{{ $qMean ?? '-' }}</td>
                            <td class="text-xs tabular-nums">{{ $qStddev ?? '-' }}</td>
                            <td class="text-xs tabular-nums">{{ data_get($material, 'instability') ?? '-' }}</td>
                            <td class="text-xs tabular-nums">{{ data_get($material, 'resistance') ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
