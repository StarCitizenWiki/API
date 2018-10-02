@extends('user.layouts.default_wide')

@section('title', __('Comm Link').' - '.$commLink->title)

@section('head__content')
    @parent
    <style>
        .card-body p {
            margin-bottom: 0;
        }
    </style>
@endsection

@section('content')
    <div class="d-flex mb-3">
        @unless(null === $prev)
            <a href="{{ route('web.user.rsi.comm-links.show', $prev) }}" class="btn btn-outline-secondary">@lang('Vorheriger')</a>
        @else
            <a href="#" class="btn btn-outline-secondary disabled">@lang('Vorheriger')</a>
        @endunless
        @unless(null === $next)
            <a href="{{ route('web.user.rsi.comm-links.show', $next) }}" class="btn btn-outline-secondary ml-auto">@lang('Nächste')</a>
        @else
            <a href="#" class="btn btn-outline-secondary disabled ml-auto">@lang('Nächste')</a>
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
            <nav class="mb-3">
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <a class="nav-item nav-link active" id="nav-en_EN-tab" data-toggle="tab" href="#nav-en_EN" role="tab" aria-controls="nav-en_EN" aria-selected="true">
                        @lang('en_EN')
                    </a>
                    <a class="nav-item nav-link" id="nav-de_DE-tab" data-toggle="tab" href="#nav-de_DE" role="tab" aria-controls="nav-de_DE" aria-selected="false">
                        @lang('de_DE')
                    </a>
                    <a class="nav-item nav-link" id="nav-links-tab" data-toggle="tab" href="#nav-links" role="tab" aria-controls="nav-links" aria-selected="false">
                        @lang('Links') <span class="badge badge-primary">{{ count($commLink->links) }}</span>
                    </a>
                    <a class="nav-item nav-link" id="nav-images-tab" data-toggle="tab" href="#nav-images" role="tab" aria-controls="nav-images" aria-selected="false">
                        @lang('Bilder') <span class="badge badge-primary">{{ count($commLink->images) }}</span>
                    </a>
                    <a class="nav-item nav-link" id="nav-meta-tab" data-toggle="tab" href="#nav-meta" role="tab" aria-controls="nav-meta" aria-selected="false">
                        @lang('Metadaten')
                    </a>
                    <a class="nav-item nav-link" id="nav-changelog-tab" data-toggle="tab" href="#nav-changelog" role="tab" aria-controls="nav-changelog" aria-selected="false">
                        @lang('Verlauf')
                    </a>
                    @can('web.user.rsi.comm-links.update')
                    <a class="nav-item nav-link" aria-selected="false" href="{{ route('web.user.rsi.comm-links.edit', $commLink->getRouteKey()) }}">
                        @lang('Bearbeiten')
                    </a>
                    @endcan
                </div>
            </nav>

            <div class="tab-content" id="nav-tab-translations">
                <div class="tab-pane fade show active" id="nav-en_EN" role="tabpanel" aria-labelledby="nav-en_EN-tab">
                    {!! empty($commLink->english()->translation) ? 'Nicht vorhanden' : $commLink->english()->translation !!}
                </div>
                <div class="tab-pane fade" id="nav-de_DE" role="tabpanel" aria-labelledby="nav-de_DE-tab">
                    <div class="alert alert-warning text-center">
                        @lang('Achtung! Durch das Benutzen des Textes stimmst du der Star Citizen Wiki Übersetzungsvereinbarung zu.')
                        <a href="{{ config('api.wiki_url') }}/Star_Citizen_Wiki:%C3%9Cbersetzungsvereinbarung">@lang('Weitere Informationen hier')</a>
                    </div>
                    {!! optional($commLink->german())->translation ?? 'Nicht vorhanden' !!}
                </div>
                <div class="tab-pane fade" id="nav-links" role="tabpanel" aria-labelledby="nav-links-tab">
                    <ul>
                        @forelse($commLink->links as $link)
                            <li><a href="{{ $link->href }}" target="_blank">{{ $link->text }}</a> &mdash; {{ $link->href }}</li>
                        @empty
                            <li>Keine Links vorhanden</li>
                        @endforelse
                    </ul>
                </div>
                <div class="tab-pane fade" id="nav-images" role="tabpanel" aria-labelledby="nav-images-tab">
                    @forelse($commLink->images as $image)
                        <a class="" href="{{ $image->getLocalOrRemoteUrl() }}" target="_blank"><img src="{{ str_replace('source', 'post', $image->url) }}" class="img-thumbnail" style="max-width: 150px;"></a>
                    @empty
                        Keine Bilder vorhanden
                    @endforelse
                </div>
                <div class="tab-pane fade" id="nav-meta" role="tabpanel" aria-labelledby="nav-meta-tab">
                    <table class="table mb-0">
                        <tr>
                            <th class="border-top-0">ID</th>
                            <td class="border-top-0">{{ $commLink->cig_id }}</td>
                        </tr>
                        <tr>
                            <th>Veröffentlichung</th>
                            <td>{{ $commLink->created_at->format('d.m.Y') }}</td>
                        </tr>
                        <tr>
                            <th>Kategorie</th>
                            <td>
                                <a href="{{ route('web.user.rsi.comm-links.categories.show', $commLink->category->getRouteKey()) }}">
                                    {{ $commLink->category->name }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Channel</th>
                            <td>
                                <a href="{{ route('web.user.rsi.comm-links.channels.show', $commLink->channel->getRouteKey()) }}">
                                    {{ $commLink->channel->name }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Serie</th>
                            <td>
                                <a href="{{ route('web.user.rsi.comm-links.series.show', $commLink->series->getRouteKey()) }}">
                                    {{ $commLink->series->name }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Url</th>
                            <td>
                                <a href="https://robertsspaceindustries.com{{ $commLink->url ?? "/comm-link/SCW/{$commLink->cig_id}-API" }}" target="_blank">
                                    {{ $commLink->url ?? 'Keine Original URL vorhanden' }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Kommentare</th>
                            <td>{{ $commLink->comment_count }}</td>
                        </tr>
                    </table>
                </div>
                <div class="tab-pane fade" id="nav-changelog" role="tabpanel" aria-labelledby="nav-changelog-tab">
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
                                Comm Link
                                @if($changelog->type === 'creation')
                                    importiert von
                                @else
                                    @unless(empty($changelog->changelog->get('changes', [])))
                                        <span title="{{ implode(array_keys($changelog->changelog->get('changes', [])), ', ') }}"><u>aktualisiert</u></span> durch
                                    @else
                                        aktualisiert durch
                                    @endunless
                                @endif
                            @endif
                            <a href="{{ optional($changelog->admin)->userNameWikiLink() ?? '#' }}" target="_blank">
                                {{ optional($changelog->admin)->username ?? config('app.name') }}
                            </a>
                            <span>
                                {{ $changelog->created_at->diffForHumans() }} &mdash; {{ $changelog->created_at->format('d.m.Y H:i') }}
                            </span>
                        </li>
                    @empty
                        <li>Keine Änderungen vorhanden</li>
                    @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
