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
                        <td>
                            {{ __($changelog->type) }}
                        </td>
                        <td>
                            {{ class_basename($changelog->changelog_type) }}
                        </td>
                        <td>
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
