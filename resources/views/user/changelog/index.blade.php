@extends('user.layouts.default')

@section('title', __('Änderungsübersicht'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Änderungsübersicht')</h4>
        <div class="card-body px-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    @can('web.user.internals.view')
                        <th>@lang('ID')</th>
                    @endcan
                    <th>@lang('Benutzer')</th>
                    <th>@lang('Typ')</th>
                    <th>@lang('Model')</th>
                    <th>@lang('Datum')</th>
                </tr>
                </thead>
                <tbody>

                @forelse($changelogs as $changelog)
                    <tr>
                        @can('web.user.internals.view')
                            <td>
                                {{ $changelog->id }}
                            </td>
                        @endcan
                        <td>
                            @unless(null === $changelog->user)
                                <a href="{{ route('web.user.users.edit', $changelog->user->getRouteKey()) }}">{{ $changelog->user->username }}</a>
                            @else
                                {{ config('app.name') }}
                            @endunless
                        </td>
                        <td
                        @if($changelog->type === 'update')
                            @php
                            $str = [];
                            foreach($changelog->changelog['changes'] as $key => $change) {
                                $str[] = ucfirst($key).": ".wordwrap($change['old'], 40, "&hellip")." &rarr; ".wordwrap($change['new'], 40, "&hellip");
                            }
                            $str = implode('<br>', $str);
                            @endphp
                            title="Änderungen"
                            data-content="{!! $str !!}"
                            data-toggle="popover"
                            data-html="true"
                        @endif
                        >
                            @if($changelog->type === 'update')
                                <u style="cursor: pointer;">{{ __($changelog->type) }}</u>
                            @else
                                {{ __($changelog->type) }}
                            @endif
                        </td>
                        <td>
                            {{ class_basename($changelog->changelog_type) }}
                        </td>
                        <td data-content="{{ $changelog->created_at->format('d.m.Y H:i:s') }}" data-toggle="popover">
                            {{ $changelog->created_at->diffForHumans() }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">@lang('Keine Änderungen vorhanden')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $changelogs->links() }}</div>
    </div>
@endsection
