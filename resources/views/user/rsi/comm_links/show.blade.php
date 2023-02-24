@extends('user.layouts.default_wide')

@section('title', __('Comm-Link').' - '.$commLink->title)

@section('head__content')
    @parent
    <style>
        .card-body p {
            margin-bottom: 0;
        }
    </style>
@endsection

@section('content')
    <div class="d-flex mb-3 nav-bar">
        @unless(null === $prev)
            <a href="{{ route('web.user.rsi.comm-links.show', $prev) }}" class="btn btn-outline-secondary">@lang('Vorheriger')</a>
        @else
            <a href="#" class="btn btn-outline-secondary disabled">@lang('Vorheriger')</a>
        @endunless
        @unless(null === $next)
            <a href="{{ route('web.user.rsi.comm-links.show', $next) }}" class="btn btn-outline-secondary ml-auto">@lang('Nächster')</a>
        @else
            <a href="#" class="btn btn-outline-secondary disabled ml-auto">@lang('Nächster')</a>
        @endunless
    </div>
    <div class="card">
        <div class="card-header">
            <h4>
                {{ $commLink->title }}
            </h4>
        </div>
        <div class="card-body">
            @include('components.messages')
            <div class="row">
                <div class="col-12 col-xl-8">
                    <nav class="mb-3">
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                            <a class="nav-item nav-link active" id="nav-en_EN-tab" data-toggle="tab" href="#english" role="tab" aria-controls="english" aria-selected="true">
                                @lang('en_EN')
                            </a>

                            @unless(empty($commLink->english()->translation))
                            <a class="nav-item nav-link" id="nav-de_DE-tab" href="{{ config('api.wiki_url') }}/Comm-Link:{{ $commLink->cig_id }}" aria-selected="false" target="_blank">
                                @lang('de_DE') <em class="fa fa-external-link-alt fa-sm" data-fa-transform="up-2"></em>
                            </a>
                            @endunless

                            <a class="nav-item nav-link" id="nav-api-tab" href="{{ app('api.url')->version('v1')->route('api.v1.rsi.comm-links.show', $commLink->cig_id) }}" aria-selected="false" target="_blank">
                                @lang('API') <em class="fa fa-external-link-alt fa-sm" data-fa-transform="up-2"></em>
                            </a>

                            <a class="nav-item nav-link" id="nav-links-tab" data-toggle="tab" href="#links" role="tab" aria-controls="links" aria-selected="false">
                                @lang('Links') <span class="badge badge-primary">{{ count($commLink->links) }}</span>
                            </a>

                            <a class="nav-item nav-link" id="nav-images-tab" data-toggle="tab" href="#images" role="tab" aria-controls="images" aria-selected="false">
                                @lang('Bilder') <span class="badge badge-primary">{{ count($commLink->images) }}</span>
                            </a>

                            <a class="nav-item nav-link" id="nav-changelog-tab" data-toggle="tab" href="#changelog" role="tab" aria-controls="changelog" aria-selected="false">
                                @lang('Aktualisierungen')
                            </a>

                            <a class="nav-item nav-link" id="nav-textchanges-tab" data-toggle="tab" href="#textchanges" role="tab" aria-controls="textchanges" aria-selected="false">
                                @lang('Textänderungen')
                            </a>

                            <a class="nav-item nav-link" href="{{ config('api.wiki_url') }}/Comm-Link:{{ $commLink->cig_id }}?veaction=edit" aria-selected="false">
                                @lang('Bearbeiten') <em class="fa fa-external-link-alt fa-sm" data-fa-transform="up-2"></em>
                            </a>

                            @can('web.user.rsi.comm-links.update')
                            <a class="nav-item nav-link" aria-selected="false" href="{{ route('web.user.rsi.comm-links.edit', $commLink->getRouteKey()) }}">
                                @lang('Metadaten') @lang('bearbeiten')
                            </a>
                            <a class="nav-item nav-link" id="nav-deepl-tab" aria-selected="false" data-toggle="tab" href="#deepl" role="tab" aria-controls="deepl">
                                @lang('DeepL Übersetzung')
                            </a>
                            @endcan
                        </div>
                    </nav>

                    <div class="tab-content" id="nav-tab-translations">
                        <div class="tab-pane fade show active" id="english" role="tabpanel" aria-labelledby="nav-en_EN-tab">
                            {!! empty($commLink->english()->translation) ? __('Nicht vorhanden') : nl2br($commLink->english()->translation) !!}
                        </div>

                        <div class="tab-pane fade" id="links" role="tabpanel" aria-labelledby="nav-links-tab">
                            <ul>
                                @forelse($commLink->links as $link)
                                    <li><a href="{{ $link->href }}" target="_blank">{{ $link->text }}</a> &mdash; {{ $link->href }}</li>
                                @empty
                                    <li>@lang('Keine Links vorhanden')</li>
                                @endforelse
                            </ul>
                        </div>

                        <div class="tab-pane fade" id="images" role="tabpanel" aria-labelledby="nav-images-tab">
                            @unless(empty($commLink->images))
                                <div class="card-columns image-card-column images-show" style="column-count: 3">
                                    @foreach($commLink->images as $image)
                                        @include('user.rsi.comm_links.components.image_info_card', ['image' => $image])
                                    @endforeach
                                </div>
                            @else
                                @lang('Keine Bilder vorhanden')
                            @endif
                        </div>

                        <div class="tab-pane fade" id="changelog" role="tabpanel" aria-labelledby="nav-changelog-tab">
                            @component('user.components.changelog_list', [
                                'changelogs' => $changelogs,
                            ])
                                Comm-Link
                            @endcomponent
                        </div>

                        <div class="tab-pane fade" id="textchanges" role="tabpanel" aria-labelledby="nav-textchanges-tab">
                            @forelse($changelogs->filter(static function($value, $key) {
                                return $value->type === 'update' && !empty($value->diff);
                            }) as $changelog)
                                <div class="mt-4">
                                    <h6>{{ $commLink->created_at->format('d.m.Y H:i:s') }} -> {{ $changelog->created_at->format('d.m.Y H:i:s') }}</h6>
                                    <pre class="mt-2 bg-light p-3" id="change-{{ $changelog->getRouteKey() }}"><code>{{ $changelog->diff }}</code></pre>
                                </div>
                            @empty
                                <p>@lang('Keine Textänderungen vorhanden')</p>
                            @endforelse
                        </div>

                        @can('web.user.rsi.comm-links.update')
                            <div class="tab-pane fade" id="deepl" role="tabpanel" aria-labelledby="nav-deepl-tab">
                                {!! empty($commLink->german()->translation) ? __('Nicht vorhanden') : nl2br($commLink->german()->translation) !!}
                            </div>
                        @endcan
                    </div>
                </div>
                <div class="col-12 col-xl-4 mt-xl-0 mt-5">
                    <table class="table mb-0 table-responsive">
                        <caption>@lang('Comm-Link Metadaten')</caption>
                        <tr>
                            <th scope="row" class="border-top-0">ID</th>
                            <td class="border-top-0">{{ $commLink->cig_id }}</td>
                        </tr>
                        <tr>
                            <th scope="row">@lang('Veröffentlichung')</th>
                            <td>{{ $commLink->created_at->format('d.m.Y') }}</td>
                        </tr>
                        <tr>
                            <th scope="row">@lang('Kategorie')</th>
                            <td>
                                <a href="{{ route('web.user.rsi.comm-links.categories.show', $commLink->category->getRouteKey()) }}">
                                    {{ $commLink->category->name }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">@lang('Channel')</th>
                            <td>
                                <a href="{{ route('web.user.rsi.comm-links.channels.show', $commLink->channel->getRouteKey()) }}">
                                    {{ $commLink->channel->name }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">@lang('Serie')</th>
                            <td>
                                <a href="{{ route('web.user.rsi.comm-links.series.show', $commLink->series->getRouteKey()) }}">
                                    {{ $commLink->series->name }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">@lang('Url')</th>
                            <td>
                                <a href="https://robertsspaceindustries.com{{ $commLink->url ?? "/comm-link/SCW/{$commLink->cig_id}-API" }}" target="_blank" style="word-break: break-all">
                                    {{ $commLink->url ?? __('Keine Original URL vorhanden') }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">@lang('Kommentare')</th>
                            <td>{{ $commLink->comment_count }}</td>
                        </tr>
                    </table>
                </div>
        </div>
    </div>
    @include('user.components.upload_modal')
@endsection

@section('body__after')
    @parent
    <script>
        const updateNavHash = (hash) => {
            const links = document.querySelectorAll('.nav-bar a');
            links.forEach(link => {
                link.href = link.href.split('#')[0] + hash;
            });
        }

        const hoverVideoPlay = () => {
            document.querySelectorAll('video').forEach(video => {
                video.addEventListener('mouseenter', () => {
                    video.play();
                });

                video.addEventListener('mouseleave', () => {
                    video.pause();
                });
            })
        }

        document.addEventListener('DOMContentLoaded', function() {
            let url = location.href.replace(/\/$/, '');

            if (location.hash) {
                const hash = url.split('#');
                $('#nav-tab a[href="#' + hash[1] + '"]').tab('show');
                url = location.href.replace(/\/#/, '#');
                history.replaceState(null, null, url);
                updateNavHash('#'+hash[1]);
            }

            $('a[data-toggle="tab"]').on('click', function () {
                let newUrl;
                const hash = $(this).attr('href');
                newUrl = url.split('#')[0] + hash;
                updateNavHash(hash);

                history.replaceState(null, null, newUrl)
            })

            document.querySelectorAll('.badge.last-modified').forEach(entry => {
                entry.addEventListener('click', () => {
                    navigator.clipboard.writeText(entry.dataset.lastModified);
                });
            });

            hoverVideoPlay();
        })
    </script>
@endsection