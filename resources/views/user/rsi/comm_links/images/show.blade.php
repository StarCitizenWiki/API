@extends('user.layouts.default')

@section('title', __('Bild').' - '.$image->id)

@section('content')
    <div class="row">
        <div class="col-12 col-md-8">
            @include('user.rsi.comm_links.components.image_info_card', ['image' => $image, 'noFooter' => true])
        </div>
        <div class="col-12 col-md-4">
            <table class="table mb-0 table-responsive">
                <caption>@lang('Bild Metadaten')</caption>
                <tr>
                    <th scope="row">@lang('Beschreibung')</th>
                    <td>{{ $image->alt ?? '-' }}</td>
                </tr>
                <tr>
                    <th scope="row">@lang('Links')</th>
                    <td>
                        <ul class="list-unstyled mb-0">
                            <li><a class="url" href="{{ $image->url }}" target="_blank">@lang('Quelle')</a></li>
                            @if(\Illuminate\Support\Str::contains($image->metadata->mime, 'image'))
                                <li>
                                    <a class="url" href="{{ route('web.user.rsi.comm-links.images.similar', $image->getRouteKey()) }}">
                                        @lang('Ähnliche Bilder (alpha)')
                                    </a>
                                </li>
                            @endif
                            @can('web.user.rsi.comm-links.view')
                                <li>
                                    <a class="url upload-btn" data-id={{ $image->id }} data-cl-id="{{ $image->commLinks->pluck('cig_id')->min() }}" href="#">
                                        @lang('Hochladen ins Wiki')
                                    </a>
                                </li>
                                <li>
                                    <a class="url" title="@lang('Edit') @lang('Tags')" href="{{ route('web.user.rsi.comm-links.images.edit-tags', $image->getRouteKey()) }}">
                                        @lang('Edit') @lang('Tags')
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th scope="row">@lang('Größe')</th>
                    <td title="{{ $image->metadata->size }} bytes">{{ round($image->metadata->size / (1024 * 1024), 2) }} MB</td>
                </tr>
                <tr>
                    <th scope="row">@lang('Zuletzt geändert')</th>
                    <td>
                        <span>{{ $image->metadata->last_modified->format('d.m.Y H:i:s') }}</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">@lang('Tags')</th>
                    <td>
                        @forelse($image->tags as $tag)
                            <a class="badge badge-secondary m-0" href="{{ route('web.user.rsi.comm-links.images.index-by-tag', $tag->getRouteKey()) }}" title="{{ $tag->images_count }} @lang('Bilder mit diesem Tag')">
                                {{ $tag->translated_name }}
                            </a>
                        @empty
                            @lang('None')
                        @endforelse
                    </td>
                </tr>
                <tr>
                    <th scope="row">@lang('Duplikate')</th>
                    <td>
                        <ul class="list-unstyled mb-0">
                        @forelse($image->duplicates as $duplicate)
                            <li><a class="url" href="{{ route('web.user.rsi.comm.links.images.show', $duplicate->getRouteKey()) }}">{{ $duplicate->name }} ({{ $duplicate->similarity }}%)</a></li>
                        @empty
                            <li>@lang('Keine')</li>
                        @endforelse
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th scope="row">@lang('Comm-Links')</th>
                    <td>
                        <ul class="list-unstyled mb-0">
                        @forelse($image->commLinks->sortByDesc('cig_id')->take(5) as $commLink)
                            <li>
                                <a href="{{ route('web.user.rsi.comm-links.show', $commLink->getRouteKey()) }}"
                                   class="card-link url">{{ $commLink->cig_id }} - {{ $commLink->title }}
                                </a>
                            </li>
                        @empty
                            <li>@lang('Keine')</li>
                        @endforelse
                            @if($image->commLinks->count() > 5)
                                <li data-toggle="collapse" href="#all-cl" style="cursor: pointer" class="btn btn-outline-secondary">@lang('Zeige alle') <b>{{ $image->commLinks->count() }}</b> @lang('Comm-Links')</li>
                            @endif
                        </ul>
                        @if($image->commLinks->count() > 5)
                            <ul class="list-unstyled mb-0 collapse" id="all-cl">
                                @foreach($image->commLinks->skip(5) as $commLink)
                                    <li>
                                        <a href="{{ route('web.user.rsi.comm-links.show', $commLink->getRouteKey()) }}"
                                           class="card-link url">{{ $commLink->cig_id }} - {{ $commLink->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                </tr>
            </table>

        </div>
        @include('user.components.upload_modal')
    </div>

@endsection
