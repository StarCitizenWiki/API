@extends('admin.layout')

@section('breadcrumbs')
    <li>Failed Jobs</li>
@endsection

@section('admin.content')
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Failed Jobs</h1>
            <div class="flex gap-2">
                <span class="text-sm text-base-content/60">Total: {{ number_format($jobs->total()) }}</span>
                @if ($jobs->total() > 0)
                    <button onclick="truncateModal.showModal()" class="btn btn-error btn-sm">
                        Truncate All
                    </button>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>UUID</th>
                        <th>Connection</th>
                        <th>Queue</th>
                        <th>Failed At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jobs as $job)
                        <tr>
                            <td>{{ $job->id }}</td>
                            <td class="font-mono text-xs">{{ Str::limit($job->uuid, 20) }}</td>
                            <td>{{ $job->connection }}</td>
                            <td>{{ $job->queue }}</td>
                            <td>{{ \Carbon\Carbon::parse($job->failed_at)->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="flex gap-2">
                                    <button onclick="exceptionModal{{ $job->id }}.showModal()" class="btn btn-outline btn-sm">
                                        View Exception
                                    </button>
                                    <button onclick="deleteModal{{ $job->id }}.showModal()" class="btn btn-error btn-sm">
                                        Delete
                                    </button>
                                </div>

                                {{-- Exception Modal --}}
                                <dialog id="exceptionModal{{ $job->id }}" class="modal">
                                    <div class="modal-box max-w-4xl">
                                        <h3 class="text-lg font-bold">Exception Details</h3>
                                        <div class="py-4">
                                            <pre class="overflow-auto rounded bg-base-200 p-4 text-sm">{{ $job->exception }}</pre>
                                        </div>
                                        <div class="modal-action">
                                            <button class="btn" onclick="exceptionModal{{ $job->id }}.close()">Close</button>
                                        </div>
                                    </div>
                                    <form method="dialog" class="modal-backdrop">
                                        <button>close</button>
                                    </form>
                                </dialog>

                                {{-- Delete Modal --}}
                                <dialog id="deleteModal{{ $job->id }}" class="modal">
                                    <div class="modal-box">
                                        <h3 class="text-lg font-bold">Confirm Deletion</h3>
                                        <p class="py-4">Are you sure you want to delete this failed job?</p>
                                        <div class="modal-action">
                                            <form method="POST" action="{{ route('admin.jobs.destroy', $job->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-error">Delete</button>
                                            </form>
                                            <button class="btn" onclick="deleteModal{{ $job->id }}.close()">Cancel</button>
                                        </div>
                                    </div>
                                </dialog>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-base-content/60">No failed jobs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($jobs->hasPages())
            <div class="flex justify-center">
                {{ $jobs->links() }}
            </div>
        @endif
    </div>

    {{-- Truncate All Modal --}}
    <dialog id="truncateModal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">Confirm Truncate</h3>
            <p class="py-4">Are you sure you want to delete ALL failed jobs? This action cannot be undone.</p>
            <div class="modal-action">
                <form method="POST" action="{{ route('admin.jobs.truncate') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-error">Delete All</button>
                </form>
                <button class="btn" onclick="truncateModal.close()">Cancel</button>
            </div>
        </div>
    </dialog>
@endsection
