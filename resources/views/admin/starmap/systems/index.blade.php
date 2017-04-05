@extends('layouts.admin')
@section('title', 'Starmap Systems')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-9 mx-auto">
                <ul class="list-unstyled text-center">
                @foreach($systems as $system)
                    <li class="d-inline-block col-2">
                        <a href="{{ route('admin_starmap_systems_edit_form', $system->code) }}">{{ $system->code }}</a></li>
                @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection

