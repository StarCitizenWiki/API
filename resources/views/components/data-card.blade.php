@props([
    'title',
    'sections' => [],
])

@php
    $hasData = collect($sections)
        ->filter(fn ($s) => ($s['rows'] ?? []) !== [] || ($s['spacer'] ?? false))
        ->isNotEmpty();

    $gridColumnClasses = [
        1 => 'grid-cols-1',
        2 => 'grid-cols-2',
        3 => 'grid-cols-3',
        4 => 'grid-cols-4',
        5 => 'grid-cols-5',
        6 => 'grid-cols-6',
        7 => 'grid-cols-7',
        8 => 'grid-cols-8',
        9 => 'grid-cols-9',
        10 => 'grid-cols-10',
        11 => 'grid-cols-11',
        12 => 'grid-cols-12',
    ];

    $columnSpanClasses = [
        1 => 'col-span-1',
        2 => 'col-span-2',
        3 => 'col-span-3',
        4 => 'col-span-4',
        5 => 'col-span-5',
        6 => 'col-span-6',
        7 => 'col-span-7',
        8 => 'col-span-8',
        9 => 'col-span-9',
        10 => 'col-span-10',
        11 => 'col-span-11',
        12 => 'col-span-12',
        'full' => 'col-span-full',
    ];
@endphp

@if ($hasData)
    <section {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow']) }}>
        <div class="card-body gap-3 p-4 sm:p-5">
            <h2 class="card-title text-base">{{ $title }}</h2>

            @php
                $gridSections = array_values(array_filter($sections, static fn ($s) => ! ($s['fullWidth'] ?? false)));
                $fullWidthSections = array_values(array_filter($sections, static fn ($s) => ($s['fullWidth'] ?? false)));
            @endphp

            @if ($fullWidthSections !== [])
                @foreach ($fullWidthSections as $section)
                    @if (($section['rows'] ?? []) !== [])
                        @php
                            $colCount = count($section['columns'] ?? []);
                            $gridColumnClass = $gridColumnClasses[$colCount] ?? 'grid-cols-1';
                        @endphp
                        <dl class="grid {{ $gridColumnClass }} gap-x-2 gap-y-1">
                            @foreach ($section['columns'] as $col)
                                <dt class="text-xs text-muted border-b border-base-200 pb-1 {{ $loop->first ? '' : 'text-right' }}">{{ $col }}</dt>
                            @endforeach

                            @foreach ($section['rows'] as $row)
                                @foreach ($row['values'] ?? [] as $value)
                                    <dd class="text-sm font-semibold py-0.5 {{ $loop->first ? '' : 'text-right' }}">{{ $value }}</dd>
                                @endforeach
                            @endforeach
                        </dl>
                    @endif
                @endforeach
            @endif

            @if ($gridSections !== [])
                <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 md:grid-cols-3">
                    @foreach ($gridSections as $section)
                        @if (($section['spacer'] ?? false))
                            <div></div>
                        @elseif (($section['rows'] ?? []) !== [])
                            @php
                                $columnSpan = $section['colSpan'] ?? null;
                                $columnSpanClass = $columnSpanClasses[$columnSpan] ?? null;
                            @endphp
                            <div @if ($columnSpanClass !== null) class="{{ $columnSpanClass }}" @endif>
                                <div class="flex items-baseline justify-between border-b border-base-200 pb-1 mb-2">
                                    <h3 class="text-xs font-medium uppercase tracking-wider text-muted">{{ $section['title'] ?? '' }}</h3>
                                    @if (($section['header'] ?? null))
                                        <span class="text-xs text-subtle">{{ $section['header'] }}</span>
                                    @endif
                                </div>

                                @if (($section['columns'] ?? null) !== null)
                                    @php
                                        $colCount = count($section['columns']) + 1;
                                        $gridColumnClass = $gridColumnClasses[$colCount] ?? 'grid-cols-1';
                                    @endphp
                                    <dl class="grid {{ $gridColumnClass }} gap-x-2 gap-y-1">
                                        <dt class="text-xs text-muted">{{ $section['colHeader'] ?? '' }}</dt>
                                        @foreach ($section['columns'] as $col)
                                            <dt class="text-right text-xs text-muted">{{ $col }}</dt>
                                        @endforeach

                                        @foreach ($section['rows'] as $row)
                                            <dt class="text-xs text-subtle">{{ $row['label'] }}</dt>
                                            @foreach ($row['values'] ?? [] as $value)
                                                <dd class="text-right text-sm font-semibold">{{ $value }}</dd>
                                            @endforeach
                                        @endforeach
                                    </dl>
                                @else
                                    <dl class="space-y-1">
                                        @foreach ($section['rows'] as $row)
                                            <div class="flex items-baseline justify-between gap-2">
                                                <dt class="text-xs text-subtle">{{ $row['label'] }}</dt>
                                                <dd class="text-sm font-semibold {{ $row['class'] ?? '' }}" @if (($row['title'] ?? null) !== null) title="{{ $row['title'] }}" @endif>{{ $row['value'] }}</dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                @endif

                                @if (($section['help'] ?? null))
                                    <p class="text-xs text-muted mt-2">{{ $section['help'] }}</p>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif
