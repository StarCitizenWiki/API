@php
    use Illuminate\Support\Str;

    $name = data_get($resource, 'name', 'Resource');
    $resolvedVersion = request()->query('version');
    $indexRoute = route('web.commodities.index');

    if (is_string($resolvedVersion) && $resolvedVersion !== '') {
        $indexRoute = url()->query($indexRoute, ['version' => $resolvedVersion]);
    }

    $breadcrumbs = data_get($seo, 'breadcrumbs', []);

    $locationCount = count(data_get($resource, 'locations', []));
    $totalDeposits = collect(data_get($resource, 'locations', []))
        ->reduce(static fn (int $carry, array $location): int => $carry + count(data_get($location, 'resources', [])), 0);
    $blueprints = data_get($resource, 'blueprints', []);
    $items = data_get($resource, 'items', []);
    $uexPrices = data_get($resource, 'uex_prices.purchase', []);
@endphp
@extends('layouts.app')

@section('title')
    {!! data_get($seo, 'title', $name.' - Star Citizen Resource') !!}
@endsection

@section('meta_description')
    {!! data_get($seo, 'metaDescription', Str::limit(data_get($resource, 'description', 'Star Citizen resource details.'), 160)) !!}
@endsection

@section('meta')
    <x-seo.metadata
        :canonical="data_get($seo, 'canonicalUrl')"
        :keywords="data_get($seo, 'keywords', [])"
        :og-title="data_get($seo, 'ogTitle')"
        :og-description="data_get($seo, 'ogDescription')"
        :twitter-title="data_get($seo, 'twitterTitle')"
        :twitter-description="data_get($seo, 'twitterDescription')"
        :structured-data="data_get($seo, 'structuredData', [])"
    />
@endsection

@section('content')
    <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-2">
            <div class="breadcrumbs text-sm text-subtle" data-testid="resource-breadcrumbs">
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

        <div class="mx-auto grid w-full gap-4 xl:grid-cols-12">
            <x-resources.hero :resource="$resource" class="xl:col-span-7" />
            <x-resources.quick-facts-card :resource="$resource" class="xl:col-span-5" />
        </div>

        <div class="flex flex-col gap-8">
            <section class="space-y-4">
                <div class="flex items-center gap-3">
                    <h2 class="text-lg font-semibold tracking-tight">Locations</h2>
                    @if ($locationCount > 0)
                        <span class="badge badge-soft badge-sm">{{ $locationCount }} locations, {{ $totalDeposits }} deposits</span>
                    @endif
                </div>

                <x-resources.locations-card :resource="$resource" />
            </section>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-uex.prices-card
                    class="sm:col-span-2"
                    title="Market Prices"
                    :sections="[['prices' => $uexPrices]]"
                />

                @if (!empty($blueprints))
                    <div class="card card-border bg-base-100 shadow">
                        <div class="card-body p-5 sm:p-6">
                            <div class="flex items-center gap-2 text-sm font-semibold text-subtle mb-3">
                                <span>Used in Blueprints</span>
                                <span class="badge badge-soft text-xs">{{ count($blueprints) }}</span>
                            </div>
                            <div class="max-h-96 overflow-auto">
                                <table class="table table-sm table-zebra">
                                    <thead>
                                        <tr>
                                            <th>Output</th>
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
                                                <td>{{ $blueprint['craft_time_label'] ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                @if (!empty($items))
                    <div class="card card-border bg-base-100 shadow">
                        <div class="card-body p-5 sm:p-6">
                            <div class="flex items-center gap-2 text-sm font-semibold text-subtle mb-3">
                                <span>Contained in Items</span>
                                <span class="badge badge-soft text-xs">{{ count($items) }}</span>
                            </div>
                            <div class="max-h-96 overflow-auto">
                                <table class="table table-sm table-zebra">
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
                @endif
            </div>
        </div>
    </div>
@endsection
