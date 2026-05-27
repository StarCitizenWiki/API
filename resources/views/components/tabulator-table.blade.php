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

@if (!empty($config['columnBuilder']) && !empty($config['fieldCatalog']))
    <div
        class="rounded-box border border-base-300 bg-base-100 px-3 py-2 -my-4"
        data-tabulator-column-builder="{{ $id }}"
        data-testid="tabulator-column-builder-{{ $id }}"
    >
        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                class="btn btn-sm btn-outline"
                aria-haspopup="dialog"
                aria-controls="{{ $id }}-column-builder-dialog"
                data-column-builder-open
                data-testid="tabulator-column-builder-open-{{ $id }}"
            >
                Columns
                <span class="badge badge-sm" data-column-builder-count>0</span>
            </button>
            <button type="button" class="btn btn-xs btn-ghost" data-column-builder-defaults>Defaults</button>
            <span class="text-xs text-subtle" data-column-builder-status aria-live="polite"></span>
        </div>

        <dialog id="{{ $id }}-column-builder-dialog" class="modal" data-column-builder-dialog>
            <div class="modal-box flex h-5/6 w-11/12 max-w-6xl flex-col gap-0 p-0">
                <div class="flex items-center justify-between gap-3 border-b border-base-300 p-3">
                    <h2 class="text-base font-semibold">Customize columns</h2>
                    <button type="button" class="btn btn-sm btn-ghost" aria-label="Close column builder" data-column-builder-cancel>✕</button>
                </div>

                <div class="flex flex-wrap items-center gap-2 border-b border-base-300 p-2">
                    <label class="input input-sm input-bordered min-w-56 flex-1 items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-subtle">Search</span>
                        <input
                            type="search"
                            class="grow"
                            placeholder="vehicle weapon damage, quantum drive..."
                            data-column-builder-search
                            data-testid="tabulator-column-builder-search-{{ $id }}"
                        >
                    </label>
                    <button type="button" class="btn btn-xs btn-ghost" data-column-builder-core>Name only</button>
                    <button type="button" class="btn btn-xs btn-ghost" data-column-builder-defaults>Defaults</button>
                </div>

                <div
                    class="min-h-0 flex-1 overflow-y-auto overscroll-contain bg-base-200/20 p-2"
                    data-column-builder-list
                    data-testid="tabulator-column-builder-list-{{ $id }}"
                ></div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button type="submit" data-column-builder-backdrop>Close</button>
            </form>
        </dialog>
    </div>
@endif

@if (!empty($config['externalFilters']))
    <div class="card card-border bg-base-100" data-testid="tabulator-external-filters-{{ $id }}">
        <div class="flex flex-wrap items-end gap-3 px-4 pt-4" data-testid="tabulator-external-filters-bar-{{ $id }}">
            @foreach ($config['externalFilters'] as $filter)
                <label class="form-control">
                    <span class="label">
                        <span class="label-text text-xs font-semibold uppercase tracking-wide text-subtle">
                            {{ $filter['title'] }}
                        </span>
                    </span>
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
    <div class="card card-border bg-base-100" data-testid="tabulator-api-url-card-{{ $id }}">
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
