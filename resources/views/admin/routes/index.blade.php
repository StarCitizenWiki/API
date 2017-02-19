@extends('layouts.app')
@section('title', 'Star Citizen Wiki API - Routes')
@section('lead', 'Routes')

@section('content')
    @include('layouts.heading')
    <div class="container">
        <div class="row">
            <div class="col-12 mt-5">
                @include('snippets.routelist')
            </div>
        </div>
    </div>
@endsection
