@extends('admin.layouts.default')

@section('content')
    @foreach($logs as $type => $log)
        <div class="card mb-4">
            <h4 class="card-header">{{ __($type) }} <small class="float-right">{{ count($log['all']) }}</small></h4>
            <div class="card-body p-0">
                <table class="table table-striped border-top-0 mb-0">
                    <tr>
                        <th>@lang('Datum')</th>
                        <th class="text-center">@lang('Env')</th>
                        <th class="text-center">@lang('Context')</th>
                        <th class="text-center">@lang('Stack')</th>
                    </tr>
                    @forelse($log['all'] as $item)
                        <tr>
                            <td>{{ $item->date->format('d.m.Y H:i:s') }}</td>
                            <td class="text-center">
                                @if($item->environment === 'local')
                                    <i class="far fa-desktop"></i>
                                @endif
                                @if($item->environment === 'testing')
                                    <i class="far fa-shield-check"></i>
                                @endif
                            </td>
                            <td class="text-center"><i class="far fa-book"></i></td>
                            <td class="text-center"><i class="far fa-bars"></i></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                @lang('Keine Logs')
                            </td>
                        </tr>
                    @endforelse
                </table>
            </div>
        </div>
    @endforeach

    <div class="modal fade" id="logModal" tabindex="-1" role="dialog" aria-labelledby="logModal" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">New message</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label for="recipient-name" class="form-control-label">Recipient:</label>
                            <input type="text" class="form-control" id="recipient-name">
                        </div>
                        <div class="form-group">
                            <label for="message-text" class="form-control-label">Message:</label>
                            <textarea class="form-control" id="message-text"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Send message</button>
                </div>
            </div>
        </div>
    </div>
@endsection