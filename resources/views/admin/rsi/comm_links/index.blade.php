@extends('admin.layouts.default_wide')

@section('title', __('Comm Links'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Comm Links')</h4>
        <div class="card-body px-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    @can('web.admin.starcitizen.vehicles.update')
                        <th>@lang('ID')</th>
                    @endcan
                    <th>@lang('CIG ID')</th>
                    <th>@lang('Titel')</th>
                    <th>@lang('Kommentare')</th>
                    <th>@lang('Inhalt')</th>
                    <th>@lang('Channel')</th>
                    <th>@lang('Kategorie')</th>
                    <th>@lang('Serie')</th>
                    <th>@lang('Veröffentlichung')</th>
                    @can('web.admin.rsi.comm_links.update')
                        <th data-orderable="false">&nbsp;</th>
                    @endcan
                </tr>
                </thead>
                <tbody>

                @forelse($commLinks as $commLink)
                    <tr>
                        @can('web.admin.internals.view')
                            <td>
                                {{ $commLink->id }}
                            </td>
                        @endcan
                        <td>
                            <a href="https://robertsspaceindustries.com/comm-link/SCW/{{ $commLink->cig_id }}-API" target="_blank">{{ $commLink->cig_id }}</a>
                        </td>
                        <td>
                            {{ $commLink->title }}
                        </td>
                        <td>
                            {{ $commLink->comment_count }}
                        </td>
                        <td>
                            {{ $commLink->english()->translation ? 'Ja' : 'Nein' }}
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
                        <td title="{{ $commLink->created_at->format('d.m.Y') }}">
                            {{ $commLink->created_at->diffForHumans() }}
                        </td>
                        @can('web.admin.starcitizen.vehicles.update')
                            <td class="text-center">
                                @component('components.edit_delete_block')
                                    @slot('edit_url')
                                        {{ route('web.admin.rsi.comm_links.edit', $commLink->getRouteKey()) }}
                                    @endslot
                                    {{ $commLink->getRouteKey() }}
                                @endcomponent
                            </td>
                        @endcan
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">@lang('Keine Comm Links vorhanden')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $commLinks->links() }}</div>
    </div>
@endsection