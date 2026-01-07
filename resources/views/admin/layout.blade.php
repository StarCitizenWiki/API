@extends('layouts.app')

@section('sidemenu')
    <x-app.sidemenu-group title="Admin">
        <x-app.sidemenu-item route="admin.dashboard" :withVersion="false">
            <x-slot:icon>
                <x-icon name="home" class="size-4" />
            </x-slot:icon>
            Dashboard
        </x-app.sidemenu-item>
        <x-app.sidemenu-item route="admin.jobs.index" :withVersion="false">
            <x-slot:icon>
                <x-icon name="database" class="size-4" />
            </x-slot:icon>
            Jobs
        </x-app.sidemenu-item>
        <x-app.sidemenu-item route="admin.commands.index" :withVersion="false">
            <x-slot:icon>
                <x-icon name="terminal" class="size-4" />
            </x-slot:icon>
            Commands
        </x-app.sidemenu-item>
    </x-app.sidemenu-group>

    <x-app.sidemenu-group title="Manage">
        <x-app.sidemenu-item route="admin.users.index" :withVersion="false">
            <x-slot:icon>
                <x-icon name="users" class="size-4" />
            </x-slot:icon>
            Users
        </x-app.sidemenu-item>
        <x-app.sidemenu-item route="admin.game-versions.index" :withVersion="false">
            <x-slot:icon>
                <x-icon name="server" class="size-4" />
            </x-slot:icon>
            Game Versions
        </x-app.sidemenu-item>
        <x-app.sidemenu-item route="admin.translations.index" :withVersion="false">
            <x-slot:icon>
                <x-icon name="languages" class="size-4" />
            </x-slot:icon>
            Translations</x-app.sidemenu-item>
    </x-app.sidemenu-group>
@endsection

@section('content')
    <div class="flex flex-col gap-6">
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
