@props([
    'childGroups' => [],
    'childCount' => 0,
    'childTypeCount' => 0,
])

@php
    $childGroups = is_array($childGroups) ? $childGroups : [];
@endphp

<div class="flex flex-col gap-4" data-testid="starmap-location-hierarchy">
    <section class="card border border-base-300 bg-base-100 shadow-sm">
        <div class="card-body gap-4 p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="text-base font-semibold tracking-tight">Child Locations</h3>
                    <span class="badge badge-ghost">{{ $childCount }}</span>
                </div>
                @if ($childTypeCount > 0)
                    <div class="text-sm text-base-content/65">{{ $childTypeCount }} type{{ $childTypeCount === 1 ? '' : 's' }}</div>
                @endif
            </div>

            @if ($childGroups !== [])
                <div class="space-y-4">
                    @foreach ($childGroups as $group)
                        <section class="space-y-3" data-testid="starmap-location-child-group-{{ $group['key'] }}">
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="text-sm font-semibold tracking-tight text-base-content/80">{{ $group['type_name'] }}</h4>
                                <span class="badge badge-outline badge-sm">{{ $group['count'] }}</span>
                            </div>

                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach ($group['visible_children'] as $child)
                                    @include('components.starmap.locations.partials.child-card', ['child' => $child])
                                @endforeach
                            </div>

                            @if ($group['overflow_children'] !== [])
                                <details class="collapse collapse-arrow border border-base-200 bg-base-100/80" data-testid="starmap-location-child-group-overflow-{{ $group['key'] }}">
                                    <summary class="collapse-title min-h-10 py-3 text-sm font-semibold">
                                        Show {{ count($group['overflow_children']) }} more
                                    </summary>
                                    <div class="collapse-content pt-1">
                                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                            @foreach ($group['overflow_children'] as $child)
                                                @include('components.starmap.locations.partials.child-card', ['child' => $child])
                                            @endforeach
                                        </div>
                                    </div>
                                </details>
                            @endif
                        </section>
                    @endforeach
                </div>
            @else
                <div class="text-sm text-base-content/70">
                    No child locations are available for this location.
                </div>
            @endif
        </div>
    </section>
</div>
