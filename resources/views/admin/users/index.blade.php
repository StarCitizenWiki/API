@extends('admin.layout')

@section('breadcrumbs')
    <li>Users</li>
@endsection

@section('admin.content')
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Users Management</h1>
            <span class="text-sm text-base-content/60">Total: {{ number_format($users->total()) }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Is Admin</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if ($user->is_admin)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-ghost">No</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                @unless($user->id === \Illuminate\Support\Facades\Auth::id())
                                <button onclick="deleteModal{{ $user->id }}.showModal()" class="btn btn-error btn-sm">
                                    Delete
                                </button>
                                @else
                                    -
                                @endunless

                                <dialog id="deleteModal{{ $user->id }}" class="modal">
                                    <div class="modal-box">
                                        <h3 class="text-lg font-bold">Confirm Deletion</h3>
                                        <p class="py-4">Are you sure you want to delete user "{{ $user->name }}"? This action cannot be undone.</p>
                                        <div class="modal-action">
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-error">Delete User</button>
                                            </form>
                                            <button class="btn" onclick="deleteModal{{ $user->id }}.close()">Cancel</button>
                                        </div>
                                    </div>
                                </dialog>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-base-content/60">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="flex justify-center">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection
