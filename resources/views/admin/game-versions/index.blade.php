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

                                        <dialog id="setDefaultModal{{ $version->id }}" class="modal">
                                            <div class="modal-box">
                                                <h3 class="text-lg font-bold">Confirm Default Version</h3>
                                                <p class="py-4">Are you sure you want to set "{{ $version->code }}" as the default version?</p>
                                                <form method="POST" action="{{ route('admin.game-versions.set-default', $version) }}" data-testid="admin-game-versions-set-default-form-{{ $version->id }}">
                                                    @csrf
                                                    <div class="modal-action">
                                                        <button type="submit" class="btn btn-primary">Set as Default</button>
                                                        <button type="button" class="btn" onclick="setDefaultModal{{ $version->id }}.close()">Cancel</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </dialog>
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
@endsection
