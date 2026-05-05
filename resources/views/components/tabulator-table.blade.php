@props([
    'id',
    'config' => [],
    'initial' => null,
    'showApiUrl' => true,
    'apiUrlLabel' => 'API URL',
])

@php
    $apiUrlTargetId = $config['apiUrlTargetId'] ?? null;
    $apiUrlEndpoint = $config['endpoint'] ?? '#';
@endphp

@if (!empty($config['externalFilters']))
    <div class="card card-border bg-base-100" data-testid="tabulator-external-filters-{{ $id }}>
        <div class="flex flex-wrap items-end gap-3 px-4 pt-4" data-testid="tabulator-external-filters-bar-{{ $id }}">
            @foreach ($config['externalFilters'] as $filter)
                <label class="form-control">
                    <div class="label">
                        <span class="label-text text-xs font-semibold uppercase tracking-wide text-subtle">
                            {{ $filter['title'] }}
                        </span>
                    </div>
                    <select
                        class="select select-bordered select-sm"
                        data-external-filter="{{ $filter['field'] }}"
                        data-testid="tabulator-external-filter-{{ $filter['field'] }}"
                        @isset($filter['options'])
                            data-external-filter-static
                        @endisset
                    >
                        @isset($filter['options'])
                            @foreach ($filter['options'] as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        @else
                            <option value="">All</option>
                        @endisset
                    </select>
                </label>
            @endforeach
        </div>
        <div class="divider my-0 -mb-2"></div>
        <div
            id="{{ $id }}"
            data-tabulator
            data-tabulator-id="{{ $id }}"
            data-testid="tabulator-table-{{ $id }}"
            class="w-full"
        ></div>
    </div>
@else
    <div
        id="{{ $id }}"
        data-tabulator
        data-tabulator-id="{{ $id }}"
        data-testid="tabulator-table-{{ $id }}"
        class="w-full"
    ></div>
@endif


@if ($showApiUrl && $apiUrlTargetId)
    <div class="card card-border bg-base-100" data-testid="tabulator-api-url-card-{{ $id }}>
        <div class="card-body gap-3 p-4">
            <label class="label p-0" for="{{ $apiUrlTargetId }}" data-testid="tabulator-api-url-label-{{ $id }}">
                <span class="label-text text-xs font-semibold uppercase tracking-wide text-subtle">
                    {{ $apiUrlLabel }}
                </span>
            </label>
            <div class="join w-full">
                <input
                    id="{{ $apiUrlTargetId }}"
                    data-testid="tabulator-api-url-input-{{ $id }}"
                    class="input input-bordered join-item w-full font-mono text-xs"
                    type="text"
                    readonly
                    value="{{ $apiUrlEndpoint }}"
                >
                <a
                    data-testid="tabulator-api-url-open-{{ $id }}"
                    class="btn btn-neutral join-item"
                    href="{{ $apiUrlEndpoint }}"
                    target="_blank"
                    rel="noreferrer noopener"
                    data-api-url-open="{{ $apiUrlTargetId }}"
                >
                    Open
                </a>
            </div>
        </div>
    </div>
@endif

<script type="application/json" id="{{ $id }}-config" data-testid="tabulator-config-{{ $id }}">@json($config)</script>

@if($initial !== null)
    <script type="application/json" id="{{ $id }}-initial" data-testid="tabulator-initial-{{ $id }}">@json($initial)</script>
@endif
