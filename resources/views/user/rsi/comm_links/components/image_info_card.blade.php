<div class="card image-card">
    <a href="{{ $image->url }}" target="_blank" class="text-center d-block">
        @if(\Illuminate\Support\Str::contains($image->metadata->mime, 'video'))
            <div style="position: relative;" class="comm-link-card-image">
                @unless($image->tags->isEmpty())
                    <a href="{{ route('web.user.rsi.comm-links.images.index-by-tag', $image->tags->first()->getRouteKey()) }}" class="first-tag badge badge-secondary">
                        {{ $image->tags->first()->translated_name }}
                    </a>
                @endunless
                <span class="file-type badge badge-{{ $image->metadata->mime_class }}">{{ $image->metadata->mime }}</span>
                <video class="card-img-top" loading="lazy" controls>
                    <source src="{{ $image->url }}" type="{{ $image->metadata->mime }}">
                </video>
            </div>
        @elseif(\Illuminate\Support\Str::contains($image->metadata->mime, 'image'))
        <div style="position: relative;" class="comm-link-card-image">
            @unless($image->tags->isEmpty())
                <a href="{{ route('web.user.rsi.comm-links.images.index-by-tag', $image->tags->first()->getRouteKey()) }}" class="first-tag badge badge-secondary">
                    {{ $image->tags->first()->translated_name }}
                </a>
            @endunless
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
    @unless(isset($noFooter))
    <div class="card-body">
        @can('web.user.rsi.comm-links.view')
            <div class="btn-group d-flex" style="gap: 0.5rem">
                <a type="button" class="btn btn-outline-primary upload-btn bg-primary" data-id={{ $image->id }} data-cl-id="{{ $image->commLinks->pluck('cig_id')->min() }}">
                    @lang('Hochladen ins Wiki')
                </a>

                <a type="button" title="@lang('Edit') @lang('Tags')" class="btn btn-secondary" href="{{ route('web.user.rsi.comm-links.images.edit-tags', $image->getRouteKey()) }}">
                    @component('components.elements.icon')
                        tag
                    @endcomponent
                </a>
            </div>

        @endcan
        @if(\Illuminate\Support\Str::contains($image->metadata->mime, 'image'))
            <a type="button" class="btn btn-block btn-secondary mt-2" href="{{ route('web.user.rsi.comm-links.images.similar', $image->getRouteKey()) }}">
                @lang('Ähnliche Bilder (alpha)')
            </a>
        @endif


        <div class="image-info-card">
            @unless(empty($image->alt))
            <p class="image-description">@lang('Beschreibung'): <span>{{ $image->alt }}</span></p>
            <div class="divider"></div>
            @endunless
            <div class="image-metadata">
                @if(isset($image->similarity))
                    <div>
                        <p>@lang('Ähnlichkeit')</p>
                        <span>{{ $image->similarity }}%<br><small>{{ $image->similarity_method }}</small></span>
                    </div>
                    <div class="divider-vertical"></div>
                @endif
                <div>
                    <p>@lang('Zuletzt geändert'):</p>
                    <span title="{{ __('Kopiert Datum bei Klick') }}"
                        data-last-modified="{{ $image->metadata->last_modified->toDateTimeString() }}"
                        style="cursor:pointer;">{{ $image->metadata->last_modified->format('d.m.Y H:i:s') }}
                    </span>
                </div>
                <div class="divider-vertical"></div>
                <div>
                    <p>@lang('Größe'):</p>
                    <span title="{{ $image->metadata->size }} bytes">{{ round($image->metadata->size / (1024 * 1024), 2) }} MB</span>
                </div>
            </div>
        </div>
    </div>
    @endunless
    @if(@isset($loop))
    <div class="image-info-card-bottom list-group list-group-flush collapse mt-1" id="comm_link_container_{{ $loop->index }}">
        @unless($image->tags->isEmpty())
        <div class="tag-container">
            @foreach($image->tags as $tag)
                <a class="badge badge-secondary m-0" href="{{ route('web.user.rsi.comm-links.images.index-by-tag', $tag->getRouteKey()) }}" title="{{ $tag->images_count }} @lang('Bilder mit diesem Tag')">
                    {{ $tag->translated_name }}
                </a>
            @endforeach
        </div>
        <div class="divider"></div>
        @endunless
        <p><a class="url" href="{{ $image->url }}" target="_blank">@lang('Quelle')</a></p>
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
        <a class="btn btn-block btn-secondary mt-1" data-toggle="collapse" href="#comm_link_container_{{ $loop->index }}" role="button"
           aria-expanded="false" aria-controls="comm_link_container_{{ $loop->index }}">
           @lang('Mehr Infos')
        </a>
    </div>
    @endif
</div>
