@props(['resource'])

@php
    $prerequisiteGroups = data_get($resource, 'prerequisite_groups') ?? [];
    $unlockGroups = data_get($resource, 'unlock_groups') ?? [];

    $hasPrerequisites = $prerequisiteGroups !== [];
    $hasUnlocks = $unlockGroups !== [];
    $hasAny = $hasPrerequisites || $hasUnlocks;

    $both = $hasPrerequisites && $hasUnlocks;

    $prerequisiteSubtitle = static fn (array $group): ?string => data_get($group, 'required_count')
        ? 'Complete ' . data_get($group, 'required_count') . ' of:'
        : null;
@endphp

@if ($hasAny)
    <section class="space-y-4">
        <h2 class="text-lg font-semibold tracking-tight">Mission Chain</h2>

        @if ($both)
            <div class="grid gap-4 lg:grid-cols-[1fr_auto_1fr] items-stretch">
                <x-missions.chain-mission-list title="Prerequisites" :subtitle-callback="$prerequisiteSubtitle" :groups="$prerequisiteGroups" />
                <div class="hidden lg:flex items-center">
                    <x-icon name="chevron-right" class="size-6 text-base-content/25" />
                </div>
                <x-missions.chain-mission-list title="Unlocks" subtitle-key="tag_name" :groups="$unlockGroups" />
            </div>
        @elseif ($hasPrerequisites)
            <x-missions.chain-mission-list title="Prerequisites" :subtitle-callback="$prerequisiteSubtitle" :groups="$prerequisiteGroups" />
        @elseif ($hasUnlocks)
            <x-missions.chain-mission-list title="Unlocks" subtitle-key="tag_name" :groups="$unlockGroups" />
        @endif
    </section>
@endif
