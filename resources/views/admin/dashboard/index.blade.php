@extends('admin.layout')

@section('admin.content')
    <div class="flex flex-col gap-4">
        <h1 class="text-2xl font-bold">Admin Dashboard</h1>

         <div class="stats stats-vertical lg:stats-horizontal shadow">
              {{-- Total Users --}}
              <div class="stat">
                  <div class="stat-title">Total Users</div>
                  <div class="stat-value">{{ number_format($stats['totalUsers']) }}</div>
                  <div class="stat-actions">
                      <a href="{{ route('admin.users.index') }}" class="btn btn-sm">Manage Users →</a>
                  </div>
              </div>

              {{-- Total Queued Jobs --}}
              <div class="stat">
                 <div class="stat-title">Total Queued Jobs</div>
                 <div class="stat-value text-info">{{ number_format($stats['totalJobs']) }}</div>
                 <div class="stat-desc">
                     @foreach ($stats['queuedBreakdown'] as $queue => $count)
                         <div>{{ $queue }}: {{ $count }}</div>
                     @endforeach
                 </div>
             </div>

              {{-- Total Failed Jobs --}}
              <div class="stat">
                  <div class="stat-title">Total Failed Jobs</div>
                  <div class="stat-value text-error">{{ number_format($stats['failedJobs']) }}</div>
                  <div class="stat-actions">
                      <a href="{{ route('admin.jobs.index') }}" class="btn btn-sm">View Details →</a>
                  </div>
              </div>
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
