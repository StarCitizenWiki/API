<div class="card">
    <a href="{{ $image->getLocalOrRemoteUrl() }}" target="_blank" class="text-center d-block">
        @if(\Illuminate\Support\Str::contains($image->metadata->mime, 'video'))
            <video class="card-img-top" loading="lazy">
                <source src="{{ $image->url }}" type="{{ $image->metadata->mime }}">
            </video>
        @else
            <img src="{{ str_replace('source', 'post', $image->url) }}"
                 alt="{{ empty($image->alt) ? __('Kein alt Text verfügbar') : $image->alt }}"
                 class="card-img-top"
                 loading="lazy" />
        @endif
    </a>
    <div class="card-body">
        @if(isset($image->similarity))
            <p>Ähnlichkeit {{ $image->similarity }}%</p>
        @endif

        @unless(empty($image->alt))
            <p>Bildbeschreibung: {{ $image->alt }}</p>
        @endunless

        <div class="text-center">
            @unless(\Illuminate\Support\Str::contains($image->metadata->mime, 'video'))
                <span class="badge badge-{{ $image->isHashed() ? 'success' : 'danger' }}"
                      title="Hash {{ !$image->isHashed() ? __('nicht') : '' }} {{ __('vorhanden') }}"
                >#</span>
            @endunless
            <span class="badge badge-info" title="{{ $image->metadata->size }} bytes">{{ __('Größe') }}: {{ round($image->metadata->size / (1024 * 1024), 2) }} MB</span>
            <span class="badge badge-{{ $image->metadata->mime_class }}">{{ __('Typ') }}: {{ $image->metadata->mime }}</span>
            <span class="badge badge-secondary last-modified" title="{{ __('Kopiert Datum bei Klick') }}"
                  data-last-modified="{{ $image->metadata->last_modified->toDateTimeString() }}"
                  style="cursor:pointer;">{{ __('Zuletzt geändert') }}: {{ $image->metadata->last_modified->format('d.m.Y H:i:s') }}</span>
            @can('web.user.rsi.comm-links.view')
                <a type="button" class="badge badge-success upload-btn" data-id={{ $image->id }} data-cl-id="{{ $image->commLinks->pluck('cig_id')->min() }}">
                    Upload
                </a>
            @endcan
        </div>
    </div>
    @unless(isset($image->similarity))
        <ul class="list-group list-group-flush collapse" id="comm_link_container_{{ $loop->index }}">
            <li class="list-group-item">
                <small>Quelle: <a href="{{ $image->url }}" target="_blank">{{ $image->src }}</a></small>
            </li>
            @foreach($image->commLinks as $commLink)
                <li class="list-group-item">
                    <a href="{{ route('web.user.rsi.comm-links.show', $commLink->getRouteKey()) }}"
                       class="card-link">{{ $commLink->cig_id }} &mdash; {{ $commLink->title }}</a>
                </li>
            @endforeach
        </ul>
        <div class="card-footer">
            <a data-toggle="collapse" href="#comm_link_container_{{ $loop->index }}" role="button"
               aria-expanded="false" aria-controls="comm_link_container_{{ $loop->index }}">
                @lang('Comm-Links anzeigen')
            </a>
        </div>
    @else
        <ul class="list-group list-group-flush" id="comm_link_container_{{ $loop->index }}">
            <li class="list-group-item">
                <small>Quelle: <a href="{{ $image->url }}" target="_blank">{{ $image->src }}</a></small>
            </li>
            @foreach($image->commLinks as $commLink)
                <li class="list-group-item">
                    <a href="{{ route('web.user.rsi.comm-links.show', $commLink->getRouteKey()) }}" class="card-link">{{ $commLink->cig_id }} &mdash; {{ $commLink->title }}</a>
                </li>
            @endforeach
        </ul>
    @endunless
</div>
