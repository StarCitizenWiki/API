@extends('layouts.app')

@section('title')
    {!! data_get($seo, 'title') !!}
@endsection
@section('meta_description')
    {!! data_get($seo, 'metaDescription') !!}
@endsection

@section('meta')
    <x-seo.metadata
        :canonical="data_get($seo, 'canonicalUrl')"
        :keywords="data_get($seo, 'keywords', [])"
        :robots="data_get($seo, 'robots')"
        :og-title="data_get($seo, 'ogTitle')"
        :og-description="data_get($seo, 'ogDescription')"
        :twitter-title="data_get($seo, 'twitterTitle')"
        :twitter-description="data_get($seo, 'twitterDescription')"
        :structured-data="data_get($seo, 'structuredData', [])"
    />
@endsection

@section('content')
    @php $breadcrumbs = data_get($seo, 'breadcrumbs', []); @endphp
    @if (! $isEmptyMode && $breadcrumbs !== [])
        <div class="breadcrumbs text-sm text-subtle overflow-x-auto" data-testid="blueprint-breadcrumbs">
            <ul class="w">
                @foreach ($breadcrumbs as $breadcrumb)
                    <li>
                        @if ($loop->last)
                            <span>{{ $breadcrumb['label'] }}</span>
                        @else
                            <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['label'] }}</a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="flex flex-col gap-6" data-blueprint-show>
        @if ($isEmptyMode)
            <div x-data="blueprintSearch" class="card card-border bg-base-100 shadow">
                <div class="card-body gap-5">
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="badge badge-primary badge-sm">Blueprint Search</span>
                    </div>

                    <div class="space-y-2">
                        <h1 class="text-3xl font-semibold tracking-tight" data-testid="blueprints-search-heading">Find craftable items</h1>
                    </div>

                    @include('blueprints.partials.search-panel')
                </div>
            </div>
        @else
            <div class="card card-border bg-base-100 shadow-sm">
                <div class="card-body p-0">
                    <details x-data="blueprintSearch" class="collapse collapse-arrow rounded-box border-0 bg-base-100/80" @if ($searchQuery !== '') open @endif>
                        <summary class="collapse-title min-h-0 py-4 pr-10">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <h2 class="text-sm font-semibold">Change blueprint</h2>
                                    <p class="text-xs text-subtle">
                                        Search or filter another craftable output.
                                    </p>
                                </div>

                                <span class="badge badge-outline badge-sm" x-text="pluralizeResults(resultCount)">
                                    {{ $renderSearchResultCount }} result{{ $renderSearchResultCount === 1 ? '' : 's' }}
                                </span>
                            </div>
                        </summary>

                        <div class="collapse-content border-t border-base-300 px-4 pb-4 pt-4">
                            @include('blueprints.partials.search-panel')
                        </div>
                    </details>
                </div>
            </div>
        @endif

        @if ($isEmptyMode)
            <div class="card card-border bg-base-100 shadow">
                <div class="card-body gap-3">
                    <h2 class="text-lg font-semibold tracking-tight">Crafting breakdown</h2>
                </div>
            </div>
        @else
            <div x-data="blueprintTuning" class="card card-border bg-base-100 shadow" id="blueprint-recipe-flow">
                <div class="card-body gap-6">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-2">
                            <h2 class="text-lg font-semibold tracking-tight">Blueprint inputs</h2>
                        </div>

                        <div class="flex flex-wrap gap-2 text-xs">
                            <span class="badge badge-outline badge-sm">{{ count($aspects) }} recipe input{{ count($aspects) === 1 ? '' : 's' }}</span>
                        </div>
                    </div>

                    <div class="grid gap-4 xl:grid-cols-12">
                        <div class="order-2 grid gap-4 xl:order-1 xl:col-span-7">
                            @if ($aspects === [])
                                <div class="rounded-box border border-dashed border-base-300 bg-base-200 p-6 text-sm text-subtle">
                                    No recipe inputs were returned for this blueprint.
                                </div>
                            @endif

                            @foreach ($aspectGroups as $aspectGroup)
                                @if ($aspectGroup['is_choice_group'])
                                    <div class="rounded-box border border-base-300 bg-base-200 p-4">
                                        <div class="flex flex-wrap items-start justify-between gap-3">
                                            <div class="space-y-2">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="badge badge-primary badge-sm">Choose {{ $aspectGroup['required_count'] }} of {{ $aspectGroup['option_count'] }}</span>
                                                    <span class="badge badge-outline badge-sm">Input set</span>
                                                </div>
                                                @if (is_string($aspectGroup['display_name'] ?? null) && trim((string) $aspectGroup['display_name']) !== '')
                                                    <h3 class="text-base font-semibold">{{ $aspectGroup['display_name'] }}</h3>
                                                @endif
                                            </div>

                                            <span :class="getGroupCountClass('{{ $aspectGroup['key'] }}')" x-text="getGroupCountText('{{ $aspectGroup['key'] }}')">
                                                {{ $aspectGroup['selected_count'] }} of {{ $aspectGroup['required_count'] }} selected
                                            </span>
                                        </div>

                                        <p class="mt-2 text-xs text-subtle">
                                            Default preview uses the first {{ $aspectGroup['required_count'] }} option{{ $aspectGroup['required_count'] === 1 ? '' : 's' }}.
                                            Switch selections to model a different valid recipe.
                                        </p>

                                        <div
                                            class="mt-3 rounded-box border border-warning/30 bg-warning/10 px-3 py-2 text-xs text-warning"
                                            x-show="getGroupWarning('{{ $aspectGroup['key'] }}') !== ''"
                                            x-text="getGroupWarning('{{ $aspectGroup['key'] }}')"
                                            style="display: none;"
                                        ></div>

                                        <div class="mt-4 grid gap-4">
                                            @foreach ($aspectGroup['aspect_indexes'] as $aspectIdx)
                                                @include('blueprints.partials.aspect-card', [
                                                    'aspect' => $aspects[$aspectIdx],
                                                    'aspectIndex' => $aspectIdx,
                                                ])
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    @foreach ($aspectGroup['aspect_indexes'] as $aspectIdx)
                                        @include('blueprints.partials.aspect-card', [
                                            'aspect' => $aspects[$aspectIdx],
                                            'aspectIndex' => $aspectIdx,
                                        ])
                                    @endforeach
                                @endif
                            @endforeach
                        </div>

                        <div class="order-1 card border border-primary/40 bg-primary/10 shadow-sm xl:order-2 xl:sticky xl:top-24 xl:col-span-5 xl:self-start">
                            <div class="card-body gap-5">
                                <div class="space-y-4">
                                    <div class="flex flex-wrap items-center gap-2 text-xs">
                                        <span class="badge badge-primary badge-sm">Output</span>
                                        @if ($outputGrade)
                                            <span class="badge badge-soft badge-sm">Grade {{ $outputGrade }}</span>
                                        @endif
                                        <span class="ml-auto">
                                            @if ($isAvailableByDefault)
                                                <span class="badge badge-success badge-sm">Default</span>
                                            @else
                                                <span class="badge badge-outline badge-sm">Unlock required</span>
                                            @endif
                                        </span>
                                    </div>

                                    <div class="space-y-2">
                                        <h3 class="text-3xl font-semibold tracking-tight">
                                            @if ($outputItemWebUrl)
                                                <a href="{{ $outputItemWebUrl }}" class="link link-hover link-primary">{{ $blueprintName }}</a>
                                            @else
                                                {{ $blueprintName }}
                                            @endif
                                        </h3>

                                        @if ($blueprintKey)
                                            <div class="text-xs font-mono text-muted">{{ $blueprintKey }}</div>
                                        @endif

                                        <p class="text-sm text-emphasis">
                                            {{ $outputType ?? 'Unknown type' }}@if ($outputSubtype) / {{ $outputSubtype }}@endif
                                        </p>
                                    </div>

                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div class="rounded-box border border-base-300 bg-base-100 px-4 py-3">
                                            <div class="text-xs font-semibold uppercase tracking-wide text-subtle">Craft time</div>
                                            <div class="mt-1 text-sm font-medium">
                                                {{ $craftTimeLabel ?? 'Unknown' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h4 class="text-sm font-semibold">Output changes</h4>

                                <div class="rounded-box border border-dashed border-base-300 bg-base-100 px-4 py-4" x-show="aggregateEmptyState !== null">
                                    <div class="text-sm font-medium text-base-content" x-text="aggregateEmptyState?.title ?? ''">
                                        {{ $hasInteractiveAspects ? 'No output changes from baseline' : 'No adjustable output tuning available' }}
                                    </div>
                                    <p class="mt-1 text-xs text-subtle" x-text="aggregateEmptyState?.copy ?? ''">
                                        {{ $hasInteractiveAspects
                                            ? 'Move any quality slider away from its baseline to preview tuning changes.'
                                            : 'This blueprint does not expose quality-range modifier data for its recipe inputs.' }}
                                    </p>
                                </div>

                                <div class="grid gap-3">
                                    @foreach ($summaryPropertyList as $summaryProperty)
                                        @php($pk = data_get($summaryProperty, 'property_key'))
                                        <div
                                            :class="getAggregateCardClass('{{ $pk }}')"
                                            x-show="isAggregateVisible('{{ $pk }}')"
                                            style="display: none;"
                                        >
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="truncate text-sm font-medium">
                                                        {{ data_get($summaryProperty, 'label', data_get($summaryProperty, 'property_key', 'Property')) }}
                                                    </div>
                                                </div>

                                                <div class="shrink-0 text-right">
                                                    <div :class="getAggregateChangeClass('{{ $pk }}')" x-text="getAggregateChangeText('{{ $pk }}')">No change</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <p class="text-xs text-emphasis">
                                    Changes are shown relative to each selected input&apos;s baseline quality.
                                </p>

                                @if ($aspects !== [])
                                    <div class="border-t border-primary/15 pt-3">
                                        <div class="overflow-hidden rounded-box border border-base-300/80 bg-base-100">
                                            <table class="table">
                                                <thead class="bg-base-200 text-xs uppercase tracking-wide text-muted">
                                                    <tr>
                                                        <th>Input</th>
                                                        <th class="text-right">Quality</th>
                                                        <th class="text-right">Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="text-xs">
                                                    @foreach ($aspects as $aspectIndex => $aspect)
                                                        @php
                                                            $selectedInputName = is_string(data_get($aspect, 'input.name')) && trim((string) data_get($aspect, 'input.name')) !== ''
                                                                ? trim((string) data_get($aspect, 'input.name'))
                                                                : 'Unknown input';
                                                            $selectedInputAmount = $formatAspectAmount($aspect);
                                                        @endphp
                                                        <tr x-show="isBomRowVisible({{ $aspectIndex }})">
                                                            <td class="py-2">
                                                                <div class="font-medium text-emphasis">{{ $aspect['name'] }}</div>
                                                                <div class="text-xs text-subtle">{{ $selectedInputName }}</div>
                                                            </td>
                                                            <td class="py-2 text-right">
                                                                <span :class="getBomQualityClass({{ $aspectIndex }})" x-text="getBomQuality({{ $aspectIndex }})">
                                                                    {{ $formatAspectQuality($aspect) }}
                                                                </span>
                                                            </td>
                                                            <td class="py-2 text-right text-subtle">
                                                                {{ $selectedInputAmount ?? 'Unknown' }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    <tr x-show="!bomHasSelected" style="display: none;">
                                                        <td colspan="3" class="py-2 text-xs text-subtle">
                                                            No inputs currently selected.
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($unlockingMissions !== [] || $hasDismantleData || (! $isAvailableByDefault && $unlockingMissions === []))
                <div class="grid gap-6 lg:grid-cols-2">
                    @if ($unlockingMissions !== [])
                        <div class="card card-border bg-base-100 shadow">
                            <div class="card-body gap-4">
                                <h2 class="text-base font-semibold tracking-tight">Unlocking missions</h2>

                                <div class="space-y-3">
                                    @foreach ($unlockingMissions as $group)
                                        <div>
                                            <div class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-muted">{{ $group['label'] }}</div>
                                            <div class="grid grid-cols-1 gap-px overflow-hidden rounded-box border border-base-300 bg-base-300 sm:grid-cols-2">
                                                @foreach ($group['missions'] as $mission)
                                                    <div class="flex flex-col justify-center bg-base-100 px-3 py-2">
                                                        <div class="flex flex-wrap items-center gap-1.5">
                                                            @if ($mission['web_url'] ?? null)
                                                                <a href="{{ $mission['web_url'] }}" class="text-sm font-medium text-emphasis link link-hover">{{ $mission['title'] ?? 'Unknown mission' }}</a>
                                                            @else
                                                                <span class="text-sm font-medium text-emphasis">{{ $mission['title'] ?? 'Unknown mission' }}</span>
                                                            @endif
                                                            @if (($mission['count'] ?? 1) > 1)
                                                                <span class="badge badge-soft badge-sm">&times;{{ $mission['count'] }}</span>
                                                            @endif
                                                        </div>
                                                        @if ($mission['reward_scope'])
                                                            <span class="text-xs text-muted">{{ $mission['reward_scope'] }}</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @elseif (! $isAvailableByDefault)
                        <div class="card card-border bg-base-100 shadow">
                            <div class="card-body gap-4">
                                <h2 class="text-base font-semibold tracking-tight">Unlocking missions</h2>
                                <p class="text-sm text-subtle">No missions available for this blueprint yet.</p>
                            </div>
                        </div>
                    @endif

                    @if ($hasDismantleData)
                        <div class="card card-border bg-base-100 shadow">
                            <div class="card-body gap-3">
                                <h2 class="text-base font-semibold tracking-tight">Dismantle</h2>

                                @if ($dismantleTimeLabel || $dismantleEfficiency !== null)
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        @if ($dismantleTimeLabel)
                                            <div class="rounded-box border border-base-300 bg-base-200 px-4 py-3">
                                                <div class="text-xs font-semibold uppercase tracking-wide text-subtle">Time</div>
                                                <div class="mt-1 text-sm font-medium">{{ $dismantleTimeLabel }}</div>
                                            </div>
                                        @endif
                                        @if ($dismantleEfficiency !== null)
                                            <div class="rounded-box border border-base-300 bg-base-200 px-4 py-3">
                                                <div class="text-xs font-semibold uppercase tracking-wide text-subtle">Efficiency</div>
                                                <div class="mt-1 text-sm font-medium">{{ $dismantleEfficiency }}</div>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                @if ($dismantleReturns !== [])
                                    <div class="space-y-2">
                                        @foreach ($dismantleReturns as $dismantleReturn)
                                            <div class="rounded-box border border-base-300 bg-base-200 px-4 py-3">
                                                <div class="flex flex-wrap items-center justify-between gap-2">
                                                    @if (data_get($dismantleReturn, 'web_url'))
                                                        <a href="{{ data_get($dismantleReturn, 'web_url') }}" class="text-sm font-medium link link-hover">{{ data_get($dismantleReturn, 'name', 'Unknown') }}</a>
                                                    @else
                                                        <div class="text-sm font-medium">{{ data_get($dismantleReturn, 'name', 'Unknown') }}</div>
                                                    @endif
                                                    @if (data_get($dismantleReturn, 'quantity_scu') !== null)
                                                        <div class="text-xs text-subtle">{{ data_get($dismantleReturn, 'quantity_scu') }} SCU</div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <details class="collapse collapse-arrow border border-base-300 bg-base-100 shadow">
                <summary class="collapse-title min-h-11 py-3 font-semibold">Technical details</summary>
                <div class="collapse-content pt-0">
                    <div class="grid gap-4 border-t border-base-300 pt-4 xl:grid-cols-3">
                        <section class="card card-border bg-base-100 shadow-sm">
                            <div class="card-body gap-4">
                                <h2 class="card-title text-base">Blueprint metadata</h2>

                                <x-dl-section dlClass="space-y-3 text-sm">
                                    <x-dt-dd label="Blueprint UUID" ddClass="break-all font-mono">{{ $blueprintUuid }}</x-dt-dd>

                                    @if ($apiLink)
                                        <x-dt-dd label="API route" ddClass="break-all font-mono">
                                            <a class="link link-hover" href="{{ $apiLink }}">{{ $apiLink }}</a>
                                        </x-dt-dd>
                                    @endif

                                    <x-dt-dd label="Availability">{{ $isAvailableByDefault ? 'Available by default' : 'Not available by default' }}</x-dt-dd>
                                </x-dl-section>

                                @if (! $isAvailableByDefault && $unlockingMissions === [])
                                    <div class="space-y-2">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-subtle">Where to find blueprint</div>

                                        <p class="text-sm text-subtle">
                                            Unlock required, but no source location was returned for this blueprint.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </section>

                        <section class="card card-border bg-base-100 shadow-sm xl:col-span-2">
                            <div class="card-body gap-4">
                                <div class="flex items-center justify-between gap-3">
                                    <h2 class="card-title text-base">Requirement groups</h2>
                                    <span class="badge badge-soft badge-sm">{{ is_array($requirementGroups) ? count($requirementGroups) : 0 }}</span>
                                </div>

                                @if (! is_array($requirementGroups) || $requirementGroups === [])
                                    <p class="text-sm text-subtle">No requirement groups were returned for this blueprint.</p>
                                @else
                                    <div class="space-y-4">
                                        @foreach ($requirementGroups as $group)
                                            <div class="rounded-box border border-base-300 bg-base-200 p-4">
                                                <div class="flex flex-wrap items-start justify-between gap-2">
                                                    <div>
                                                        <h3 class="text-base font-semibold">
                                                            {{ $resolveRequirementLabel(data_get($group, 'name'), data_get($group, 'key'), 'Requirement group') }}
                                                        </h3>
                                                        @if (data_get($group, 'key'))
                                                            <div class="mt-1 text-xs font-mono text-subtle">{{ data_get($group, 'key') }}</div>
                                                        @endif
                                                    </div>

                                                    @if (data_get($group, 'required_count') !== null)
                                                        <span class="badge badge-outline badge-sm">
                                                            {{ data_get($group, 'required_count') }} required
                                                        </span>
                                                    @endif
                                                </div>

                                                @if (data_get($group, 'modifiers', []) !== [])
                                                    <div class="mt-3 flex flex-wrap gap-2">
                                                        @foreach (data_get($group, 'modifiers', []) as $modifier)
                                                            <span class="badge badge-primary badge-outline badge-sm">
                                                                {{ data_get($modifier, 'label', data_get($modifier, 'property_key', 'Modifier')) }}
                                                                @if (data_get($modifier, 'better_when'))
                                                                    better when {{ data_get($modifier, 'better_when') }}
                                                                @endif
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                <div class="mt-3 space-y-3">
                                                    @foreach (data_get($group, 'children', []) as $node)
                                                        @include('blueprints.partials.requirement-node', [
                                                            'node' => $node,
                                                            'resolvedVersionCode' => $resolvedVersionCode,
                                                        ])
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach>
                                    </div>
                                @endif
                            </div>
                        </section>

                        <section class="card card-border bg-base-100 shadow-sm xl:col-span-3">
                            <div class="card-body gap-3">
                                <h2 class="card-title text-base">Raw Blueprint Payload</h2>
                                <pre class="overflow-x-auto rounded-box bg-base-200 p-4 text-xs">{{ $rawBlueprintJson }}</pre>
                            </div>
                        </section>
                    </div>
                </div>
            </details>
        @endif

        <script type="application/json" id="blueprint-show-data">
            {!! $clientPayload !!}
        </script>
    </div>
@endsection
