@props([
    'entries',
    'testId' => null,
])

<section class="space-y-4">
    <h2 class="text-lg font-semibold tracking-tight">Technical</h2>

    <details {{ $attributes->merge(['class' => 'collapse collapse-arrow border border-base-300 bg-base-100 shadow']) }}
        @if($testId) data-testid="{{ $testId }}" @endif>
        <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
            Technical
        </summary>
        <div class="collapse-content">
            <x-dl-section dlClass="grid gap-3 sm:gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 tabular-nums">
                @foreach($entries as $entry)
                    <x-dt-dd :label="$entry['label']" ddClass="{{ ($entry['url'] ?? null) ? 'break-all' : '' }}">
                        @if($entry['url'] ?? null)
                            <a href="{{ $entry['url'] }}" class="link link-primary">{{ $entry['value'] }}</a>
                        @else
                            {{ $entry['value'] ?? '-' }}
                        @endif
                    </x-dt-dd>
                @endforeach
            </x-dl-section>

            {{ $slot }}
        </div>
    </details>
</section>
