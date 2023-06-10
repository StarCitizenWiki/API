@extends('user.layouts.default_wide')

@section('title', __('Fehlgeschlagene Jobs'))

@section('content')
    <div class="card">
        <h4 class="card-header">@lang('Fehlgeschlagene Jobs')</h4>
        <div class="card-body px-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>@lang('ID')</th>
                        <th>@lang('Verbindung')</th>
                        <th>@lang('Queue')</th>
                        <th>@lang('Payload') / @lang('Exception')</th>
                        <th>@lang('Datum')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($failed as $fail)
                        <tr>
                            @can('web.user.internals.view')
                                <td>
                                    {{ $fail->id }}
                                </td>
                            @endcan
                            <td>
                                {{ $fail->connection }}
                            </td>
                            <td>
                                {{ $fail->queue }}
                            </td>
                            <td>
                                <a data-toggle="collapse" href="#details_{{$loop->index}}" role="button"
                                aria-expanded="false" aria-controls="#details_{{$loop->index}}">
                                    <u>{{ optional(json_decode($fail->payload))->displayName ?? 'Exception' }}</u>
                                </a>
                            </td>
                            <td>
                                {{ \Illuminate\Support\Carbon::parse($fail->failed_at)->toDateTimeLocalString() }}
                            </td>
                        </tr>

                        <tr id="details_{{ $loop->index }}" class="collapse" style="overflow-y:scroll">
                            <td colspan="5">
                                <p class="mb-0">@lang('Details'):</p>
                                <pre><code class="mb-0" style="white-space: pre-wrap; word-break: break-all;">{{ $fail->exception }}</code></pre>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9">@lang('Keine fehlgeschlafenen Jobs vorhanden')</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            @can('web.user.jobs.truncate')
                @component('components.forms.form', [
                    'action' => route('web.user.jobs.truncate'),
                    'class' => 'mb-3',
                ])
                    <button class="btn btn-danger">@lang('Tabelle leeren')</button>
                @endcomponent
            @endcan
        </div>
    </div>
@endsection
