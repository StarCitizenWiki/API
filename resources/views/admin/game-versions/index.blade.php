@extends('admin.layout')

@section('breadcrumbs')
    <li>Game Versions</li>
@endsection

@section('admin.content')
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold" data-testid="admin-game-versions-heading">Game Versions</h1>
            <span class="text-sm text-subtle" data-testid="admin-game-versions-total">Total: {{ $versions->count() }}</span>
        </div>

        <section class="space-y-4">
            <div class="card card-border bg-base-100 shadow">
                <div class="card-body p-0">
                    <div class="overflow-x-auto">
                        <table class="table table-sm" data-testid="admin-game-versions-table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Channel</th>
                                    <th>Released At</th>
                                    <th>Status</th>
                                    <th>Selector</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($versions as $version)
                                    <tr class="{{ $version->is_default ? 'bg-success/10' : '' }}" data-testid="admin-game-versions-row-{{ $version->id }}">
                                        <td class="font-mono font-semibold" data-testid="admin-game-versions-code-{{ $version->id }}">{{ $version->code }}</td>
                                        <td>
                                            <span class="badge badge-outline">{{ $version->channel }}</span>
                                        </td>
                                        <td>{{ $version->released_at?->format('Y-m-d') ?? 'N/A' }}</td>
                                        <td>
                                            @if ($version->is_default)
                                                <span class="badge badge-success" data-testid="admin-game-versions-default-status-{{ $version->id }}">Default</span>
                                            @else
                                                <span class="badge badge-soft" data-testid="admin-game-versions-default-status-{{ $version->id }}">Not Default</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($version->is_hidden)
                                                <span class="badge badge-warning" data-testid="admin-game-versions-selector-status-{{ $version->id }}">Hidden</span>
                                            @else
                                                <span class="badge badge-success" data-testid="admin-game-versions-selector-status-{{ $version->id }}">Visible</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex flex-wrap gap-2">
                                                @if (!$version->is_default)
                                                    <button
                                                        onclick="setDefaultModal{{ $version->id }}.showModal()"
                                                        class="btn btn-primary btn-sm"
                                                        data-testid="admin-game-versions-set-default-button-{{ $version->id }}"
                                                    >
                                                        Set as Default
                                                    </button>

                                                    <x-confirm-dialog
                                                        id="setDefaultModal{{ $version->id }}"
                                                        title="Confirm Default Version"
                                                        :message="'Are you sure you want to set &quot;'.$version->code.'&quot; as the default version?'"
                                                        :action="route('admin.game-versions.set-default', $version)"
                                                        method="POST"
                                                        confirmLabel="Set as Default"
                                                        confirmClass="btn btn-primary"
                                                        :testId="'admin-game-versions-set-default-modal-'.$version->id"
                                                    />
                                                @else
                                                    <span class="text-sm text-subtle" data-testid="admin-game-versions-current-default-{{ $version->id }}">Current Default</span>
                                                @endif

                                                @if ($version->is_hidden)
                                                    <form method="POST" action="{{ route('admin.game-versions.show', $version) }}" data-testid="admin-game-versions-show-form-{{ $version->id }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline btn-sm">Show</button>
                                                    </form>
                                                @elseif (!$version->is_default)
                                                    <form method="POST" action="{{ route('admin.game-versions.hide', $version) }}" data-testid="admin-game-versions-hide-form-{{ $version->id }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline btn-sm">Hide</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-subtle" data-testid="admin-game-versions-empty">No game versions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
