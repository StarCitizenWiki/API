@extends('layouts.admin')
@section('title', 'Logs')

@section('header')
    <style>
        .display-5 {
            font-size: 2.5rem;
        }

        .date {
            white-space: nowrap;
        }

        .stack {
            font-size: 0.85em;
        }

        .date {
            min-width: 75px;
        }

        .text {
            word-break: break-all;
        }

        a.llv-active {
            z-index: 2;
            background-color: #f5f5f5;
            border-color: #777;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12 col-md-3">
            <h4>@lang('admin/logs.header')</h4>
            <div class="list-group mt-3 mb-5">
                @foreach($files as $file)
                    <a href="?l={{ base64_encode($file) }}" class="list-group-item @if ($current_file == $file) llv-active @endif">
                        {{$file}}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mx-4 pr-4 mb-4 table-container">
            @if ($logs === null)
                <div>
                    @lang('admin/logs.log_too_big')
                </div>
            @else
                <table id="table-log" class="table table-striped my-4 pr-3">
                    <thead>
                    <tr>
                        <th>@lang('admin/logs.level')</th>
                        <th>@lang('admin/logs.context')</th>
                        <th>@lang('admin/logs.date')</th>
                        <th>@lang('admin/logs.content')</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($logs as $key => $log)
                        <tr>
                            <td class="text-{{{$log['level_class']}}}"><span class="fa fa-{{{$log['level_img']}}}-sign" aria-hidden="true"></span> &nbsp;{{$log['level']}}</td>
                            <td class="text">{{$log['context']}}</td>
                            <td class="date" data-order="{{{$log['date']}}}">{{ \Carbon\Carbon::parse($log['date'])->format('d.m.Y H:i:s') }}</td>
                            <td class="text">
                                @if ($log['stack']) <a class="pull-right expand btn btn-default btn-xs" data-display="stack{{{$key}}}"><span class="fa fa-search"></span></a>@endif
                                {{{$log['text']}}}
                                @if (isset($log['in_file'])) <br />{{{$log['in_file']}}}@endif
                                @if ($log['stack']) <div class="stack" id="stack{{{$key}}}" style="display: none; white-space: pre-wrap;">{{{ trim($log['stack']) }}}</div>@endif
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            @endif
            <div>
                @if($current_file)
                    <a href="?dl={{ base64_encode($current_file) }}"><span class="fa fa-download"></span> @lang('admin/logs.download_file')</a>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.datatables.net/1.10.13/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function(){
            $('#table-log').DataTable({
                "order": [ 1, 'desc' ],
                "stateSave": true,
                "stateSaveCallback": function (settings, data) {
                    window.localStorage.setItem("datatable", JSON.stringify(data));
                },
                "stateLoadCallback": function (settings) {
                    var data = JSON.parse(window.localStorage.getItem("datatable"));
                    if (data) data.start = 0;
                    return data;
                }
            });
            $('.table-container').on('click', '.expand', function(){
                $('#' + $(this).data('display')).toggle();
            });
            $('#delete-log, #delete-all-log').click(function(){
                return confirm('Are you sure?');
            });
        });
    </script>
@endsection