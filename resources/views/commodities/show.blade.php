@php
    use Illuminate\Support\Str;

    $name = data_get($resource, 'name', 'Resource');
    $resolvedVersion = request()->query('version');
    $indexRoute = route('web.commodities.index');

    if (is_string($resolvedVersion) && $resolvedVersion !== '') {
        $indexRoute = url()->query($indexRoute, ['version' => $resolvedVersion]);
    }

    $breadcrumbs = [
        ['label' => 'All Commodities', 'url' => $indexRoute],
    ];

    $rawVersions = data_get($resource, 'raw_versions', []);
    $refinedVersion = data_get($resource, 'refined_version', []);

    if (is_array($rawVersions) && count($rawVersions) === 1 && ($rawVersions[0]['web_url'] ?? null)) {
        $breadcrumbs[] = ['label' => $rawVersions[0]['name'], 'url' => $rawVersions[0]['web_url']];
    }

    $breadcrumbs[] = ['label' => $name, 'url' => null];

    if (!empty($refinedVersion)) {
        $breadcrumbs[] = ['label' => $refinedVersion['name'], 'url' => $refinedVersion['web_url']];
    }

    $locationCount = count(data_get($resource, 'locations', []));
    $totalDeposits = collect(data_get($resource, 'locations', []))
        ->reduce(static fn (int $carry, array $location): int => $carry + count(data_get($location, 'resources', [])), 0);
    $blueprints = data_get($resource, 'blueprints', []);
    $items = data_get($resource, 'items', []);
@endphp

@extends('layouts.app')

@section('title')
    {!! $pageTitle !!} - Star Citizen Resource
@endsection
@section('meta_description', Str::limit(data_get($resource, 'description', 'Star Citizen resource details.'), 160))

@section('content')
    <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-2">
            <div class="breadcrumbs text-sm text-base-content/70" data-testid="resource-breadcrumbs">
                <ul>
                    @foreach ($breadcrumbs as $breadcrumb)
                        <li>
                            @if (! empty($breadcrumb['url']))
                                <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['label'] }}</a>
                            @else
                                <span>{{ $breadcrumb['label'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <x-resource-search
            title="Search resources"
            description="Find resources by name across the current game version."
            :route="$indexRoute"
            placeholder="Search resource names"
            variant="minimal"
        />

        <div class="mx-auto grid w-full gap-4 xl:grid-cols-12 xl:items-stretch">
            <x-resources.hero :resource="$resource" class="xl:col-span-7" />
            <x-resources.quick-facts-card :resource="$resource" class="xl:col-span-5" />
        </div>

        <div class="flex flex-col gap-8">
            <section class="space-y-4">
                <div class="flex items-center gap-3">
                    <h2 class="text-lg font-semibold tracking-tight">Locations</h2>
                    @if ($locationCount > 0)
                        <span class="badge badge-ghost badge-sm">{{ $locationCount }} locations, {{ $totalDeposits }} deposits</span>
                    @endif
                </div>

                <x-resources.locations-card :resource="$resource" />
            </section>

            @if (!empty($blueprints))
                <section class="space-y-4">
                    <div class="flex items-center gap-3">
                        <h2 class="text-lg font-semibold tracking-tight">Used in Blueprints</h2>
                        <span class="badge badge-ghost badge-sm">{{ count($blueprints) }}</span>
                    </div>

                    <div class="card border border-base-300 bg-base-100 shadow">
                        <div class="card-body p-5 sm:p-6">
                            <div class="overflow-x-auto overflow-y-auto max-h-96">
                                <table class="table table-zebra">
                                    <thead>
                                        <tr>
                                            <th>Output</th>
                                            <th>Key</th>
                                            <th>Craft Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($blueprints as $blueprint)
                                            <tr>
                                                <td>
                                                    @if (!empty($blueprint['web_url']))
                                                        <a href="{{ $blueprint['web_url'] }}" class="link link-primary">{{ $blueprint['output_name'] ?? $blueprint['key'] }}</a>
                                                    @else
                                                        {{ $blueprint['output_name'] ?? $blueprint['key'] }}
                                                    @endif
                                                </td>
                                                <td class="font-mono text-sm">{{ $blueprint['key'] }}</td>
                                                <td>{{ $blueprint['craft_time_label'] ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            @endif

            @if (!empty($items))
                <section class="space-y-4">
                    <div class="flex items-center gap-3">
                        <h2 class="text-lg font-semibold tracking-tight">Contained in Items</h2>
                        <span class="badge badge-ghost badge-sm">{{ count($items) }}</span>
                    </div>

                    <div class="card border border-base-300 bg-base-100 shadow">
                        <div class="card-body p-5 sm:p-6">
                            <div class="overflow-x-auto overflow-y-auto max-h-96">
                                <table class="table table-zebra">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Sub Type</th>
                                            <th>Size</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($items as $item)
                                            <tr>
                                                <td>
                                                    @if (!empty($item['web_url']))
                                                        <a href="{{ $item['web_url'] }}" class="link link-primary">{{ $item['name'] }}</a>
                                                    @else
                                                        {{ $item['name'] }}
                                                    @endif
                                                </td>
                                                <td>{{ $item['type'] ?? '-' }}</td>
                                                <td>{{ $item['sub_type'] ?? '-' }}</td>
                                                <td>{{ $item['size'] ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            @endif

            <section class="space-y-4">
                <h2 class="text-lg font-semibold tracking-tight">Technical</h2>

                <x-resources.technical-card :resource="$resource" />
            </section>
        </div>
    </div>
@endsection
