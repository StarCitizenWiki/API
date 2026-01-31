@props([
    'rawData',
])

<details {{ $attributes->merge(['class' => 'collapse collapse-arrow border border-base-300 bg-base-100 shadow']) }}>
    <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
        Raw Item Payload
    </summary>
    <div class="collapse-content">
        <pre class="text-xs whitespace-pre-wrap max-h-72 overflow-y-auto sm:max-h-96">{{ $rawData }}</pre>
    </div>
</details>
