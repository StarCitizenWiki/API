@extends('user.layouts.default_wide')

@section('title', __('Artikel').' - '.$article->cleanTitle)

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
            <a href="{{ route('web.user.starcitizen.galactapedia.show', $prev) }}" class="btn btn-outline-secondary">@lang('Vorheriger')</a>
        @else
            <a href="#" class="btn btn-outline-secondary disabled">@lang('Vorheriger')</a>
        @endunless
        @unless(null === $next)
            <a href="{{ route('web.user.starcitizen.galactapedia.show', $next) }}" class="btn btn-outline-secondary ml-auto">@lang('Nächster')</a>
        @else
            <a href="#" class="btn btn-outline-secondary disabled ml-auto">@lang('Nächster')</a>
        @endunless
    </div>
    <div class="card">
        <div class="card-header">
            <h4>
                {{ $article->cleanTitle }}
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

                            @unless(empty($article->english()->translation))
                                <a class="nav-item nav-link" id="nav-de_DE-tab" href="{{ config('api.wiki_url') }}/{{ urlencode(str_replace(' ', '_', $article->cleanTitle)) }}" aria-selected="false" target="_blank">
                                    @lang('de_DE') <em class="fa fa-external-link-alt fa-sm" data-fa-transform="up-2"></em>
                                </a>
                            @endunless

                            <a class="nav-item nav-link" id="nav-api-tab" href="{{ route('api.v2.galactapedia.show', $article->cig_id) }}" aria-selected="false" target="_blank">
                                @lang('API') <em class="fa fa-external-link-alt fa-sm" data-fa-transform="up-2"></em>
                            </a>

                            <a class="nav-item nav-link" id="nav-wikitext-tab" data-toggle="tab" href="#wikitext" role="tab" aria-controls="wikitext" aria-selected="false">
                                @lang('Wikitext')
                            </a>

                            <a class="nav-item nav-link" id="nav-changelog-tab" data-toggle="tab" href="#changelog" role="tab" aria-controls="changelog" aria-selected="false">
                                @lang('Aktualisierungen')
                            </a>

                            <a class="nav-item nav-link" id="nav-textchanges-tab" data-toggle="tab" href="#textchanges" role="tab" aria-controls="textchanges" aria-selected="false">
                                @lang('Textänderungen')
                            </a>

                            <a class="nav-item nav-link" href="{{ config('api.wiki_url') }}/{{ $article->title }}?veaction=edit" aria-selected="false">
                                @lang('Bearbeiten') <em class="fa fa-external-link-alt fa-sm" data-fa-transform="up-2"></em>
                            </a>

                            @can('web.user.rsi.comm-links.update')
                                <a class="nav-item nav-link" id="nav-deepl-tab" aria-selected="false" data-toggle="tab" href="#deepl" role="tab" aria-controls="deepl">
                                    @lang('DeepL Übersetzung')
                                </a>
                            @endcan
                        </div>
                    </nav>

                    <div class="tab-content" id="nav-tab-translations">
                        <div class="tab-pane fade show active" id="english" role="tabpanel" aria-labelledby="nav-en_EN-tab">
                            {!! empty($article->english()->translation) ? __('Nicht vorhanden') : nl2br(\App\Models\StarCitizen\Galactapedia\Article::normalizeContent($article->english()->translation)) !!}
                        </div>

                        <div class="tab-pane fade" id="changelog" role="tabpanel" aria-labelledby="nav-changelog-tab">
                            @component('user.components.changelog_list', [
                                'changelogs' => $changelogs,
                            ])
                                @lang('Artikel')
                            @endcomponent
                        </div>

                        <div class="tab-pane fade" id="textchanges" role="tabpanel" aria-labelledby="nav-textchanges-tab">
                            @forelse($changelogs->filter(static function($value, $key) {
                                return $value->type === 'update' && !empty($value->diff);
                            }) as $changelog)
                                <div class="mt-4">
                                    <h6>{{ $article->created_at->format('d.m.Y H:i:s') }} -> {{ $changelog->created_at->format('d.m.Y H:i:s') }}</h6>
                                    <pre class="mt-2 bg-light p-3" id="change-{{ $changelog->getRouteKey() }}"><code>{{ $changelog->diff }}</code></pre>
                                </div>
                            @empty
                                <p>@lang('Keine Textänderungen vorhanden')</p>
                            @endforelse
                        </div>

                        <div class="tab-pane fade" id="wikitext" role="tabpanel" aria-labelledby="nav-wikitext-tab">
                            <pre>{!! htmlspecialchars($wikitext) !!}</pre>
                        </div>

                        @can('web.user.rsi.comm-links.update')
                            <div class="tab-pane fade" id="deepl" role="tabpanel" aria-labelledby="nav-deepl-tab">
                                {!! empty($article->german()->translation) ? __('Nicht vorhanden') : nl2br($article->german()->translation) !!}
                            </div>
                        @endcan
                    </div>
                </div>
                <div class="col-12 col-xl-4">
                    <table class="table mb-0 table-responsive">
                        <caption>@lang('Artikel Metadaten')</caption>
                        <tr>
                            <th colspan="2" scope="row" class="border-top-0">
                                <img class="img-fluid" width="400" src="{{ $article->thumbnail }}" alt="{{ $article->title }}">
                            </th>
                        </tr>
                        <tr>
                            <th class="border-top-0" scope="row">@lang('ID')</th>
                            <td class="border-top-0">{{ $article->cig_id }}</td>
                        </tr>
                        <tr>
                            <th scope="row">@lang('Kategorien')</th>
                            <td>
                                {!! $article->categories->map(function($cat){return $cat->name;})->implode('<br>') !!}
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">@lang('Tags')</th>
                            <td>
                                {!! $article->tags->map(function($tag){return sprintf('<span class="badge badge-info">#%s</span>', $tag->name);})->implode('<br>') !!}
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">@lang('Eigenschaften')</th>
                            <td>
                                <table class="table table-borderless table-sm table-responsive">
                                    @forelse($article->properties as $property)
                                        <tr>
                                            <th scope="row">{{ $property->name }}</th>
                                            <td class="text-break">{{ $property->content }}</td>
                                        </tr>
                                    @empty
                                        -
                                    @endforelse
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">@lang('URL')</th>
                            <td>
                                <a href="{{ $article->url }}" target="_blank" class="text-break">
                                    {{ $article->url ?? __('Keine Original URL vorhanden') }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">@lang('Verwandte Artikel')</th>
                            <td>
                                @forelse($article->related as $related)
                                    <a href="{{ route('web.user.starcitizen.galactapedia.show', $related->getRouteKey()) }}" style="word-break: break-all">
                                        {{ $related->title }}
                                    </a>
                                    <br>
                                    @empty
                                        -
                                @endforelse
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
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

            let html = document.getElementById('english').innerText

            document.getElementById('english').innerHTML = snarkdown(
                html.replace(
                    /\(https:\/\/robertsspaceindustries.com\/galactapedia\/article\/(.{10})(?:[\w-]+)?\)/g,
                    '(/starcitizen/galactapedia/$1)'
                )
            )
        })
    </script>
@endsection