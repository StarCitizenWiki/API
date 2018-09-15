@extends('layouts.full_width')

@section('body--class', 'bg-dark')
{{-- Page Title --}}
@section('P__title')
    @parent
    @lang('Comm Link: ') - {{ $commLink->title }} - Star Citizen Wiki Api
@endsection


{{-- Head --}}
@section('head__content')
    @parent
    <link rel="stylesheet" href="{{ URL::asset('/css/app.css') }}">
    <style>
        .card-body p {
            color: #dee2e6!important;
        }
    </style>
@endsection

@section('topNav--class', 'd-none')

@section('main--class', 'mt-5')

@section('P__content')
    @component('components.heading', [
        'class' => 'text-center mb-5',
        'route' => url('/'),
    ])@endcomponent

    <div class="row my-5">
        <div class="col-12 col-md-6 mx-auto">
            <div class="card bg-dark text-light-grey">
                <h4 class="card-header">
                    @lang('Comm Link') - {{ $commLink->title }}

                    @if(null !== $commLink->german())
                        <small class="float-right mt-1" title="{{ $commLink->german()->updated_at->diffForHumans() }}">Version vom: {{ $commLink->german()->updated_at->format('d.m.Y H:i') }}</small>
                    @endif
                </h4>
                <div class="card-body">
                    {!! optional($commLink->german())->translation ?? __('Keine deutsche Übersetzung vorhanden.') !!}
                </div>
                <div class="card-footer">
                    Quellenangabe: <var>Übersetzung aus der Star Citizen Wiki API ({{ route('web.api.comm-link.show', $commLink) }})</var>
                    @if(null !== $commLink->german())
                        <br>
                        Übersetzt durch: <a href="{{ $commLink->german()->changelogs()->where('type', 'creation')->first()->admin->userNameWikiLink() }}" target="_blank" class="text-white">
                            {{ $commLink->german()->changelogs()->where('type', 'creation')->first()->admin->username }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection