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

@if ($showApiUrl && $apiUrlTargetId)
    <div class="card card-border border-base-300 bg-base-100 shadow" data-testid="tabulator-api-url-card-{{ $id }}">
        <div class="card-body gap-3 p-4">
            <label class="label p-0" for="{{ $apiUrlTargetId }}" data-testid="tabulator-api-url-label-{{ $id }}">
                <span class="label-text text-xs font-semibold uppercase tracking-wide text-base-content/60">
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

<div
    id="{{ $id }}"
    data-tabulator
    data-tabulator-id="{{ $id }}"
    data-testid="tabulator-table-{{ $id }}"
    class="w-full shadow"
></div>

<script type="application/json" id="{{ $id }}-config" data-testid="tabulator-config-{{ $id }}">@json($config)</script>

@if($initial !== null)
    <script type="application/json" id="{{ $id }}-initial" data-testid="tabulator-initial-{{ $id }}">@json($initial)</script>
@endif
