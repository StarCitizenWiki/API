@if ($logs === null)
    <div>
        @lang('admin/logs.log_too_big')
    </div>
@else
    <table id="table-log" class="table table-striped">
        <thead>
        <tr>
            <th>@lang('admin/logs.context')</th>
            <th>@lang('admin/logs.date')</th>
            <th>@lang('admin/logs.content')</th>
        </tr>
        </thead>
        <tbody>
        <?php $i = 0; ?>
        @foreach($logs as $key => $log)
            @if($log['level'] == $slot && $i < 10)
                <tr>
                    <td class="text">{{$log['context']}}</td>
                    <td class="date">{{\Carbon\Carbon::parse($log['date'])->format('d.m.Y H:i:s')}}</td>
                    <td class="text">
                        @if ($log['stack']) <a class="pull-right expand btn btn-default btn-xs" data-display="stack{{{$key}}}"><span class="fa fa-search"></span></a>@endif
                        {{{$log['text']}}}
                        @if (isset($log['in_file'])) <br />{{{$log['in_file']}}}@endif
                        @if ($log['stack']) <div class="stack" id="stack{{{$key}}}" style="display: none; white-space: pre-wrap;">{{{ trim($log['stack']) }}}</div>@endif
                    </td>
                </tr>
                <?php $i++; ?>
            @endif
        @endforeach

        </tbody>
    </table>
@endif