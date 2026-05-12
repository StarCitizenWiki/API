@php use App\Support\Format; @endphp
@extends('admin.layout')

@section('admin.content')
    <div class="flex flex-col gap-4">
        <h1 class="text-2xl font-semibold" data-testid="admin-dashboard-heading">Admin Dashboard</h1>

        <div class="stats stats-vertical lg:stats-horizontal shadow" data-testid="admin-dashboard-stats">
            {{-- Total Users --}}
            <div class="stat" data-testid="admin-dashboard-users-card">
                <div class="stat-title" data-testid="admin-dashboard-users-title">Total Users</div>
                <div class="stat-value"
                     data-testid="admin-dashboard-users-value">{{ Format::number($stats['totalUsers']) }}</div>
                <div class="stat-actions">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm"
                       data-testid="admin-dashboard-users-link">Manage Users →</a>
                </div>
            </div>

            {{-- Total Queued Jobs --}}
            <div class="stat" data-testid="admin-dashboard-jobs-card">
                <div class="stat-title" data-testid="admin-dashboard-jobs-title">Total Queued Jobs</div>
                <div class="stat-value text-info"
                     data-testid="admin-dashboard-jobs-value">{{ Format::number($stats['totalJobs']) }}</div>
                <div class="stat-desc" data-testid="admin-dashboard-jobs-breakdown">
                    @foreach ($stats['queuedBreakdown'] as $queue => $count)
                        <div>{{ $queue }}: {{ $count }}</div>
                    @endforeach
                </div>
            </div>

            {{-- Total Failed Jobs --}}
            <div class="stat" data-testid="admin-dashboard-failed-jobs-card">
                <div class="stat-title" data-testid="admin-dashboard-failed-jobs-title">Total Failed Jobs</div>
                <div class="stat-value text-error"
                     data-testid="admin-dashboard-failed-jobs-value">{{ Format::number($stats['failedJobs']) }}</div>
                <div class="stat-actions">
                    <a href="{{ route('admin.jobs.index') }}" class="btn btn-sm"
                       data-testid="admin-dashboard-failed-jobs-link">View Details →</a>
                </div>
            </div>
        </div>

        <div class="divider"></div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-lg"
               data-testid="admin-dashboard-manage-users-link">
                <x-icon name="users" class="size-5"/>
                Manage Users
            </a>

            <a href="{{ route('admin.game-versions.index') }}" class="btn btn-outline btn-lg"
               data-testid="admin-dashboard-game-versions-link">
                <x-icon name="server" class="size-5"/>
                Game Versions
            </a>

            <a href="{{ route('admin.translations.index') }}" class="btn btn-outline btn-lg"
               data-testid="admin-dashboard-translations-link">
                <x-icon name="languages" class="size-5"/>
                Translations
            </a>
        </div>
    </div>
@endsection
