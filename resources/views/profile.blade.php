@extends('layouts.app')

@section('title', 'Profile')
@section('meta_description', 'Manage your account settings.')

@section('content')
    <div class="mx-auto flex w-full max-w-2xl flex-col gap-8">
        <div class="text-center">
            <h1 class="text-2xl font-semibold" data-testid="profile-heading">Profile</h1>
            <p class="text-sm text-subtle">Manage your account settings.</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success text-sm">
                <x-icon name="check-circle" />
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error text-sm">
                <x-icon name="alert-circle" />
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="card border border-base-200 bg-base-100 shadow" data-testid="profile-token-card">
            <div class="card-body gap-6">
                <h2 class="card-title">API Token</h2>

                @if (session('token'))
                    <div
                        x-data="{ copied: false }"
                        class="space-y-2"
                        data-testid="profile-new-token"
                    >
                        <label class="label">
                            <span class="label-text font-semibold">Your New API Token</span>
                        </label>
                        <div class="join w-full">
                            <input
                                type="text"
                                value="{{ session('token') }}"
                                readonly
                                class="input input-bordered join-item input-disabled flex-1 font-mono"
                            />
                            <button
                                type="button"
                                x-on:click="navigator.clipboard.writeText('{{ session('token') }}'); copied = true; setTimeout(() => copied = false, 3000)"
                                class="btn btn-ghost join-item"
                                aria-label="Copy token"
                            >
                                <x-icon name="copy" />
                                Copy
                            </button>
                        </div>
                        <p class="text-xs text-warning">
                            <x-icon name="info" class="size-3 inline" />
                            Copy this token now. You won't be able to see it again.
                        </p>

                        <div class="toast toast-end toast-bottom" x-show="copied" x-transition style="display: none;">
                            <div class="alert alert-success">
                                <x-icon name="check-circle" />
                                <span>API token copied to clipboard!</span>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.token.create') }}" class="space-y-4" data-testid="profile-token-create-form">
                    @csrf

                    @if ($errors->any())
                        <div class="alert alert-error text-sm">
                            <x-icon name="alert-circle" />
                            <ul class="list-disc space-y-1 pl-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <label class="form-control">
                        <span class="label-text">Token Name</span>
                        <input
                            type="text"
                            name="name"
                            data-testid="profile-token-name-input"
                            placeholder="e.g., My Personal Token"
                            class="input input-bordered w-full"
                        />
                        @error('name')
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <button type="submit" class="btn btn-primary mt-6" data-testid="profile-token-create-submit">Create New Token</button>
                </form>

                @if ($tokens->isNotEmpty())
                    <div class="overflow-x-auto" data-testid="profile-token-table-wrapper">
                        <table class="table table-zebra" data-testid="profile-token-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Last Used</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tokens as $token)
                                    <tr data-testid="profile-token-row-{{ $token->id }}">
                                        <td>
                                            <div class="font-medium">{{ $token->name }}</div>
                                        </td>
                                        <td data-testid="profile-token-last-used-{{ $token->id }}">
                                            @if ($token->last_used_at)
                                                {{ $token->last_used_at->diffForHumans() }}
                                            @else
                                                <span class="text-muted">Never</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <form
                                                method="POST"
                                                action="{{ route('profile.token.delete', $token->id) }}"
                                                onsubmit="return confirm('Are you sure you want to delete this token? This action cannot be undone.')"
                                                data-testid="profile-token-delete-form-{{ $token->id }}"
                                                class="inline-block"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="btn btn-ghost btn-error btn-sm"
                                                    aria-label="Delete token"
                                                    data-testid="profile-token-delete-button-{{ $token->id }}"
                                                >
                                                    <x-icon name="trash-2" />
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-8 text-center" data-testid="profile-token-empty-state">
                        <x-icon name="key" class="size-12 text-base-content/30 mb-3" />
                        <p class="text-subtle">You don't have any API tokens yet.</p>
                        <p class="text-sm text-muted">Create a token to authenticate with the API.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="card border border-base-200 bg-base-100 shadow" data-testid="profile-password-card">
            <div class="card-body gap-4">
                <h2 class="card-title">Change Password</h2>
                <form method="POST" action="{{ route('user-password.update') }}" data-testid="profile-password-form">
                    @csrf
                    @method('PUT')

                    <label class="form-control">
                        <span class="label-text">Current Password</span>
                        <input
                            type="password"
                            name="current_password"
                            data-testid="profile-current-password-input"
                            required
                            autocomplete="current-password"
                            class="input input-bordered w-full"
                        />
                        @error('current_password')
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="form-control">
                        <span class="label-text">New Password</span>
                        <input
                            type="password"
                            name="password"
                            data-testid="profile-password-input"
                            required
                            autocomplete="new-password"
                            class="input input-bordered w-full"
                        />
                        @error('password')
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="form-control">
                        <span class="label-text">Confirm New Password</span>
                        <input
                            type="password"
                            name="password_confirmation"
                            data-testid="profile-password-confirmation-input"
                            required
                            autocomplete="new-password"
                            class="input input-bordered w-full"
                        />
                    </label>

                    <button type="submit" class="btn btn-primary mt-4" data-testid="profile-password-submit">Update Password</button>
                </form>
            </div>
        </div>

        <div class="card border border-base-200 bg-base-100 shadow" data-testid="profile-delete-card">
            <div class="card-body gap-4">
                <h2 class="card-title text-error">Delete Account</h2>
                <p class="text-sm text-subtle">
                    Once you delete your account, there is no going back. Please be certain.
                </p>

                <form method="POST" action="{{ route('profile.destroy') }}" data-testid="profile-delete-form">
                    @csrf
                    @method('DELETE')

                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <label class="form-control flex-1">
                            <div class="label cursor-pointer justify-start gap-2">
                                <input
                                    type="checkbox"
                                    name="confirm"
                                    value="1"
                                    data-testid="profile-delete-confirm-checkbox"
                                    required
                                    class="checkbox checkbox-error checkbox-sm"
                                />
                                <span class="label-text">I understand that this action is irreversible</span>
                            </div>
                        </label>

                        <button type="submit" class="btn btn-error btn-sm" data-testid="profile-delete-submit">Delete Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
