@extends('layouts.app')


@section('content')
    <div class="flex flex-col gap-6">
        <div class="breadcrumbs text-sm">
            <ul>
                <li><a href="{{ route('admin.dashboard') }}">Admin Dashboard</a></li>
                @hasSection('breadcrumbs')
                    @yield('breadcrumbs')
                @endif
            </ul>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <div class="flex flex-col gap-1">
                    @foreach ($errors->all() as $error)
                        <span>{{ $error }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        @yield('admin.content')
    </div>
@endsection
