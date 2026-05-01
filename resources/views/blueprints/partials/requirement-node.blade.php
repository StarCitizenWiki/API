@php use Illuminate\Support\Str; @endphp

@php
    $kind = (string) data_get($node, 'kind', 'requirement');
    $label = data_get($node, 'name') ?? data_get($node, 'key') ?? Str::headline($kind);
    $uuid = data_get($node, 'uuid');
    $requiredCount = data_get($node, 'required_count');
    $quantity = data_get($node, 'quantity');
    $quantityScu = data_get($node, 'quantity_scu');
    $minQuality = data_get($node, 'min_quality');
    $modifiers = data_get($node, 'modifiers', []);
    $children = data_get($node, 'children', []);
    $badges = [];

    if ($requiredCount !== null) {
        $badges[] = $requiredCount.' required';
    }

    if ($quantityScu !== null) {
        $badges[] = $quantityScu.' SCU';
    }

    if ($quantity !== null) {
        $badges[] = $quantity.' qty';
    }

    if ($minQuality !== null) {
        $badges[] = 'Min quality '.$minQuality;
    }

    $itemLink = $kind === 'item' && is_string($uuid) && Str::isUuid($uuid)
        ? route('web.items.show', array_filter([
            'item' => $uuid,
            'version' => $resolvedVersionCode,
        ]))
        : null;
@endphp

<div class="rounded-box border border-base-300 bg-base-100 p-3">
    <div class="flex flex-wrap items-start justify-between gap-2">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <span class="badge badge-outline badge-sm">{{ Str::headline($kind) }}</span>

                @if ($itemLink)
                    <a class="link link-hover font-medium" href="{{ $itemLink }}">{{ $label }}</a>
                @else
                    <span class="font-medium">{{ $label }}</span>
                @endif
            </div>

            @if (is_string($uuid) && $uuid !== '')
                <div class="mt-1 break-all font-mono text-xs text-subtle">{{ $uuid }}</div>
            @endif
        </div>

        @if ($badges !== [])
            <div class="flex flex-wrap gap-2">
                @foreach ($badges as $badge)
                    <span class="badge badge-ghost badge-sm">{{ $badge }}</span>
                @endforeach
            </div>
        @endif
    </div>

    @if (is_array($modifiers) && $modifiers !== [])
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach ($modifiers as $modifier)
                <span class="badge badge-primary badge-outline badge-sm">
                    {{ data_get($modifier, 'label', data_get($modifier, 'property_key', 'Modifier')) }}
                    @if (data_get($modifier, 'better_when'))
                        · better when {{ data_get($modifier, 'better_when') }}
                    @endif
                </span>
            @endforeach
        </div>
    @endif

    @if (is_array($children) && $children !== [])
        <div class="mt-3 space-y-3 border-l border-base-300 pl-4">
            @foreach ($children as $child)
                @include('blueprints.partials.requirement-node', [
                    'node' => $child,
                    'resolvedVersionCode' => $resolvedVersionCode,
                ])
            @endforeach
        </div>
    @endif
</div>
