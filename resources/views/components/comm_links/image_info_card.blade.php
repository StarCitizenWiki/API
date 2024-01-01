@php use Illuminate\Support\Str; @endphp
<div class="card image-card">
    @if(Str::contains($image->metadata->mime, 'video'))
        <div style="position: relative;" class="comm-link-card-image">
            @unless($image->tags->isEmpty())
                <a href="{{ route('web.rsi.comm-links.images.index-by-tag', $image->tags->first()->getRouteKey()) }}"
                   class="first-tag badge badge-secondary">
                    {{ $image->tags->first()->translated_name }}
                </a>
            @endunless
            <span class="file-type badge badge-{{ $image->metadata->mime_class }}">{{ $image->metadata->mime }}</span>
            <video class="card-img-top" loading="lazy" controls>
                <source src="{{ $image->url }}" type="{{ $image->metadata->mime }}">
            </video>
        </div>
    @elseif(Str::contains($image->metadata->mime, 'image'))
        <div style="position: relative;" class="comm-link-card-image">
            @unless($image->tags->isEmpty())
                <a href="{{ route('web.rsi.comm-links.images.index-by-tag', $image->tags->first()->getRouteKey()) }}"
                   class="first-tag badge badge-secondary">
                    {{ $image->tags->first()->translated_name }}
                </a>
            @endunless
            <span class="file-type badge badge-{{ $image->metadata->mime_class }}">{{ $image->metadata->mime }}</span>
            <a href="{{ $image->url }}" target="_blank" class="text-center d-block">
                <img src="{{ str_replace('source', 'post', $image->url) }}"
                     alt="{{ empty($image->alt) ? __('Kein alt Text verfügbar') : $image->alt }}"
                     class="card-img-top"
                     loading="lazy"/>
            </a>
        </div>
    @elseif(Str::contains($image->metadata->mime, 'audio'))
        <audio class="card-img-top" controls>
            <source src="{{ $image->url }}" type="{{ $image->metadata->mime }}">
        </audio>
    @else
        <p class="card-img-top m-0 pt-2">{{ $image->name }}</p>
    @endif
    @unless(isset($noFooter))
        <div class="card-body">
            @can('web.rsi.comm-links.view')
                <div class="btn-group d-flex mb-2" style="gap: 0.5rem">
                    <a type="button" class="btn btn-outline-primary upload-btn bg-primary w-75"
                       data-id={{ $image->id }} data-cl-id="{{ $image->commLinks->pluck('cig_id')->min() }}">
                        @lang('Hochladen ins Wiki')
                    </a>

                    <a type="button" title="@lang('Edit') @lang('Tags')" class="btn btn-secondary w-25"
                       href="{{ route('web.rsi.comm-links.images.edit-tags', $image->getRouteKey()) }}">
                        @component('components.elements.icon')
                            tag
                        @endcomponent
                    </a>
                </div>
            @endcan

            <div class="image-info-card">
                <p class="image-description">@lang('Name'): <span>{{ $image->name }}</span></p>
                @unless(empty($image->alt))
                    <p class="image-description">@lang('Beschreibung'): <span>{{ $image->alt }}</span></p>
                @endunless
                <div class="divider"></div>
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

    <div class="image-info-card-bottom list-group list-group-flush collapse mt-1"
         id="comm_link_container_@php if(isset($loop))echo $loop->index;@endphp">
        @unless($image->tags->isEmpty())
            <div class="tag-container">
                @foreach($image->tags as $tag)
                    <a class="badge badge-secondary m-0"
                       href="{{ route('web.rsi.comm-links.images.index-by-tag', $tag->getRouteKey()) }}"
                       title="{{ $tag->images_count }} @lang('Bilder mit diesem Tag')">
                        {{ $tag->translated_name }}
                    </a>
                @endforeach
            </div>
            <div class="divider"></div>
        @endunless
        <ul class="list-unstyled mb-0">
            <li><a class="url" href="{{ $image->url }}" target="_blank">@lang('Bildquelle')</a></li>
            <li><a class="url"
                   href="{{ route('web.rsi.comm-links.images.show', $image->getRouteKey()) }}">@lang('Dateiinfo')</a>
            </li>
            @if(Str::contains($image->metadata->mime, 'image') || Str::contains($image->metadata->mime, 'video'))
                <li><a class="url"
                       href="{{ route('web.rsi.comm-links.images.similar', $image->getRouteKey()) }}">@lang('Ähnliche Dateien (alpha)')</a>
                </li>
            @endif
        </ul>
        @unless($image->duplicates->isEmpty())
            <div class="divider"></div>
            <div>
                <p>@lang('Duplikate')</p>
                <ul class="list-unstyled mb-0">
                    @foreach($image->duplicates as $duplicate)
                        <li><a class="url"
                               href="{{ route('web.rsi.comm-links.images.show', $duplicate->getRouteKey()) }}">{{ $duplicate->name }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endunless
        @unless(empty($image->commLinks))
            <div class="divider"></div>
            <p>@lang('Comm-Links'):</p>
            <ul class="list-unstyled mb-0">
                @foreach($image->commLinks->sortByDesc('cig_id')->take(5) as $commLink)
                    <li>
                        <a class="url" href="{{ route('web.rsi.comm-links.show', $commLink->getRouteKey()) }}"
                           class="card-link">{{ $commLink->cig_id }} - {{ $commLink->title }}
                        </a>
                    </li>
                @endforeach
                @if($image->commLinks->count() > 5)
                    <li>@lang('Verwendet in') <b>{{ $image->commLinks->count() }}</b> @lang('Comm-Links')</li>
                @endif
            </ul>
        @endunless
    </div>

    <div class="card-footer">
        <a class="btn btn-block btn-secondary mt-1" data-toggle="collapse"
           href="#comm_link_container_@php if(isset($loop))echo $loop->index;@endphp" role="button"
           aria-expanded="false" aria-controls="comm_link_container_@php if(isset($loop))echo $loop->index;@endphp">
            @lang('Zeige mehr Infos')
        </a>
    </div>
</div>
