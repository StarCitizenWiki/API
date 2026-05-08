@extends('admin.layout')

@section('breadcrumbs')
    <li>Users</li>
@endsection

@section('admin.content')
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Users Management</h1>
            <span class="text-sm text-subtle" data-testid="admin-users-total">Total: {{ number_format($users->total()) }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="table table-sm" data-testid="admin-users-table">
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
                        <tr data-testid="admin-users-row-{{ $user->id }}">
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if ($user->is_admin)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-soft">No</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                @unless($user->id === \Illuminate\Support\Facades\Auth::id())
                                    <button
                                        data-testid="admin-users-delete-button-{{ $user->id }}"
                                        onclick="deleteModal{{ $user->id }}.showModal()"
                                        class="btn btn-error btn-sm"
                                    >
                                        Delete
                                    </button>

                                    <dialog id="deleteModal{{ $user->id }}" class="modal" data-testid="admin-users-delete-modal-{{ $user->id }}">
                                        <div class="modal-box">
                                            <h3 class="text-lg font-semibold">Confirm Deletion</h3>
                                            <p class="py-4">Are you sure you want to delete user "{{ $user->name }}"? This action cannot be undone.</p>
                                            <div class="modal-action">
                                                <form
                                                    method="POST"
                                                    action="{{ route('admin.users.destroy', $user) }}"
                                                    data-testid="admin-users-delete-form-{{ $user->id }}"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-error">Delete User</button>
                                                </form>
                                                <button class="btn" onclick="deleteModal{{ $user->id }}.close()">Cancel</button>
                                            </div>
                                        </div>
                                    </dialog>
                                @else
                                    -
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-subtle">No users found.</td>
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
