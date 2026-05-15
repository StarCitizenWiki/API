@php use App\Support\Format; @endphp
@extends('admin.layout')

@section('admin.content')
    <div class="flex flex-col gap-6">
        <h1 class="text-2xl font-semibold" data-testid="admin-dashboard-heading">Admin Dashboard</h1>

        <section class="space-y-4">
            <h2 class="text-lg font-semibold tracking-tight">Overview</h2>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {{-- Total Users --}}
                <a href="{{ route('admin.users.index') }}" class="card card-border bg-base-100 shadow transition hover:shadow-md" data-testid="admin-dashboard-users-card">
                    <div class="card-body p-5">
                        <div class="stat">
                            <div class="stat-title" data-testid="admin-dashboard-users-title">Total Users</div>
                            <div class="stat-value" data-testid="admin-dashboard-users-value">{{ Format::number($stats['totalUsers']) }}</div>
                            <div class="stat-desc">Manage users →</div>
                        </div>
                    </div>
                </a>

                {{-- Total Queued Jobs --}}
                <div class="card card-border bg-base-100 shadow" data-testid="admin-dashboard-jobs-card">
                    <div class="card-body p-5">
                        <div class="stat">
                            <div class="stat-title" data-testid="admin-dashboard-jobs-title">Total Queued Jobs</div>
                            <div class="stat-value text-info" data-testid="admin-dashboard-jobs-value">{{ Format::number($stats['totalJobs']) }}</div>
                            <div class="stat-desc" data-testid="admin-dashboard-jobs-breakdown">
                                @foreach ($stats['queuedBreakdown'] as $queue => $count)
                                    <div>{{ $queue }}: {{ $count }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Failed Jobs --}}
                <a href="{{ route('admin.jobs.index') }}" class="card card-border bg-base-100 shadow transition hover:shadow-md" data-testid="admin-dashboard-failed-jobs-card">
                    <div class="card-body p-5">
                        <div class="stat">
                            <div class="stat-title" data-testid="admin-dashboard-failed-jobs-title">Total Failed Jobs</div>
                            <div class="stat-value text-error" data-testid="admin-dashboard-failed-jobs-value">{{ Format::number($stats['failedJobs']) }}</div>
                            <div class="stat-desc">View Details →</div>
                        </div>
                    </div>
                </a>
            </div>
        </section>

        <section class="space-y-4">
            <h2 class="text-lg font-semibold tracking-tight">Quick Actions</h2>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <a href="{{ route('admin.users.index') }}" class="card card-border bg-base-100 shadow transition hover:shadow-md" data-testid="admin-dashboard-manage-users-link">
                    <div class="card-body flex-row items-center gap-4 p-5">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-base-200">
                            <x-icon name="users" class="size-5"/>
                        </div>
                        <h3 class="font-semibold">Manage Users</h3>
                    </div>
                </a>

                <a href="{{ route('admin.game-versions.index') }}" class="card card-border bg-base-100 shadow transition hover:shadow-md" data-testid="admin-dashboard-game-versions-link">
                    <div class="card-body flex-row items-center gap-4 p-5">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-base-200">
                            <x-icon name="server" class="size-5"/>
                        </div>
                        <h3 class="font-semibold">Game Versions</h3>
                    </div>
                </a>

                <a href="{{ route('admin.translations.index') }}" class="card card-border bg-base-100 shadow transition hover:shadow-md" data-testid="admin-dashboard-translations-link">
                    <div class="card-body flex-row items-center gap-4 p-5">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-base-200">
                            <x-icon name="languages" class="size-5"/>
                        </div>
                        <h3 class="font-semibold">Translations</h3>
                    </div>
                </a>

            </div>
        </section>
    </div>
@endsection
