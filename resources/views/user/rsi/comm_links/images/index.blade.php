@extends('user.layouts.default_wide')

@section('title', __('Comm-Link Bilder'))

@section('content')
    <h3>Comm-Link Bilder</h3>

    @if(is_callable([$images, 'links']) && method_exists($images, 'links'))
    <div>
        {{ $images->links() }}
    </div>
    @endif

    <div class="card-columns" style="column-count: 5">
        @foreach($images as $image)
            <div class="card">
                <a href="{{ $image->getLocalOrRemoteUrl() }}" target="_blank" class="text-center d-block">
                    <img src="{{ str_replace('source', 'post', $image->url) }}"
                         alt="{{ empty($image->alt) ? __('Kein alt Text verfügbar') : $image->alt }}"
                         class="card-img-top">
                </a>
                <div class="card-body">
                    @if(isset($image->similarity))
                        <p>Ähnlichkeit {{ $image->similarity }}%</p>
                    @endif

                    @unless(empty($image->alt))
                        <p>Bildbeschreibung: {{ $image->alt }}</p>
                    @endunless

                    <span class="badge badge-{{ $image->isHashed() ? 'success' : 'danger' }}"
                          title="Hash {{ !$image->isHashed() ? __('nicht') : '' }} {{ __('vorhanden') }}"
                    >#</span>
                    <span class="badge badge-info" title="{{ $image->metadata->size }} bytes">{{ __('Größe') }}: {{ round($image->metadata->size / (1024 * 1024), 2) }} MB</span>
                    <span class="badge badge-{{ $image->metadata->mime_class }}">{{ __('Typ') }}: {{ $image->metadata->mime }}</span>
                    <span class="badge badge-secondary last-modified" title="{{ __('Kopiert Datum bei Klick') }}"
                          data-last-modified="{{ $image->metadata->last_modified->toDateTimeString() }}"
                          style="cursor:pointer;">{{ __('Zuletzt geändert') }}: {{ $image->metadata->last_modified->format('d.m.Y H:i:s') }}</span>
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
        @endforeach
    </div>

    @if(is_callable([$images, 'links']) && method_exists($images, 'links'))
    <div>
        {{ $images->links() }}
    </div>
    @endif
@endsection

@section('body__after')
    @parent
    <script>
        (() => {
            document.querySelectorAll('.badge.last-modified').forEach(entry => {
                entry.addEventListener('click', () => {
                    navigator.clipboard.writeText(entry.dataset.lastModified);
                });
            });
        })();
    </script>
@endsection