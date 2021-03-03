@extends('user.layouts.default_wide')

@section('title', __('Galactapedia'))

@section('head__content')
    @parent
    <style>
        span ul {
            margin-bottom: 0;
        }
    </style>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex">
            <h4 class="mb-0 pt-1">@lang('Galactapedia')</h4>
            @unless(empty($articles) || !method_exists($articles, 'links'))
            <span class="d-flex ml-auto">{{ $articles->links() }}</span>
            @endunless
        </div>
        <div class="card-body px-0 table-responsive">
            <table class="table table-striped mb-0" data-order='[[ 0, "desc" ]]' data-page-length="50" data-length-menu='[ [25, 50, 100, -1], [25, 50, 100, "Alle"] ]'>
                <thead>
                <tr>
                    @can('web.user.internals.view')
                        <th>@lang('ID')</th>
                    @endcan
                    <th>@lang('CIG ID')</th>
                    <th>@lang('Titel')</th>
                    <th>@lang('Typ')</th>
                    <th>@lang('Kategorien')</th>
                    <th>@lang('Tags')</th>
                    <th>@lang('Eigenschaften')</th>
                    <th>@lang('Related')</th>
                    <th>@lang('Inhalt')</th>
                    <th>@lang('Übersetzt')</th>
                    <th>@lang('Erstellt')</th>
                    @if(isset($appends) && !empty($appends))
                        @foreach($appends as $append)
                            <th>{{$append}}</th>
                        @endforeach
                    @endif
                    <th data-orderable="false">&nbsp;</th>
                </tr>
                </thead>
                <tbody>

                @forelse($articles as $article)
                    <tr>
                        @can('web.user.internals.view')
                            <td>
                                {{ $article->id }}
                            </td>
                        @endcan
                        <td>
                            {{ $article->cig_id }}
                        </td>
                        <td>
                            {{ $article->cleanTitle }}
                        </td>
                        <td>
                            {{ $article->templates->isEmpty() ? '-' : $article->templates[0]->template }}
                        </td>
                        <td>
                            {{ $article->categories->count() }}
                        </td>
                        <td>
                            {{ $article->tags->count() }}
                        </td>
                        <td>
                            {{ $article->properties->count() }}
                        </td>
                        <td>
                            {{ $article->related->count() }}
                        </td>
                        <td>
                            {{ optional($article->english())->translation ? 'Ja' : 'Nein' }}
                        </td>
                        @php
                            if (null !== $article->german()) {
                                $status = 'warning';
                                $text = 'Automatisch';
                                if ($article->german()->proofread === 1) {
                                    $status = 'success';
                                    $text = 'Ja';
                                }
                            } else {
                                $status = 'danger';
                                $text = 'Nein';
                                if (empty($article->english()->translation)) {
                                    $status = 'normal';
                                    $text = '-';
                                }
                            }
                        @endphp
                        <td class="text-{{ $status }}">
                            {{ $text }}
                        </td>
                        <td data-content="{{ $article->created_at->format('d.m.Y H:i:s') }}" data-toggle="popover">
                            {{ $article->created_at->diffForHumans() }}
                        </td>
                        @if(isset($appends) && !empty($appends))
                            @foreach($appends as $append)
                                <td>{{$article->$append}}</td>
                            @endforeach
                        @endif
                        <td class="text-center">
                            @component('components.edit_delete_block')
                                @slot('show_url')
                                    {{ route('web.user.starcitizen.galactapedia.show', $article->getRouteKey()) }}
                                @endslot
                                @can('web.user.starcitizen.galactapedia.update')
                                    @slot('edit_url')
                                        {{ route('web.user.starcitizen.galactapedia.edit', $article->getRouteKey()) }}
                                    @endslot
                                @endcan
                                {{ $article->getRouteKey() }}
                            @endcomponent
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12">@lang('Keine Artikel vorhanden')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @unless(empty($articles) || !method_exists($articles, 'links'))
        <div class="card-footer">{{ $articles->links() }}</div>
        @endunless
    </div>
@endsection

@section('body__after')
    @parent
    @if(count($articles) > 0)
        @include('components.init_dataTables')
    @endunless
@endsection