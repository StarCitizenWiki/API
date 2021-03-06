<ul>
    @forelse($changelogs as $changelog)
        <li>
            @if(isset($changelog->changelog['extra']['locale']))
                Übersetzung @lang($changelog->changelog['extra']['locale'])
                @if($changelog->type === 'creation')
                    erstellt durch
                @else
                    aktualisiert durch
                @endif
            @else
                {{ $slot }}
                @if($changelog->type === 'creation')
                    importiert von
                @else
                    @unless(empty($changelog->changelog->get('changes', [])))
                        <span
                            @php
                                $str = [];
                                foreach($changelog->changelog['changes'] as $key => $change) {
                                    if (is_array($change['old'])) {
                                        $str[] = ucfirst($key).": ".implode(', ', $change['old'])." &rarr; ".implode(', ', $change['new']);
                                    } else {
                                        $str[] = ucfirst($key).": ".\Illuminate\Support\Str::limit($change['old'], 40, "&hellip;")." &rarr; ".\Illuminate\Support\Str::limit($change['new'], 40, "&hellip;");
                                    }
                                }
                                $str = implode('<br>', $str);
                            @endphp
                            title="Änderungen"
                            data-content="{!! $str !!}"
                            data-toggle="popover"
                            data-html="true"
                        >
                            <u>aktualisiert</u>
                        </span> durch
                    @else
                        aktualisiert durch
                    @endunless
                @endif
            @endif
            <a href="{{ optional($changelog->user)->userNameWikiLink() ?? config('api.wiki_url').'/Star_Citizen_Wiki:API' }}" target="_blank">
                {{ optional($changelog->user)->username ?? config('app.name') }}
            </a>
            <span>
                {{ $changelog->created_at->diffForHumans() }} &mdash; {{ $changelog->created_at->format('d.m.Y H:i') }}
            </span>
        </li>
    @empty
        <li>Keine Änderungen vorhanden</li>
    @endforelse
</ul>