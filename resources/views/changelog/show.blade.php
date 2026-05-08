@php
    $fromCode = $previousVersion->code;
    $toCode = $version->code;
@endphp
@extends('layouts.app')

@section('title')
    {{ $pageTitle }} - Star Citizen Changelog
@endsection
@section('meta_description')
    What changed between Star Citizen versions {{ $fromCode }} and {{ $toCode }}.
@endsection

@section('content')
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <div class="breadcrumbs text-sm text-subtle">
                <ul>
                    <li>Changelog</li>
                    <li>{{ $toCode }}</li>
                </ul>
            </div>

            <h1 class="text-3xl font-bold">Version Changelog</h1>
            <div class="flex items-center gap-2 text-subtle">
                <span class="font-mono">{{ $fromCode }}</span>
                <x-icon name="arrow-right" class="size-4" />
                <span class="font-mono font-semibold">{{ $toCode }}</span>
                @if($version->released_at)
                    <span class="text-sm">· Released {{ $version->released_at->format('M j, Y') }}</span>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            @foreach($entityTypes as $key => $config)
                @php
                    $counts = $changeCounts[$key] ?? ['added' => 0, 'removed' => 0, 'modified' => 0];
                    $total = $counts['added'] + $counts['removed'] + $counts['modified'];
                    $isActive = $entityType === $key;
                @endphp
                @if($total > 0)
                    <a href="{{ route('web.changelog.show', ['version' => $version->code, 'entity_type' => $key, 'change_type' => $changeType]) }}"
                       class="card bg-base-100 shadow-sm border {{ $isActive ? 'border-primary' : 'border-base-300' }} hover:border-primary transition-colors">
                        <div class="card-body p-3 gap-0.5">
                            <div class="text-xs font-semibold">{{ $config['label'] }}</div>
                            <div class="flex gap-2 text-xs">
                                <span>+{{ $counts['added'] }}</span>
                                <span class="text-subtle">~{{ $counts['modified'] }}</span>
                                <span class="text-subtle">-{{ $counts['removed'] }}</span>
                            </div>
                        </div>
                    </a>
                @endif
            @endforeach
        </div>

        <div class="flex flex-wrap gap-2 items-center">
            <div class="join">
                <a href="{{ route('web.changelog.show', ['version' => $version->code, 'entity_type' => $entityType, 'change_type' => 'all']) }}"
                   class="btn btn-sm join-item {{ $changeType === 'all' ? 'btn-secondary' : 'btn-ghost' }}">
                    All
                </a>
                <a href="{{ route('web.changelog.show', ['version' => $version->code, 'entity_type' => $entityType, 'change_type' => 'added']) }}"
                   class="btn btn-sm join-item {{ $changeType === 'added' ? 'btn-success' : 'btn-ghost' }}">
                    Added
                </a>
                <a href="{{ route('web.changelog.show', ['version' => $version->code, 'entity_type' => $entityType, 'change_type' => 'modified']) }}"
                   class="btn btn-sm join-item {{ $changeType === 'modified' ? 'btn-info' : 'btn-ghost' }}">
                    Changed
                </a>
                <a href="{{ route('web.changelog.show', ['version' => $version->code, 'entity_type' => $entityType, 'change_type' => 'removed']) }}"
                   class="btn btn-sm join-item {{ $changeType === 'removed' ? 'btn-error' : 'btn-ghost' }}">
                    Removed
                </a>
            </div>

            <span class="text-sm text-subtle">{{ $changes->total() }} results</span>
        </div>

        @if($changes->isEmpty())
            <div class="text-center text-subtle py-12">No changes found for this filter.</div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach($changes as $diff)
                    @php
                        $displayName = $diff->resolveDisplayName();
                        $webUrl = $diff->resolveWebUrl();
                        $className = $diff->resolveClassName();
                        $columnChanges = $diff->column_changes ? $diff->column_changes->toArray() : [];
                        $dataChanges = $diff->data_changes ? $diff->data_changes->toArray() : [];
                        $hasDetails = $diff->change_type === 'modified' && (count($columnChanges) > 0 || count($dataChanges) > 0);
                        $changeTree = $hasDetails ? $diff->buildChangeTree() : [];
                    @endphp

                    <div class="card bg-base-100 shadow-sm">
                        <div class="card-body p-3 gap-1">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    @if($webUrl && $displayName)
                                        <a href="{{ $webUrl }}" class="link link-hover link-primary font-medium text-sm">{{ $displayName }}</a>
                                        @if($className)
                                            <span class="font-mono text-xs text-subtle">({{ $className }})</span>
                                        @endif
                                        @if($diff->change_type === 'removed')
                                            <div class="text-xs text-subtle">via {{ $fromCode }}</div>
                                        @endif
                                    @else
                                        <span class="text-sm text-subtle italic">Unknown</span>
                                    @endif
                                </div>
                                @if($diff->change_type === 'added')
                                    <span class="badge badge-success badge-sm shrink-0">New</span>
                                @elseif($diff->change_type === 'removed')
                                    <span class="badge badge-error badge-sm shrink-0">Removed</span>
                                @else
                                    <span class="badge badge-info badge-sm shrink-0">Changed</span>
                                @endif
                            </div>

                            @if($diff->change_type === 'modified' && $hasDetails)
                                @php
                                    $totalChanges = count($columnChanges) + count($dataChanges);
                                @endphp
                                <details class="mt-1">
                                    <summary class="text-xs font-medium cursor-pointer select-none px-2 py-1 rounded hover:bg-base-200 transition-colors">
                                        {{ $totalChanges }} change{{ $totalChanges !== 1 ? 's' : ''}}
                                    </summary>
                                    <div class="mt-2 max-h-96 overflow-y-auto">
                                        @include('changelog.partials.change-tree', ['node' => $changeTree])
                                    </div>
                                </details>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Pagination --}}
        <div class="flex justify-center">
            {{ $changes->withQueryString()->links() }}
        </div>
    </div>
@endsection
