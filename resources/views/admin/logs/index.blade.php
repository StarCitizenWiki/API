@extends('admin.layouts.default_wide')

@section('content')

    <div class="card mb-4">
        @component('components.forms.form', [
            'method' => 'PATCH',
            'action' => route('web.admin.logs.mark_read'),
        ])
        <h4 class="card-header">
            @lang('Logs')
            <button name="mark_all" class="btn btn-outline-danger btn-sm float-right">@lang('Alle als gelesen markieren')</button>
        </h4>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped border-top-0 mb-0" id="logTable">
                <tr>
                    <th class="text-center">
                        <div class="custom-control custom-checkbox mr-0 mb-0">
                            <input type="checkbox" class="custom-control-input" id="mark-all">
                            <label class="custom-control-label" for="mark-all"></label>
                        </div>
                    </th>
                    <th>@lang('ID')</th>
                    <th>@lang('Message')</th>
                    <th>@lang('Environment')</th>
                    <th>@lang('Level')</th>
                    <th>@lang('Datum')</th>
                </tr>
                @forelse($logs as $log)
                    <tr>
                        <td class="text-center">
                            <div class="custom-control custom-checkbox mr-0 mb-0">
                                <input type="checkbox" class="custom-control-input" name="mark_read[]" id="mark-read-{{$log->id}}"
                                       value="{{ $log->id }}">
                                <label class="custom-control-label" for="mark-read-{{$log->id}}"></label>
                            </div>
                        </td>
                        <td>
                            <nobr>
                                <a data-toggle="collapse" href="#collapse-{{ $log->id }}" aria-expanded="false" aria-controls="collapse-{{ $log->id }}">
                                    @component('components.elements.icon')
                                        plus-circle
                                    @endcomponent
                                        <span title="{{ $log->id }}"> {{ substr($log->id, 0, 8) }}...</span>
                                </a>
                            </nobr>
                        </td>
                        <td>{{ explode('{', $log->context->message)[0] }}</td>

                        <td>
                            @if($log->environment === 'local')
                                @component('components.elements.icon')
                                    desktop
                                @endcomponent
                                @lang('Local')
                            @endif
                            @if($log->environment === 'testing')
                                @component('components.elements.icon')
                                    shield-check
                                @endcomponent
                                @lang('Testing')
                            @endif
                        </td>

                        <td class="text-{{ get_bootstrap_class_from_log_level($log->level) }}">{{ $log->level }}</td>

                        <td>{{ $log->date->format('d.m.Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <td colspan="6" class="py-0">
                            <div class="collapse collapse-log-content mt-2" id="collapse-{{ $log->id }}">
                                <?php $traces = null; preg_match_all('/(#[0-9]{1,3})(.*)\:(.*)/', $log->context->message, $traces); unset($traces[0]); ?>
                                <div class="row mb-2">
                                    <div class="col-1">
                                        @component('components.elements.icon')
                                            check
                                        @endcomponent
                                        @lang('Log file'):
                                    </div>
                                    <div class="col-11">
                                        {{ $log->file_path }}
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-1">
                                        @unless(count($traces[1]) == 0)
                                            @component('components.elements.icon')
                                                check
                                            @endcomponent @lang('Stack trace'):
                                        @else
                                            @component('components.elements.icon')
                                                check
                                            @endcomponent @lang('Content'):
                                        @endunless
                                    </div>
                                    <div class="col-11">
                                        @unless(count($traces[1]) == 0)
                                            @for($i = 0; $i < count($traces[1]); $i++)
                                                <div class="row">
                                                    <div class="col" style="max-width: 55px">
                                                        {{ str_replace('#', '', $traces[1][$i]) }}.
                                                    </div>
                                                    <div class="col-11">
                                                        <div><i>@lang('Caught at'):</i> {{ $traces[3][$i] }}</div>
                                                        <div><i>@lang('Caught in'):</i> {{ $traces[2][$i] }}</div>
                                                    </div>
                                                </div>
                                                <hr>
                                            @endfor
                                        @else
                                            {{ $log->context->message }}
                                        @endunless
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            @lang('Keine Logs')
                        </td>
                    </tr>
                @endforelse
            </table>
        </div>
        <div class="card-footer clearfix">
            <button class="btn btn-outline-secondary float-left" type="submit">@lang('Als gelesen markieren')</button>
            {{ $logs->links() }}
        </div>
        @endcomponent
    </div>


@endsection

@section('body__after')
    @parent
    <script>
        window.addEventListener('load', function () {
            let width, collapseLogContent;

            width = document.getElementById('logTable').offsetWidth;
            console.log(width);

            collapseLogContent = document.getElementsByClassName('collapse-log-content');

            for (let i = 0; i < collapseLogContent.length; i++) {
                collapseLogContent[i].style.width = (width - 25) + "px";
            }

        });

        document.getElementById('mark-all').addEventListener('click', function (e) {
            let checkboxes;

            checkboxes = document.getElementsByName('mark_read[]');

            for (let i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = e.target.checked;
            }
        });
    </script>
@endsection