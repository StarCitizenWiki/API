@props([
    'composition',
    'versionQuery' => null,
])

@php
    $entries = is_array($composition) ? $composition : [];
    $entryCount = count($entries);
@endphp

<section {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow']) }}>
    <div class="card-body gap-4 p-5 sm:p-6">
        <div class="flex items-center gap-2">
            <h2 class="card-title text-base">Default Composition</h2>
            @if ($entryCount > 0)
                <span class="badge badge-ghost text-xs">{{ $entryCount }}</span>
            @endif
        </div>

        @if ($entryCount > 0)
            <div class="grid gap-2 sm:hidden">
                @foreach ($entries as $entry)
                    @php
                        $commodity = data_get($entry, 'commodity');
                        $commodityName = data_get($commodity, 'name', data_get($entry, 'entry', '-'));
                        $commodityUuid = data_get($commodity, 'uuid');
                        $weight = data_get($entry, 'weight');
                        $commodityUrl = $commodityUuid ? route('web.commodities.show', $commodityUuid) : null;
                        if ($commodityUrl && $versionQuery) {
                            $commodityUrl = url()->query($commodityUrl, ['version' => $versionQuery]);
                        }
                    @endphp
                    <div class="card border border-base-300 bg-base-100 shadow-sm">
                        <div class="card-body gap-2 p-3">
                            <div class="text-sm font-semibold">
                                @if ($commodityUrl)
                                    <a href="{{ $commodityUrl }}" class="link link-hover link-primary">{{ $commodityName }}</a>
                                @else
                                    {{ $commodityName }}
                                @endif
                            </div>
                            <div class="text-xs text-subtle">
                                Weight: {{ $weight !== null ? rtrim(rtrim(number_format($weight * 100, 2), '0'), '.') . '%' : '-' }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="hidden overflow-x-auto sm:block">
                <table class="table table-sm">
                    <caption class="sr-only">Default commodity composition</caption>
                    <thead>
                        <tr>
                            <th scope="col">Commodity</th>
                            <th scope="col">Weight</th>
                            <th scope="col">Link</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($entries as $entry)
                            @php
                                $commodity = data_get($entry, 'commodity');
                                $commodityName = data_get($commodity, 'name', data_get($entry, 'entry', '-'));
                                $commodityUuid = data_get($commodity, 'uuid');
                                $weight = data_get($entry, 'weight');
                                $commodityUrl = $commodityUuid ? route('web.commodities.show', $commodityUuid) : null;
                                if ($commodityUrl && $versionQuery) {
                                    $commodityUrl = url()->query($commodityUrl, ['version' => $versionQuery]);
                                }
                            @endphp
                            <tr>
                                <td class="whitespace-nowrap">
                                    @if ($commodityUrl)
                                        <a href="{{ $commodityUrl }}" class="link link-hover link-primary">{{ $commodityName }}</a>
                                    @else
                                        {{ $commodityName }}
                                    @endif
                                </td>
                                <td>{{ $weight !== null ? rtrim(rtrim(number_format($weight * 100, 2), '0'), '.') . '%' : '-' }}</td>
                                <td>
                                    @if ($commodityUrl)
                                        <a href="{{ $commodityUrl }}" class="link link-primary">View</a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-sm text-subtle">No default composition data available.</div>
        @endif
    </div>
</section>
