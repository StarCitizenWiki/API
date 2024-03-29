@extends('web.layouts.default_wide')

@section('title', __('Comm-Links'))

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
            <h4 class="mb-0 pt-1">@lang('Comm-Links')</h4>
            @unless(empty($commLinks) || !method_exists($commLinks, 'links'))
                <span class="d-flex ml-auto">{{ $commLinks->links() }}</span>
            @endunless
        </div>
        <div class="card-body px-0 table-responsive">
            <table class="table table-striped mb-0"
                   @unless(isset($relevanceSorted))
                       data-order='[[ 0, "desc" ]]'
                   @else
                       data-order=''
                   @endif
                   data-page-length="50" data-length-menu='[ [25, 50, 100, -1], [25, 50, 100, "@lang('Alle')"] ]'>
                <thead>
                    <tr>
                        @can('web.internals.view')
                            <th>@lang('ID')</th>
                        @endcan
                        <th>@lang('CIG ID')</th>
                        <th>@lang('Titel')</th>
                        <th>@lang('Bilder')</th>
                        <th>@lang('Links')</th>
                        <th>@lang('Inhalt')</th>
                        <th>@lang('Übersetzt')</th>
                        <th>@lang('Channel')</th>
                        <th>@lang('Kategorie')</th>
                        <th>@lang('Serie')</th>
                        <th>@lang('Veröffentlichung')</th>
                        @if(isset($appends) && !empty($appends))
                            @foreach($appends as $append)
                                <th>{{$append}}</th>
                            @endforeach
                        @endif
                        <th data-orderable="false"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($commLinks as $commLink)
                    <tr>
                        @can('web.internals.view')
                            <td>
                                {{ $commLink->id }}
                            </td>
                        @endcan
                        <td>
                            <a href="{{config('api.rsi_url') }}{{ $commLink->url ?? "/SCW/{$commLink->cig_id}-API" }}" target="_blank">{{ $commLink->cig_id }}</a>
                        </td>
                        <td>
                            {{ $commLink->title }}
                        </td>
                        <td>
                            {{ $commLink->images_count }}
                        </td>
                        <td>
                            {{ $commLink->links_count }}
                        </td>
                        <td>
                            {{ optional($commLink->english())->translation ? __('Ja') : __('Nein') }}
                        </td>
                        @php
                            if (null !== $commLink->german()) {
                                $status = 'warning';
                                $text = __('Automatisch');
                                if ($commLink->german()->proofread === 1) {
                                    $status = 'success';
                                    $text = __('Ja');
                                }
                            } else {
                                $status = 'danger';
                                $text = __('Nein');
                                if (empty($commLink->english()->translation)) {
                                    $status = 'normal';
                                    $text = '-';
                                }
                            }
                        @endphp
                        <td class="text-{{ $status }}">
                            {{ $text }}
                        </td>
                        <td>
                            {{ $commLink->channel->name }}
                        </td>
                        <td>
                            {{ $commLink->category->name }}
                        </td>
                        <td>
                            {{ $commLink->series->name }}
                        </td>
                        <td data-content="{{ $commLink->created_at->format('d.m.Y') }}" data-toggle="popover" data-search="{{ $commLink->created_at->format('d.m.Y') }}" data-sort="{{ $commLink->created_at->timestamp }}">
                            {{ $commLink->created_at->diffForHumans() }}
                        </td>
                            @if(isset($appends) && !empty($appends))
                                @foreach($appends as $append)
                                    <td>{{$commLink->$append}}</td>
                                @endforeach
                            @endif
                        <td class="text-center">
                            @component('components.edit_delete_block')
                                @slot('show_url')
                                    {{ route('web.rsi.comm-links.show', $commLink->getRouteKey()) }}
                                @endslot
                                @can('web.rsi.comm-links.update')
                                    @slot('edit_url')
                                        {{ route('web.rsi.comm-links.edit', $commLink->getRouteKey()) }}
                                    @endslot
                                @endcan
                                {{ $commLink->getRouteKey() }}
                            @endcomponent
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12">@lang('Keine Comm-Links vorhanden')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @unless(empty($commLinks) || !method_exists($commLinks, 'links'))
        <div class="card-footer">{{ $commLinks->links() }}</div>
        @endunless
    </div>
@endsection

@section('body__after')
    @parent
    @if(count($commLinks) > 0)
        @include('components.init_dataTables')
    @endunless
@endsection