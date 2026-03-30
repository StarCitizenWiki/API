@extends('admin.layout')

@section('breadcrumbs')
    <li>Game Versions</li>
@endsection

@section('admin.content')
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Game Versions</h1>
            <span class="text-sm text-base-content/60">Total: {{ $versions->count() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="table table-sm">
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
                        <tr class="{{ $version->is_default ? 'bg-success/10' : '' }}">
                            <td class="font-mono font-semibold">{{ $version->code }}</td>
                            <td>
                                <span class="badge badge-outline">{{ $version->channel }}</span>
                            </td>
                            <td>{{ $version->released_at?->format('Y-m-d') ?? 'N/A' }}</td>
                            <td>
                                @if ($version->is_default)
                                    <span class="badge badge-success">Default</span>
                                @else
                                    <span class="badge badge-ghost">Not Default</span>
                                @endif
                            </td>
                            <td>
                                @if ($version->is_hidden)
                                    <span class="badge badge-warning">Hidden</span>
                                @else
                                    <span class="badge badge-success">Visible</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    @if (!$version->is_default)
                                        <button onclick="setDefaultModal{{ $version->id }}.showModal()" class="btn btn-primary btn-sm">
                                            Set as Default
                                        </button>

                                        <dialog id="setDefaultModal{{ $version->id }}" class="modal">
                                            <div class="modal-box">
                                                <h3 class="text-lg font-bold">Confirm Default Version</h3>
                                                <p class="py-4">Are you sure you want to set "{{ $version->code }}" as the default version?</p>
                                                <div class="modal-action">
                                                    <form method="POST" action="{{ route('admin.game-versions.set-default', $version) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-primary">Set as Default</button>
                                                    </form>
                                                    <button class="btn" onclick="setDefaultModal{{ $version->id }}.close()">Cancel</button>
                                                </div>
                                            </div>
                                        </dialog>
                                    @else
                                        <span class="text-sm text-base-content/60">Current Default</span>
                                    @endif

                                    @if ($version->is_hidden)
                                        <form method="POST" action="{{ route('admin.game-versions.show', $version) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline btn-sm">Show</button>
                                        </form>
                                    @elseif (!$version->is_default)
                                        <form method="POST" action="{{ route('admin.game-versions.hide', $version) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline btn-sm">Hide</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-base-content/60">No game versions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
