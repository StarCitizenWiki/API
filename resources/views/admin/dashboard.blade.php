@extends('layouts.admin')

@section('header')
<style>
    .display-5 {
        font-size: 2.5rem;
    }

    .date {
        white-space: nowrap;
    }
</style>
@endsection

@section('content')
    <section class="row text-center placeholders mat-5 mx-auto">
        <div class="col-sm-6 col-lg-3 mb-1">
            <div class="card">
                <div class="card-block p-0 clearfix">
                    <i class="fa fa-users bg-inverse py-4 text-white mr-1 float-left display-5 col-5"></i>
                    <div class="h5 mb-0 pt-4 text-center">{{ count($users) }}</div>
                    <div class="text-muted text-uppercase font-weight-bold font-xs text-center">Users</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3 mb-1">
            <div class="card">
                <div class="card-block p-0 clearfix">
                    <i class="fa fa-sign-in bg-inverse py-4 text-white mr-1 float-left display-5 col-5"></i>
                    <div class="h5 mb-0 pt-4 text-center">{{ $logins }}</div>
                    <div class="text-muted text-uppercase font-weight-bold font-xs text-center">User Logins</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3 mb-1">
            <div class="card">
                <div class="card-block p-0 clearfix">
                    <i class="fa fa-code bg-inverse py-4 text-white mr-1 float-left display-5 col-5"></i>
                    <div class="h5 mb-0 pt-4 text-center">{{ $api_requests }}</div>
                    <div class="text-muted text-uppercase font-weight-bold font-xs text-center">API Requests Today</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3 mb-1">
            <div class="card">
                <div class="card-block p-0 clearfix">
                    <i class="fa fa-link bg-inverse py-4 text-white mr-1 float-left display-5 col-5"></i>
                    <div class="h5 mb-0 pt-4 text-center">{{ count($urls) }}</div>
                    <div class="text-muted text-uppercase font-weight-bold font-xs text-center">Short URLs</div>
                </div>
            </div>
        </div>

    </section>

    <section class="row mt-4 mx-auto">
        <div class="col-12 table-container">
            <h1 class="text-muted mt-4 mb-4">Latest Logs:</h1>
            <h4 class="mt-4 mb-4"><i class="fa fa-warning text-danger mr-1"></i> Errors:</h4>
            @component('admin.snippets.loglist', ['logs' => $logs])
                error
            @endcomponent

            <h4 class="mt-5 mb-4"><i class="fa fa-exclamation text-warning mr-1"></i> Warnings:</h4>
            @component('admin.snippets.loglist', ['logs' => $logs])
                warning
            @endcomponent

            <h4 class="mt-5 mb-4"><i class="fa fa-info text-info mr-1"></i> Info:</h4>
            @component('admin.snippets.loglist', ['logs' => $logs])
                info
            @endcomponent

            @if (config('app.debug'))
                <h4 class="mt-5 mb-4"><i class="fa fa-bug text-success mr-1"></i> Debug:</h4>
                @component('admin.snippets.loglist', ['logs' => $logs])
                    debug
                @endcomponent
            @endif
        </div>

    </section>
@endsection

@section('scripts')
    <script>
            $('.table-container').on('click', '.expand', function(){
                $('#' + $(this).data('display')).toggle();
            });

    </script>
@endsection