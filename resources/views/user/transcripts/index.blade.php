@extends('user.layouts.default_wide')

@section('title', __('Transkripte'))

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
            <h4 class="mb-0 pt-1">@lang('Transkripte')</h4>
            <span class="d-flex ml-auto">{{ $transcripts->links() }}</span>
        </div>
        <div class="card-body px-0 table-responsive">
            <table class="table table-striped mb-0" data-order='[[ 0, "desc" ]]' data-page-length="50" data-length-menu='[ [25, 50, 100, -1], [25, 50, 100, "Alle"] ]'>
                <thead>
                <tr>
                    @can('web.user.internals.view')
                        <th>@lang('ID')</th>
                    @endcan
                    <th>@lang('Wiki ID')</th>
                    <th>@lang('Titel')</th>
                    <th>@lang('Titel (Quelle)')</th>
                    <th>@lang('Format')</th>
                    <th>@lang('Übersetzt')</th>
                    <th>@lang('Veröffentlichung')</th>
                    <th data-orderable="false">&nbsp;</th>
                </tr>
                </thead>
                <tbody>

                @forelse($transcripts as $transcript)
                    <tr>
                        @can('web.user.internals.view')
                            <td>
                                {{ $transcript->id }}
                            </td>
                        @endcan
                        <td>
                            {{ $transcript->wiki_id ?? '-' }}
                        </td>
                        <td>
                            {{ $transcript->title ?? '' }}
                        </td>
                        <td>
                            {{ $transcript->source_title ?? '' }}
                        </td>
                        <td>
                            {{ $transcript->format->name }}
                        </td>
                        @php
                            if (null !== $transcript->german()) {
                                $status = 'warning';
                                $text = 'Automatisch';
                                if ($transcript->german()->proofread === 1) {
                                    $status = 'success';
                                    $text = 'Ja';
                                }
                            } else {
                                $status = 'danger';
                                $text = 'Nein';
                                if (empty($transcript->english()->translation)) {
                                    $status = 'normal';
                                    $text = '-';
                                }
                            }
                        @endphp
                        <td class="text-{{ $status }}">
                            {{ $text }}
                        </td>
                        @if(null === $transcript->published_at)
                            <td data-content="{{ $transcript->created_at->format('d.m.Y') }}" data-toggle="popover" data-search="{{ $transcript->created_at->format('d.m.Y') }}" data-sort="{{ $transcript->created_at->timestamp }}">
                                {{ $transcript->created_at->diffForHumans() }}
                            </td>
                        @else
                            <td data-content="{{ $transcript->published_at->format('d.m.Y') }}" data-toggle="popover" data-search="{{ $transcript->published_at->format('d.m.Y') }}" data-sort="{{ $transcript->published_at->timestamp }}">
                                {{ $transcript->published_at->diffForHumans() }}
                            </td>
                        @endif
                        <td class="text-center">
                            @component('components.edit_delete_block')
                                {{-- @slot('show_url')
                                    {{ route('web.user.transcripts.show', $transcript->getRouteKey()) }}
                                @endslot --}}
                                @can('web.user.transcripts.update')
                                    @slot('edit_url')
                                        {{ route('web.user.transcripts.edit', $transcript->getRouteKey()) }}
                                    @endslot
                                @endcan
                                {{ $transcript->getRouteKey() }}
                            @endcomponent
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12">@lang('Keine Transkripte vorhanden')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $transcripts->links() }}</div>
    </div>
@endsection

@section('body__after')
    @parent
    @if(count($transcripts) > 0)
        @include('components.init_dataTables')
    @endunless
@endsection