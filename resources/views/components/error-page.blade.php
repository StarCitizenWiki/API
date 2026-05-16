@props([
    'code' => 500,
    'icon' => 'octagon-alert',
    'title' => 'Something Went Wrong',
    'message' => 'An unexpected error occurred. Please try again later.',
    'severity' => 'error',
])

<div class="flex flex-1 items-center justify-center py-12">
    <div class="card w-full max-w-md bg-base-100 shadow-xl">
        <div class="card-body items-center text-center">
            <div class="flex items-center gap-3">
                <x-icon :name="$icon" class="size-8 text-{{ $severity }}" />
                <h1 class="text-6xl font-bold text-{{ $severity }}">{{ $code }}</h1>
            </div>
            <h2 class="card-title mt-2">{{ $title }}</h2>
            <p class="text-base-content/70">{{ $message }}</p>

            <div class="card-actions mt-4">
                {{ $actions ?? '' }}
            </div>
        </div>
    </div>
</div>
