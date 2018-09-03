@extends('admin.layouts.default_wide')

@section('title', __('Comm Link bearbeiten'))

@section('content')
    <h4>Links in diesem Comm Link: ({{ count($links) }})</h4>
    @forelse($links as $link)
        <a class="d-block" href="{{ $link->href }}" target="_blank">{{ $link->text }}</a> &mdash; {{ $link->href }}
    @empty
        Keine Bilder vorhanden
    @endforelse

    <br><br>
    <h4>Bilder in diesem Comm Link: ({{ count($images) }})</h4>
    @forelse($images as $image)
        <a class="d-block" href="{{ $image->src }}" target="_blank">{{ $image->src }}</a>
    @empty
        Keine Bilder vorhanden
    @endforelse

    <br><br>
    <h4>Text</h4>
    <p>
        {!! $content !!}
    </p>
@endsection
