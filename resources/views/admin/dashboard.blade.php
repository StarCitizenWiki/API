@extends('admin.layouts.default')

{{-- Page Content --}}
@section('P__content')
    <section class="row text-center placeholders mat-5 mx-auto">

        <div class="col-sm-6 col-lg-2 mb-1">
            @component('components.card')
                @slot('icon')
                    user-plus
                @endslot
                @slot('content')
                    {{ $users_registered_count_today }}
                @endslot
                @slot('text')
                    @lang('admin/dashboard.today')
                @endslot
                Registrierungen
            @endcomponent
        </div>

        <div class="col-sm-6 col-lg-2 mb-1">
            @component('components.card')
                @slot('icon')
                    users
                @endslot
                @slot('content')
                    {{ count($users) }}
                @endslot
                @slot('text')
                    Insgesamt
                @endslot
                @lang('admin/dashboard.user')
            @endcomponent
        </div>

        <div class="col-sm-6 col-lg-2 mb-1">
            @component('components.card')
                @slot('icon')
                    sign-in
                @endslot
                @slot('content')
                    {{ $logins_count_today }} / {{ $logins_count }}
                @endslot
                @slot('text')
                    @lang('admin/dashboard.today') / Insgesamt
                @endslot
                @lang('admin/dashboard.user_logins')
            @endcomponent
        </div>

        <div class="col-sm-6 col-lg-2 mb-1">
            @component('components.card')
                @slot('icon')
                    code
                @endslot
                @slot('content')
                    {{ $api_requests_count_today }} / {{ count($api_requests) }}
                @endslot
                @slot('text')
                    @lang('admin/dashboard.today') / Insgesamt
                @endslot
                @lang('admin/dashboard.api_requests')
            @endcomponent
        </div>

        <div class="col-sm-6 col-lg-2 mb-1">
            @component('components.card')
                @slot('icon')
                    link
                @endslot
                @slot('content')
                    {{ count($urls) }}
                @endslot
                @slot('text')
                    Insgesamt
                @endslot
                Short URLs
            @endcomponent
        </div>

        <div class="col-sm-6 col-lg-2 mb-1">
            @component('components.card')
                @slot('icon')
                    book
                @endslot
                @slot('content')
                    {{ count($logs) }}
                @endslot
                @slot('text')
                    Insgesamt
                @endslot
                Logs
            @endcomponent
        </div>

    </section>


@endsection

@section('scripts')
    <script>
        $('.table-container').on('click', '.expand', function () {
            $('#' + $(this).data('display')).toggle();
        });
    </script>
@endsection
