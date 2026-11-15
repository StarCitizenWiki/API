@php
    $versionedNotFound = $exception instanceof \App\Exceptions\NotFoundInVersionException;
    $lastVersionCode = $versionedNotFound ? $exception->availableVersions[0] : null;
    $shortVersionCode = fn (string $code): string => \App\Models\Game\GameVersion::patchFamily($code) ?? $code;
    $title = $versionedNotFound ? 'Not available in this version' : 'Page not found';
    $message = $versionedNotFound
        ? sprintf(
            'This entry has no data in %s. The most recent version with data is %s.',
            $shortVersionCode($exception->requestedVersion),
            $shortVersionCode($lastVersionCode),
        )
        : "We couldn't find this page. The link may be incorrect, or the page may no longer exist.";
@endphp

@extends('layouts.app')

@section('title', '404 - ' . $title)

@section('content')
    <x-error-page
        :code="404"
        icon="search-x"
        :title="$title"
        :message="$message"
        severity="info"
    >
        <x-slot:actions>
            @if ($versionedNotFound)
                <a href="{{ request()->fullUrlWithQuery(['version' => $lastVersionCode]) }}"
                   class="btn btn-primary btn-sm">View in {{ $shortVersionCode($lastVersionCode) }}</a>
            @else
                <a href="{{ route('home') }}" class="btn btn-primary btn-sm">Go to home</a>
            @endif
            <button onclick="history.back()" class="btn btn-ghost btn-sm">Back</button>
            @if ($versionedNotFound)
                <a href="{{ route('home') }}" class="btn btn-ghost btn-sm">Home</a>
            @endif
        </x-slot:actions>
    </x-error-page>
@endsection
