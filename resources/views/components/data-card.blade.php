@props([
    'title',
    'sections' => [],
])

@php
    $hasData = collect($sections)
        ->filter(fn ($s) => ($s['rows'] ?? []) !== [] || ($s['spacer'] ?? false))
        ->isNotEmpty();
@endphp

@if ($hasData)
    <section {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow']) }}>
        <div class="card-body gap-3 p-4 sm:p-5">
            <h2 class="card-title text-base">{{ $title }}</h2>

            @php
                $hasFullWidth = collect($sections)->contains(fn ($s) => ($s['fullWidth'] ?? false));
                $gridSections = array_values(array_filter($sections, fn ($s) => ! ($s['fullWidth'] ?? false)));
                $fullWidthSections = array_values(array_filter($sections, fn ($s) => ($s['fullWidth'] ?? false)));
            @endphp

            @if ($fullWidthSections !== [])
                @foreach ($fullWidthSections as $section)
                    @if (($section['rows'] ?? []) !== [])
                        @php $colCount = count($section['columns'] ?? []); @endphp
                        <dl class="grid grid-cols-{{ $colCount }} gap-x-2 gap-y-1">
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
                            <div @if (($section['colSpan'] ?? null)) class="col-span-{{ $section['colSpan'] }}" @endif>
                                <div class="flex items-baseline justify-between border-b border-base-200 pb-1 mb-2">
                                    <h3 class="text-xs font-medium uppercase tracking-wider text-muted">{{ $section['title'] ?? '' }}</h3>
                                    @if (($section['header'] ?? null))
                                        <span class="text-xs text-subtle">{{ $section['header'] }}</span>
                                    @endif
                                </div>

                                @if (($section['columns'] ?? null) !== null)
                                    @php $colCount = count($section['columns']) + 1; @endphp
                                    <dl class="grid grid-cols-{{ $colCount }} gap-x-2 gap-y-1">
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
                                                <dd class="text-sm font-semibold {{ $row['class'] ?? '' }}">{{ $row['value'] }}</dd>
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
