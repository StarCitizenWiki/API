@php
    $childName = data_get($child, 'name', 'Unknown child location');
    $childTypeName = data_get($child, 'type_name');
    $childClassification = data_get($child, 'type_classification');
    $childUrl = data_get($child, 'url');
    $childHighlights = is_array(data_get($child, 'highlights')) ? data_get($child, 'highlights') : [];
    $testId = data_get($child, 'testid');
@endphp

@if ($childUrl)
    <a
        href="{{ $childUrl }}"
        class="group block rounded-box border border-base-300 bg-base-100 p-4 transition hover:border-base-content/20 hover:bg-base-200/35 focus:outline-none focus-visible:ring-2 focus-visible:ring-base-content/20"
        @if ($testId) data-testid="{{ $testId }}" @endif
    >
        <div class="min-w-0 space-y-2">
            <div class="space-y-1">
                <div class="truncate text-sm font-semibold text-base-content transition group-hover:text-base-content/80">
                    {{ $childName }}
                </div>

                @if (is_string($childTypeName) && $childTypeName !== '')
                    <div class="truncate text-xs text-subtle">
                        {{ $childTypeName }}

                        @if (is_string($childClassification) && $childClassification !== '' && $childClassification !== $childTypeName)
                            <span class="text-base-content/35">·</span>
                            {{ $childClassification }}
                        @endif
                    </div>
                @endif
            </div>

            @if ($childHighlights !== [])
                <div class="flex flex-wrap gap-2">
                    @foreach ($childHighlights as $highlight)
                        <span class="badge badge-sm {{ $highlight['variant'] }}">@if ($highlight['icon'] ?? null)<x-icon :name="$highlight['icon']" class="size-3" /> @endif{{ $highlight['label'] }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </a>
@else
    <div class="rounded-box border border-base-300 bg-base-100 p-4">
        <div class="min-w-0 space-y-2">
            <div class="space-y-1">
                <div class="truncate text-sm font-semibold text-base-content">
                    {{ $childName }}
                </div>

                @if (is_string($childTypeName) && $childTypeName !== '')
                    <div class="truncate text-xs text-subtle">
                        {{ $childTypeName }}

                        @if (is_string($childClassification) && $childClassification !== '' && $childClassification !== $childTypeName)
                            <span class="text-base-content/35">·</span>
                            {{ $childClassification }}
                        @endif
                    </div>
                @endif
            </div>

            @if ($childHighlights !== [])
                <div class="flex flex-wrap gap-2">
                    @foreach ($childHighlights as $highlight)
                        <span class="badge badge-sm {{ $highlight['variant'] }}">@if ($highlight['icon'] ?? null)<x-icon :name="$highlight['icon']" class="size-3" /> @endif{{ $highlight['label'] }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endif
