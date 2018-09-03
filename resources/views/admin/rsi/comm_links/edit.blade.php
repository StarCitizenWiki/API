@extends('admin.layouts.default_wide')

@section('title', __('Comm Link bearbeiten'))

@section('content')
    <h4>Links in diesem Comm Link:</h4>
    @forelse($extra['links'] as $link)
        @if($link['href'] !== '#')
            <a class="d-block" href="{{ $link['href'] }}" target="_blank">{{ $link['text'] }}</a> &mdash; {{ $link['href'] }}
        @endif
    @empty
        Keine Bilder vorhanden
    @endforelse

    <br><br>
    <h4>Bilder in diesem Comm Link:</h4>
    @forelse($extra['images'] as $image)
        <a class="d-block" href="https://robertsspaceindustries.com{{ $image['src'] }}" target="_blank">{{ $image['src'] }}</a>
    @empty
        Keine Bilder vorhanden
    @endforelse

    <br><br>
    <h4>Text</h4>
    <p>
        {!! $content !!}
    </p>
@endsection
