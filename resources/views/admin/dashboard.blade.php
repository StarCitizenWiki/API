@extends('layouts.admin')

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
    <section class="row text-center placeholders mat-5 mx-auto">
        <div class="col-sm-6 col-lg-3 mb-1">
            @component('admin.components.card')
                @slot('title')
                    {{ $users_today }} Today
                @endslot
                @slot('icon')
                    users
                @endslot
                @slot('content')
                    {{ count($users) }}
                @endslot
                Users
            @endcomponent
        </div>

        <div class="col-sm-6 col-lg-3 mb-1">
            @component('admin.components.card')
                @slot('icon')
                    sign-in
                @endslot
                @slot('content')
                        {{ $logins }}
                @endslot
                    User Logins
            @endcomponent
        </div>

        <div class="col-sm-6 col-lg-3 mb-1">
            @component('admin.components.card')
                @slot('icon')
                    code
                @endslot
                @slot('content')
                    {{ $api_requests }}
                @endslot
                API Requests Today
            @endcomponent
        </div>

        <div class="col-sm-6 col-lg-3 mb-1">
            @component('admin.components.card')
                @slot('title')
                    {{ $urls_today }} Today
                @endslot
                @slot('icon')
                    link
                @endslot
                @slot('content')
                    {{ count($urls) }}
                @endslot
                Short URLs
            @endcomponent
        </div>

    </section>

    <section class="row mt-4 mx-auto">
        <div class="col-12 table-container">
            <h1 class="text-muted mt-4 mb-4">Latest Logs:</h1>
            <h4 class="mt-4 mb-4"><i class="fa fa-warning text-danger mr-1"></i> Errors:</h4>
            @component('admin.components.loglist', ['logs' => $logs])
                error
            @endcomponent

            <h4 class="mt-5 mb-4"><i class="fa fa-exclamation text-warning mr-1"></i> Warnings:</h4>
            @component('admin.components.loglist', ['logs' => $logs])
                warning
            @endcomponent

            <h4 class="mt-5 mb-4"><i class="fa fa-info text-info mr-1"></i> Info:</h4>
            @component('admin.components.loglist', ['logs' => $logs])
                info
            @endcomponent

            @if (config('app.debug'))
                <h4 class="mt-5 mb-4"><i class="fa fa-bug text-success mr-1"></i> Debug:</h4>
                @component('admin.components.loglist', ['logs' => $logs])
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