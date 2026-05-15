@php use App\Support\Format; use Illuminate\Support\Facades\Auth; @endphp
@extends('admin.layout')

@section('breadcrumbs')
    <li>Users</li>
@endsection

@section('admin.content')
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Users Management</h1>
            <span class="text-sm text-subtle"
                  data-testid="admin-users-total">Total: {{ Format::number($users->total()) }}</span>
        </div>

        <section class="space-y-4">
            <div class="card card-border bg-base-100 shadow">
                <div class="card-body p-0">
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
                                        @unless($user->id === Auth::id())
                                            <button
                                                data-testid="admin-users-delete-button-{{ $user->id }}"
                                                onclick="deleteModal{{ $user->id }}.showModal()"
                                                class="btn btn-error btn-sm"
                                            >
                                                Delete
                                            </button>

                                            <x-confirm-dialog
                                                id="deleteModal{{ $user->id }}"
                                                title="Confirm Deletion"
                                                :message="'Are you sure you want to delete user &quot;'.$user->name.'&quot;? This action cannot be undone.'"
                                                :action="route('admin.users.destroy', $user)"
                                                method="DELETE"
                                                confirmLabel="Delete User"
                                                :testId="'admin-users-delete-modal-'.$user->id"
                                            />
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
                </div>
            </div>
        </section>

        @if ($users->hasPages())
            <div class="flex justify-center">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection
