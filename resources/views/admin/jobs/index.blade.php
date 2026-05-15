@php use App\Support\Format; @endphp
@extends('admin.layout')

@section('breadcrumbs')
    <li>Failed Jobs</li>
@endsection

@section('admin.content')
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold" data-testid="admin-failed-jobs-heading">Failed Jobs</h1>
            <div class="flex gap-2">
                <span class="text-sm text-subtle"
                      data-testid="admin-failed-jobs-total">Total: {{ Format::number($jobs->total()) }}</span>
                @if ($jobs->total() > 0)
                    <button onclick="truncateModal.showModal()" class="btn btn-error btn-sm"
                            data-testid="admin-failed-jobs-truncate-button">
                        Truncate All
                    </button>
                @endif
            </div>
        </div>

        <section class="space-y-4">
            <div class="card card-border bg-base-100 shadow">
                <div class="card-body p-0">
                    <div class="overflow-x-auto">
                        <table class="table table-sm" data-testid="admin-failed-jobs-table">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Job</th>
                                <th>Queue</th>
                                <th>Failed At</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($jobs as $job)
                                <tr data-testid="admin-failed-jobs-row-{{ $job->id }}">
                                    <td data-testid="admin-failed-jobs-id-{{ $job->id }}">{{ $job->id }}</td>
                                    <td class="font-mono text-sm">
                                        @php
                                            $payload = json_decode($job->payload, true);
                                            $displayName = data_get($payload, 'displayName', data_get($payload, 'job', '-'));
                                        @endphp
                                        <span title="{{ $displayName }}">{{ Str::of($displayName)->afterLast('\\')->limit(40) }}</span>
                                    </td>
                                    <td data-testid="admin-failed-jobs-queue-{{ $job->id }}">{{ $job->queue }}</td>
                                    <td data-testid="admin-failed-jobs-failed-at-{{ $job->id }}">{{ \Carbon\Carbon::parse($job->failed_at)->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button
                                                onclick="exceptionModal{{ $job->id }}.showModal()"
                                                class="btn btn-outline btn-sm"
                                                data-testid="admin-failed-jobs-view-exception-button-{{ $job->id }}"
                                            >
                                                View Exception
                                            </button>
                                            <button
                                                onclick="deleteModal{{ $job->id }}.showModal()"
                                                class="btn btn-error btn-sm"
                                                data-testid="admin-failed-jobs-delete-button-{{ $job->id }}"
                                            >
                                                Delete
                                            </button>
                                        </div>

                                        {{-- Exception Viewer Modal --}}
                                        <dialog id="exceptionModal{{ $job->id }}" class="modal">
                                            <div class="modal-box max-w-4xl">
                                                <h3 class="text-lg font-semibold">Exception Details</h3>
                                                <div class="py-4">
                                                    <pre
                                                        class="overflow-auto rounded bg-base-200 p-4 text-sm">{{ $job->exception }}</pre>
                                                </div>
                                                <div class="modal-action">
                                                    <button class="btn" onclick="exceptionModal{{ $job->id }}.close()">Close
                                                    </button>
                                                </div>
                                            </div>
                                            <form method="dialog" class="modal-backdrop">
                                                <button>close</button>
                                            </form>
                                        </dialog>

                                        <x-confirm-dialog
                                            id="deleteModal{{ $job->id }}"
                                            title="Confirm Deletion"
                                            message="Are you sure you want to delete this failed job?"
                                            :action="route('admin.jobs.destroy', $job->id)"
                                            method="DELETE"
                                            confirmLabel="Delete"
                                            :testId="'admin-failed-jobs-delete-modal-'.$job->id"
                                        />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-subtle" data-testid="admin-failed-jobs-empty-state">No
                                        failed jobs found.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        @if ($jobs->hasPages())
            <div class="flex justify-center">
                {{ $jobs->links() }}
            </div>
        @endif
    </div>

    <x-confirm-dialog
        id="truncateModal"
        title="Confirm Truncate"
        message="Are you sure you want to delete ALL failed jobs? This action cannot be undone."
        :action="route('admin.jobs.truncate')"
        method="DELETE"
        confirmLabel="Delete All"
        testId="admin-failed-jobs-truncate-modal"
    />
@endsection
