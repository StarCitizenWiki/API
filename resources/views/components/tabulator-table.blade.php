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
    <div class="card card-border border-base-300 bg-base-100 shadow">
        <div class="card-body gap-3 p-4">
            <label class="label p-0" for="{{ $apiUrlTargetId }}">
                <span class="label-text text-xs font-semibold uppercase tracking-wide text-base-content/60">
                    {{ $apiUrlLabel }}
                </span>
            </label>
            <div class="join w-full">
                <input
                    id="{{ $apiUrlTargetId }}"
                    class="input input-bordered join-item w-full font-mono text-xs"
                    type="text"
                    readonly
                    value="{{ $apiUrlEndpoint }}"
                >
                <a
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
    class="w-full shadow"
></div>

<script type="application/json" id="{{ $id }}-config">@json($config)</script>

@if($initial !== null)
    <script type="application/json" id="{{ $id }}-initial">@json($initial)</script>
@endif
