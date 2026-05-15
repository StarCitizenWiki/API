@extends('admin.layout')

@section('breadcrumbs')
    <li><a href="{{ route('admin.game-versions.index') }}">Game Versions</a></li>
    <li>{{ $version->code }} - Diff</li>
@endsection

@section('admin.content')
    <div class="flex flex-col gap-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold" data-testid="admin-version-diff-heading">
                Regression Report: {{ $previousVersion?->code ?? 'N/A' }} → {{ $version->code }}
            </h1>
        </div>

        @if(!$previousVersion || !$diffExists)
            <div role="alert" class="alert alert-warning">
                @if(!$previousVersion)
                    No previous version found for {{ $version->code }}.
                @else
                    No diff computed yet. Run <code class="text-sm">php artisan game:compute-version-diff {{ $version->code }}</code>.
                @endif
            </div>
        @else
            <section class="space-y-4">
                <div class="stats stats-vertical md:stats-horizontal shadow bg-base-200">
                    <div class="stat">
                        <div class="stat-title">Items Removed</div>
                        <div class="stat-value text-error">{{ $summary['items']['removed'] }}</div>
                    </div>
                    <div class="stat">
                        <div class="stat-title">Vehicles Removed</div>
                        <div class="stat-value text-error">{{ $summary['vehicles']['removed'] }}</div>
                    </div>
                    <div class="stat">
                        <div class="stat-title">Item Regressions</div>
                        <div class="stat-value text-warning">{{ $summary['item_regressions'] }}</div>
                    </div>
                    <div class="stat">
                        <div class="stat-title">Vehicle Regressions</div>
                        <div class="stat-value text-warning">{{ $summary['vehicle_regressions'] }}</div>
                    </div>
                </div>

                <div class="text-sm text-subtle">
                    Context: {{ $summary['items']['modified'] }} items modified, {{ $summary['vehicles']['modified'] }} vehicles modified, {{ $summary['items']['added'] }} items added, {{ $summary['vehicles']['added'] }} vehicles added.
                </div>
            </section>

            @if($removedPaths->isNotEmpty())
                <section class="space-y-4">
                    <div class="card card-border bg-base-100 shadow">
                        <div class="card-body p-5 sm:p-6">
                            <h2 class="text-lg font-semibold tracking-tight text-error">⚠️ Item Data Paths Removed</h2>
                            <p class="text-sm text-subtle">Data paths that existed in {{ $previousVersion->code }} but are missing/null in {{ $version->code }}</p>
                            <div class="max-h-96 overflow-y-auto mt-2">
                                <table class="table table-sm table-pin-rows">
                                    <thead>
                                        <tr>
                                            <th>Path</th>
                                            <th>Affected Items</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($removedPaths as $path => $count)
                                            <tr>
                                                <td class="font-mono text-sm">{{ $path }}</td>
                                                <td>
                                                    <span class="badge badge-error">{{ $count }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            @endif

            @if($removedVehiclePaths->isNotEmpty())
                <section class="space-y-4">
                    <div class="card card-border bg-base-100 shadow">
                        <div class="card-body p-5 sm:p-6">
                            <h2 class="text-lg font-semibold tracking-tight text-error">⚠️ Vehicle Data Paths Removed</h2>
                            <p class="text-sm text-subtle">Data paths that existed in {{ $previousVersion->code }} but are missing/null in {{ $version->code }}</p>
                            <div class="max-h-96 overflow-y-auto mt-2">
                                <table class="table table-sm table-pin-rows">
                                    <thead>
                                        <tr>
                                            <th>Path</th>
                                            <th>Affected Vehicles</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($removedVehiclePaths as $path => $count)
                                            <tr>
                                                <td class="font-mono text-sm">{{ $path }}</td>
                                                <td>
                                                    <span class="badge badge-error">{{ $count }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            @endif
        @endif
    </div>
@endsection
