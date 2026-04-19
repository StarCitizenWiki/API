@props([
    'groups',
    'title',
    'subtitleKey' => null,
    'subtitleCallback' => null,
])

<div class="card border border-base-300 bg-base-100 shadow">
    <div class="card-body p-5 sm:p-6">
        <h3 class="text-sm font-semibold text-base-content/65 mb-3">{{ $title }}</h3>
        <div class="space-y-4">
            @foreach ($groups as $group)
                <div>
                    @php
                        $groupSubtitle = null;
                        if ($subtitleCallback && is_callable($subtitleCallback)) {
                            $groupSubtitle = $subtitleCallback($group);
                        } elseif ($subtitleKey && data_get($group, $subtitleKey)) {
                            $groupSubtitle = data_get($group, $subtitleKey);
                        }
                    @endphp

                    @if ($groupSubtitle)
                        <div class="mb-2 text-xs text-base-content/50">{{ $groupSubtitle }}</div>
                    @endif
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                        @foreach (data_get($group, 'missions', []) as $mission)
                            @if (data_get($mission, 'web_link'))
                                <a href="{{ data_get($mission, 'web_link') }}" class="flex items-center justify-between gap-2 rounded-lg border border-base-300 bg-base-100/70 px-3 py-2 transition hover:border-base-content/20 hover:bg-base-200/35">
                                    <span class="truncate text-sm font-medium">{{ data_get($mission, 'title', '-') }}</span>
                                    @if (data_get($mission, 'mission_type'))
                                        <span class="badge badge-ghost badge-sm shrink-0">{{ data_get($mission, 'mission_type') }}</span>
                                    @endif
                                </a>
                            @else
                                <div class="flex items-center justify-between gap-2 rounded-lg border border-base-300 px-3 py-2">
                                    <span class="truncate text-sm">{{ data_get($mission, 'title', '-') }}</span>
                                    @if (data_get($mission, 'mission_type'))
                                        <span class="badge badge-ghost badge-sm shrink-0">{{ data_get($mission, 'mission_type') }}</span>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
