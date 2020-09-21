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
                            {{ $changelog->user_link }}
                        </td>
                        <td>
                            <a data-toggle="collapse" href="#details_{{$loop->index}}" role="button"
                               aria-expanded="false" aria-controls="#details_{{$loop->index}}">
                                <u>{{ __($changelog->type) }}</u>
                            </a>
                        </td>
                        <td>
                            <a href="{{ $changelog->model_route }}">
                            {{ class_basename($changelog->changelog_type) }}
                            </a>
                        </td>
                        <td data-content="{{ $changelog->created_at->format('d.m.Y H:i:s') }}" data-toggle="popover">
                            {{ $changelog->created_at->diffForHumans() }}
                        </td>
                    </tr>

                    <tr id="details_{{ $loop->index }}" class="collapse" style="overflow-y:scroll">
                        <td colspan="5">
                            @unless($changelog->model_route === '#')
                                <a href="{{ $changelog->model_route }}">{{ __('Änderungen') }} {{ __('Ansehen') }}</a>
                                <hr>
                            @endunless

                            <p class="mb-0">Details:</p>
                            <pre><code class="mb-0">{!! $changelog->formatted_changelog !!}</code></pre>
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
