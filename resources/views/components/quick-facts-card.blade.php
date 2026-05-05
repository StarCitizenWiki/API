@props([
    'columns',
    'footer' => null,
    'testId' => null,
])

@php
    $columns = array_values(array_filter(array_map(function ($section) {
        if (isset($section['rows']) && is_array($section['rows'])) {
            $section['rows'] = array_values(array_filter($section['rows'], function ($row): bool {
                if ($row === null) {
                    return false;
                }

                return ! array_key_exists('value', $row) || $row['value'] !== null;
            }));
        }

        if (($section['rows'] ?? null) === []) {
            return null;
        }

        return $section;
    }, $columns)));

    $footer = is_array($footer) ? array_values(array_filter($footer)) : null;
@endphp

<section {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow']) }} @if($testId) data-testid="{{ $testId }}" @endif>
    <div class="card-body p-5 sm:p-6">
        <div class="grid h-full gap-6 sm:grid-cols-2 sm:gap-8">
            @foreach ($columns as $section)
                <section class="min-w-0 space-y-3">
                    <div class="font-semibold text-accent">
                        {{ $section['title'] }}
                    </div>

                    @if (($section['rows'] ?? []) !== [])
                        <dl class="space-y-2">
                            @foreach ($section['rows'] as $row)
                                <div class="grid grid-cols-2 items-start gap-x-3">
                                    <dt class="text-xs font-light uppercase tracking-wide text-subtle">
                                        {{ $row['label'] }}
                                    </dt>
                                    <dd class="min-w-0 text-right font-semibold text-base-content">
                                        @if (($row['type'] ?? null) === 'link')
                                            <a
                                                href="{{ $row['url'] }}"
                                                class="link link-hover link-primary"
                                                @if (! empty($row['test_id'])) data-testid="{{ $row['test_id'] }}" @endif
                                            >
                                                {{ $row['value'] }}
                                            </a>
                                        @elseif (($row['type'] ?? null) === 'links')
                                            <span class="flex flex-wrap justify-end gap-x-1.5 gap-y-0.5">
                                                @foreach ($row['value'] as $link)
                                                    <a
                                                        href="{{ $link['url'] }}"
                                                        class="link link-hover link-primary"
                                                    >{{ $link['name'] }}</a>@if (!$loop->last),@endif
                                                @endforeach
                                            </span>
                                        @elseif (($row['type'] ?? null) === 'badges')
                                            <span class="flex flex-wrap justify-end gap-2">
                                                @foreach ($row['value'] as $badge)
                                                    <span class="badge badge-outline badge-sm">{{ $badge }}</span>
                                                @endforeach
                                            </span>
                                        @elseif (($row['type'] ?? null) === 'badges_ghost')
                                            <span class="flex flex-wrap justify-end gap-2">
                                                @foreach ($row['value'] as $badge)
                                                    <span class="badge badge-ghost badge-sm">{{ $badge }}</span>
                                                @endforeach
                                            </span>
                                        @else
                                            @if (! empty($row['title']))
                                                <span title="{{ $row['title'] }}">{{ $row['value'] }}</span>
                                            @else
                                                {{ $row['value'] ?? '-' }}
                                            @endif
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    @else
                        <div class="text-sm text-subtle">-</div>
                    @endif
                </section>
            @endforeach
        </div>
    </div>
    @if($footer)
        <div class="px-5 sm:px-6 pt-3 pb-4">
            <dl class="text-xs flex flex-wrap gap-x-4 gap-y-1 text-muted justify-end">
                @foreach ($footer as $item)
                    <div class="flex gap-1">
                        <dt class="font-semibold">{{ $item['label'] }}</dt>
                        <dd>
                            @if (! empty($item['url']))
                                <a href="{{ $item['url'] }}" class="link link-hover link-primary" target="_blank">{{ $item['value'] }}</a>
                            @else
                                {{ $item['value'] }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
        </div>
    @endif
</section>
