@props(['resource'])

@php
    $uuid = data_get($resource, 'uuid');
    $apiLink = data_get($resource, 'link');
    $missionType = data_get($resource, 'mission_type');
    $debugName = data_get($resource, 'debug_name');
    $missionGiver = data_get($resource, 'mission_giver');
    $completionTags = data_get($resource, 'completion_tags') ?? [];
@endphp

<details {{ $attributes->merge(['class' => 'collapse collapse-arrow border border-base-300 bg-base-100 shadow', 'data-testid' => 'mission-technical-card']) }}>
    <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
        Technical
    </summary>
    <div class="collapse-content">
        <dl class="grid gap-3 sm:gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 tabular-nums">
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">UUID</dt>
                <dd class="text-sm font-medium break-all">{{ $uuid ?? '-' }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Mission Type</dt>
                <dd class="text-sm font-medium">{{ $missionType ?? '-' }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Mission Giver</dt>
                <dd class="text-sm font-medium">{{ $missionGiver ?? '-' }}</dd>
            </div>
            @if ($debugName)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Debug Name</dt>
                    <dd class="text-sm font-medium break-all font-mono">{{ $debugName }}</dd>
                </div>
            @endif
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">API Link</dt>
                <dd class="text-sm font-medium break-all">
                    @if ($apiLink)
                        <a href="{{ $apiLink }}" class="link link-primary">{{ $apiLink }}</a>
                    @else
                        -
                    @endif
                </dd>
            </div>
        </dl>

        @if ($completionTags !== [])
            <div class="mt-5 pt-5 border-t border-base-300">
                <h3 class="text-sm font-semibold text-base-content/65 mb-3">Completion Tags</h3>
                <div class="overflow-x-auto">
                    <table class="table table-sm table-zebra">
                        <thead>
                            <tr>
                                <th>Tag</th>
                                <th>Unlocks Missions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($completionTags as $tag)
                                <tr>
                                    <td>{{ data_get($tag, 'name', '-') }}</td>
                                    <td>
                                        @php
                                            $tagMissions = data_get($tag, 'unlocks_missions', []);
                                        @endphp
                                        @if ($tagMissions !== [])
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($tagMissions as $tagMission)
                                                    @if (data_get($tagMission, 'link'))
                                                        <a href="{{ data_get($tagMission, 'link') }}" class="link link-primary text-sm">{{ data_get($tagMission, 'title', '-') }}</a>
                                                    @else
                                                        <span class="text-sm">{{ data_get($tagMission, 'title', '-') }}</span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</details>
