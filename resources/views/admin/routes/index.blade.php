@extends('layouts.app')
@section('title', 'Star Citizen Wiki API - Routes')
@section('lead', 'Routes')

@section('content')
    @include('layouts.heading')
    <div class="container-fluid">
        <div class="row">
            <div class="col-10 mx-auto mt-5">
                @include('snippets.routelist')
            </div>
        </div>
    </div>
@endsection
