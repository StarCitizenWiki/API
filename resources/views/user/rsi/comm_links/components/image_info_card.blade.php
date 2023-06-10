<div class="card image-card">
    <a href="{{ $image->getLocalOrRemoteUrl() }}" target="_blank" class="text-center d-block">
        @if(\Illuminate\Support\Str::contains($image->metadata->mime, 'video'))
            <div style="position: relative;" class="comm-link-card-image">
                <span class="file-type badge badge-{{ $image->metadata->mime_class }}">{{ $image->metadata->mime }}</span>
                <video class="card-img-top" loading="lazy">
                    <source src="{{ $image->url }}" type="{{ $image->metadata->mime }}">
                </video>
            </div>
        @elseif(\Illuminate\Support\Str::contains($image->metadata->mime, 'image'))
        <div style="position: relative;" class="comm-link-card-image">
            <span class="file-type badge badge-{{ $image->metadata->mime_class }}">{{ $image->metadata->mime }}</span>
            <img src="{{ str_replace('source', 'post', $image->url) }}"
                 alt="{{ empty($image->alt) ? __('Kein alt Text verfügbar') : $image->alt }}"
                 class="card-img-top"
                 loading="lazy" />
        </div>
        @elseif(\Illuminate\Support\Str::contains($image->metadata->mime, 'audio'))
            <audio class="card-img-top" controls>
                <source src="{{ $image->url }}" type="{{ $image->metadata->mime }}">
            </audio>
        @else
            <p class="card-img-top m-0 pt-2">{{ $image->name }}</p>
        @endif
    </a>
    <div class="card-body">
        @can('web.user.rsi.comm-links.view')
            <a type="button" class="badge badge-info upload-btn" data-id={{ $image->id }} data-cl-id="{{ $image->commLinks->pluck('cig_id')->min() }}">
                @lang('Hochladen ins Wiki')
            </a>
        @endcan
        @if(isset($image->similarity))
            <p>@lang('Ähnlichkeit') {{ $image->similarity }}%</p>
        @endif

        <div class="image-info-card">
            @unless(empty($image->alt))
            <p>@lang('Beschreibung'):</p>
            <span>{{ $image->alt }}</span>
            <div class="divider"></div>
            @endunless
            <div style="display: flex;">
                <div style="width: 50%;">
                    <p>@lang('Zuletzt geändert'):</p>
                    <span title="{{ __('Kopiert Datum bei Klick') }}"
                        data-last-modified="{{ $image->metadata->last_modified->toDateTimeString() }}"
                        style="cursor:pointer;">{{ $image->metadata->last_modified->format('d.m.Y H:i:s') }}
                    </span>
                </div>
                <div class="divider-vertical"></div>
                <div style="width: 50%; padding-left: 10px;">
                    <p>@lang('Größe'):</p>
                    <span title="{{ $image->metadata->size }} bytes">{{ round($image->metadata->size / (1024 * 1024), 2) }} MB</span>
                </div>
            </div>
        </div>
    </div>
    @unless(isset($image->similarity))
        <div class="image-info-card-bottom list-group list-group-flush collapse" id="comm_link_container_{{ $loop->index }}">
            <p>@lang('Quelle'):</p>
            <span><a class="url" href="{{ $image->url }}" target="_blank">{{ $image->src }}</a></span>
            @unless(empty($image->commLinks))
                <div class="divider"></div>
                <p>@lang('Comm-Links'):</p>
                <span style="display: flex; flex-direction: column;">
                    @foreach($image->commLinks->sortByDesc('cig_id')->take(5) as $commLink)
                        <a href="{{ route('web.user.rsi.comm-links.show', $commLink->getRouteKey()) }}"
                           class="card-link">{{ $commLink->cig_id }} - {{ $commLink->title }}
                        </a>
                    @endforeach
                    @if($image->commLinks->count() > 5)
                        <span>@lang('Verwendet in') <b>{{ $image->commLinks->count() }}</b> @lang('Comm-Links')</span>
                    @endif
                </span>
            @endunless
        </div>

        <div class="card-footer">
            <a data-toggle="collapse" href="#comm_link_container_{{ $loop->index }}" role="button"
               aria-expanded="false" aria-controls="comm_link_container_{{ $loop->index }}">
               @lang('Mehr Infos')
            </a>
        </div>

        @else

        <div class="image-info-card-bottom" id="comm_link_container_{{ $loop->index }}">
            <p>@lang('Quelle'):</p>
            <span><a class="url" href="{{ $image->url }}" target="_blank">{{ $image->src }}</a></span>
            <div class="divider"></div>
            <p>@lang('Comm-Links'):</p>
            <span style="display: flex; flex-direction: column;">
                @foreach($image->commLinks as $commLink)
                    <a href="{{ route('web.user.rsi.comm-links.show', $commLink->getRouteKey()) }}"
                       class="card-link">{{ $commLink->cig_id }} - {{ $commLink->title }}
                    </a>
                @endforeach
            </span>
        </div>
    @endunless
</div>
