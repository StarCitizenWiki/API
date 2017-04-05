@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
    <section class="row text-center placeholders mt-5 mx-auto">
        <div class="col-6 col-sm-3 placeholder mx-md-auto">
            <div class="rounded-circle mx-auto bg-inverse d-flex img-fluid" style="height: 200px; width: 200px;">
                <h1 class="text-white d-flex my-auto mx-auto" style="font-size: 4rem;">
                    <span style="margin-top: -10px; margin-left: -5px;" class="d-block">
                        {{ count($users) }}
                    </span>
                </h1>
            </div>
            <h4 class="my-4">Users</h4>
        </div>
        <div class="col-6 col-sm-3 placeholder mx-md-auto">
            <div class="rounded-circle mx-auto bg-inverse d-flex img-fluid" style="height: 200px; width: 200px;">
                <h1 class="text-white d-flex my-auto mx-auto" style="font-size: 4rem;">
                    <span style="margin-top: -10px; margin-left: -5px;" class="d-block">
                        {{ count(\App\Models\User::whereDate('api_token_last_used', '=', \Carbon\Carbon::today()->toDateString())) }}
                    </span>
                </h1>
            </div>
            <h4 class="my-4">API Requests Today</h4>
        </div>
        <div class="col-6 col-sm-3 placeholder mx-md-auto">
            <div class="rounded-circle mx-auto bg-inverse d-flex img-fluid" style="height: 200px; width: 200px;">
                <h1 class="text-white d-flex my-auto mx-auto" style="font-size: 4rem;">
                    <span style="margin-top: -10px; margin-left: -5px;" class="d-block">
                        {{ count($urls) }}
                    </span>
                </h1>
            </div>
            <h4 class="my-4">Urls</h4>
        </div>
    </section>
@endsection
