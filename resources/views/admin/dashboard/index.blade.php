@extends('admin.layout')

@section('admin.content')
    <div class="flex flex-col gap-4">
        <h1 class="text-2xl font-bold">Admin Dashboard</h1>

        <div class="grid gap-4 md:grid-cols-3">
            {{-- Total Users --}}
            <a href="{{ route('admin.users.index') }}" class="card border border-base-200 bg-base-100 shadow-sm hover:shadow-md">
                <div class="card-body">
                    <h2 class="card-title text-lg">Total Users</h2>
                    <p class="text-3xl font-bold">{{ number_format($stats['totalUsers']) }}</p>
                    <div class="card-actions justify-end">
                        <span class="text-sm text-base-content/60">Manage Users →</span>
                    </div>
                </div>
            </a>

            {{-- Total Jobs --}}
            <div class="card border border-base-200 bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg">Total Jobs</h2>
                    <p class="text-3xl font-bold">{{ number_format($stats['totalJobs']) }}</p>
                    <div class="card-actions justify-end">
                        <span class="text-sm text-base-content/60">In Queue</span>
                    </div>
                </div>
            </div>

            {{-- Failed Jobs --}}
            <a href="{{ route('admin.jobs.index') }}" class="card border border-base-200 bg-base-100 shadow-sm hover:shadow-md">
                <div class="card-body">
                    <h2 class="card-title text-lg">Failed Jobs</h2>
                    <p class="text-3xl font-bold">{{ number_format($stats['failedJobs']) }}</p>
                    <div class="card-actions justify-end">
                        <span class="text-sm text-base-content/60">View Details →</span>
                    </div>
                </div>
            </a>
        </div>

        <div class="divider"></div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-lg">
                <x-icon name="users" class="size-5" />
                Manage Users
            </a>

            <a href="{{ route('admin.game-versions.index') }}" class="btn btn-outline btn-lg">
                <x-icon name="server" class="size-5" />
                Game Versions
            </a>

            <a href="{{ route('admin.translations.index') }}" class="btn btn-outline btn-lg">
                <x-icon name="languages" class="size-5" />
                Translations
            </a>
        </div>
    </div>
@endsection
